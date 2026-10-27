<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Logger;
use App\Core\ValidationException;
use App\Models\Customer;
use App\Models\AuditLog;
use Throwable;

class CustomerController extends Controller
{
    private Customer $customerModel;
    private AuditLog $auditLog;

    public function __construct()
    {
        $this->customerModel = new Customer();
        $this->auditLog = new AuditLog();
    }

    /**
     * Display Customer Directory with search, filters, and pagination
     */
    public function index(Request $request): Response
    {
        $search = trim((string)$request->get('search', (string)$request->get('q', '')));
        $status = trim((string)$request->get('status', ''));
        $page = max(1, (int)$request->get('page', 1));
        $perPage = in_array((int)$request->get('per_page'), [5, 10, 15, 20, 25, 30, 50, 100], true) ? (int)$request->get('per_page') : 10;

        $totalCustomers = $this->customerModel->countFiltered($search, $status);
        $customers = $this->customerModel->getPaginated($search, $status, $page, $perPage);
        $totalPages = max(1, (int)ceil($totalCustomers / $perPage));

        // Metric summaries
        $totalActive = $this->customerModel->count("`status` = 'active'");
        $totalInactive = $this->customerModel->count("`status` = 'inactive'");
        $totalWithBookings = $this->customerModel->count("EXISTS (SELECT 1 FROM `bookings` WHERE `bookings`.`customer_id` = `customers`.`id`)");

        return $this->render('office/customers/index', [
            'customers'          => $customers,
            'totalCustomers'     => $totalCustomers,
            'totalActive'        => $totalActive,
            'totalInactive'      => $totalInactive,
            'totalWithBookings'  => $totalWithBookings,
            'currentPage'        => $page,
            'totalPages'         => $totalPages,
            'perPage'            => $perPage,
            'search'             => $search,
            'selectedStatus'     => $status,
            'title'              => 'Customer Directory',
        ], 'layouts/office');
    }

    /**
     * Display the Premium Customer Registration form
     * Target page: /office/users/create & /office/customers/create
     */
    public function create(Request $request): Response
    {
        $generatedCode = $this->customerModel->generateCustomerCode();

        return $this->render('office/customers/create', [
            'title'          => 'Add New Customer Profile',
            'customerCode'   => $generatedCode,
            'errors'         => Session::getFlash('form_errors') ?? [],
            'old'            => Session::getFlash('form_old') ?? [],
            'duplicates'     => Session::getFlash('potential_duplicates') ?? [],
        ], 'layouts/office');
    }

    /**
     * Store new customer profile
     * Strictly creates a customer profile without login credentials or administrative roles
     */
    public function store(Request $request): Response
    {
        try {
            $input = $request->all();
            if (empty($input['phone']) && !empty($input['primary_mobile'])) {
                $input['phone'] = $input['primary_mobile'];
            }
            if (empty($input['address_line']) && !empty($input['address'])) {
                $input['address_line'] = $input['address'];
            }

            // 1. Server-side validation
            $rules = [
                'name'   => 'required|min:2|max:150',
                'phone'  => 'required|min:8|max:25',
                'status' => 'required|in:active,inactive',
            ];

            if (!empty($input['email'])) {
                $rules['email'] = 'email|max:150';
            }

            if (!empty($input['gender'])) {
                $rules['gender'] = 'in:male,female,non_binary,prefer_not_to_say,other';
            }

            $validated = $this->validate($input, $rules);

            // 2. Normalization
            $phone = preg_replace('/[^\d+]/', '', trim((string)$input['phone']));
            if (strlen($phone) < 8) {
                throw new ValidationException('Please provide a valid contact phone number.', [
                    'phone' => ['Primary mobile number is invalid.']
                ]);
            }

            $alternatePhone = !empty($input['alternate_phone']) ? preg_replace('/[^\d+]/', '', trim((string)$input['alternate_phone'])) : null;
            $email = !empty($input['email']) ? strtolower(trim((string)$input['email'])) : null;

            // 3. Duplicate detection flag (do not auto-merge; prompt staff if potential duplicate detected)
            $confirmDuplicate = !empty($input['confirm_duplicate']) && $input['confirm_duplicate'] === '1';
            if (!$confirmDuplicate) {
                $potentialDuplicates = $this->customerModel->findPotentialDuplicates($phone, $email);
                if (!empty($potentialDuplicates)) {
                    Session::setFlash('form_errors', [
                        'duplicate_warning' => 'A customer record with matching contact details already exists. Please review below.'
                    ]);
                    Session::setFlash('potential_duplicates', $potentialDuplicates);
                    Session::setFlash('form_old', $input);
                    return $this->redirect(url('/office/customers/create'));
                }
            }

            // 4. Guaranteed unguessable/unique reference code generated on server
            $customerCode = $this->customerModel->generateCustomerCode();

            $dateOfBirth = !empty($input['date_of_birth']) ? date('Y-m-d', strtotime($input['date_of_birth'])) : null;
            $marketingConsent = isset($input['marketing_consent']) ? (int)$input['marketing_consent'] : 1;

            // Handle optional profile photo and document file upload (supporting both file upload and Base64 payloads)
            $uploadedAvatarPath = $this->saveUploadedFileOrBase64(
                $request,
                $input,
                'profile_photo',
                'profile_photo_base64',
                'customers/avatars',
                [
                    'image/jpeg'  => 'jpg',
                    'image/pjpeg' => 'jpg',
                    'image/png'   => 'png',
                    'image/x-png' => 'png',
                    'image/webp'  => 'webp',
                ]
            );

            $uploadedDocPath = $this->saveUploadedFileOrBase64(
                $request,
                $input,
                'id_document_file',
                'id_document_file_base64',
                'customers/documents',
                [
                    'application/pdf'   => 'pdf',
                    'application/x-pdf' => 'pdf',
                    'image/jpeg'        => 'jpg',
                    'image/pjpeg'       => 'jpg',
                    'image/png'         => 'png',
                    'image/x-png'       => 'png',
                    'image/webp'        => 'webp',
                ]
            );

            // 5. Database insertion (Customer profile ONLY, no login credentials, no admin roles)
            $customerId = $this->customerModel->create([
                'customer_code'     => $customerCode,
                'user_id'           => null, // Staff-created customers do not have an office login account
                'title'             => !empty($input['title']) ? trim((string)$input['title']) : null,
                'name'              => trim((string)$input['name']),
                'date_of_birth'     => $dateOfBirth,
                'gender'            => !empty($input['gender']) ? $input['gender'] : null,
                'phone'             => $phone,
                'alternate_phone'   => $alternatePhone,
                'email'             => $email,
                'address_line'      => !empty($input['address_line']) ? trim((string)$input['address_line']) : null,
                'city'              => !empty($input['city']) ? trim((string)$input['city']) : null,
                'state'             => !empty($input['state']) ? trim((string)$input['state']) : null,
                'postal_code'       => !empty($input['postal_code']) ? trim((string)$input['postal_code']) : null,
                'country'           => !empty($input['country']) ? trim((string)$input['country']) : 'India',
                'profile_photo'     => $uploadedAvatarPath,
                'id_type'           => !empty($input['id_type']) ? trim((string)$input['id_type']) : null,
                'id_number'         => !empty($input['id_number']) ? trim((string)$input['id_number']) : null,
                'id_expiry'         => !empty($input['id_expiry']) ? date('Y-m-d', strtotime($input['id_expiry'])) : null,
                'id_document_file'  => $uploadedDocPath,
                'notes'             => !empty($input['notes']) ? trim((string)$input['notes']) : null,
                'status'            => $input['status'] ?? 'active',
                'marketing_consent' => $marketingConsent,
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s'),
            ]);

            // 6. Security & Audit Logging (No sensitive personal data logged)
            $this->auditLog->record(
                'customer_registered',
                'customers',
                $customerId,
                null,
                [
                    'customer_code' => $customerCode,
                    'status'        => $input['status'] ?? 'active',
                    'created_by'    => Session::getUserId(),
                ]
            );

            Session::setFlash('success', "Customer profile [{$customerCode}] for {$input['name']} registered successfully!");
            if ($request->isAjax()) {
                return $this->json([
                    'success'  => true,
                    'redirect' => url('/office/customers')
                ]);
            }
            return $this->redirect(url('/office/customers'));

        } catch (ValidationException $e) {
            Session::setFlash('form_errors', $e->getErrors());
            Session::setFlash('form_old', $request->all());
            return $this->redirect(url('/office/customers/create'));
        } catch (Throwable $e) {
            Logger::error('Customer registration error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            Session::setFlash('error', 'An internal error occurred while registering customer profile. Please try again.');
            Session::setFlash('form_old', $request->all());
            return $this->redirect(url('/office/customers/create'));
        }
    }

    /**
     * Display Customer Profile and comprehensive travel booking ledger
     */
    public function show(Request $request, array $params): Response
    {
        $id = (int)($params['id'] ?? 0);
        $customer = $this->customerModel->find($id);

        if (!$customer) {
            Session::setFlash('error', 'Customer profile not found.');
            return $this->redirect(url('/office/customers'));
        }

        $bookings = $this->customerModel->getBookingHistory($id);

        return $this->render('office/customers/show', [
            'customer' => $customer,
            'bookings' => $bookings,
            'title'    => "Customer Profile — {$customer['name']} ({$customer['customer_code']})",
        ], 'layouts/office');
    }

    /**
     * Display edit customer profile form
     */
    public function edit(Request $request, array $params): Response
    {
        $id = (int)($params['id'] ?? 0);
        $customer = $this->customerModel->find($id);

        if (!$customer) {
            Session::setFlash('error', 'Customer profile not found.');
            return $this->redirect(url('/office/customers'));
        }

        return $this->render('office/customers/edit', [
            'customer' => $customer,
            'title'    => "Edit Customer Profile — {$customer['customer_code']}",
            'errors'   => Session::getFlash('form_errors') ?? [],
            'old'      => Session::getFlash('form_old') ?? [],
        ], 'layouts/office');
    }

    /**
     * Update customer profile
     */
    public function update(Request $request, array $params): Response
    {
        $id = (int)($params['id'] ?? 0);
        $customer = $this->customerModel->find($id);

        if (!$customer) {
            Session::setFlash('error', 'Customer profile not found.');
            return $this->redirect(url('/office/customers'));
        }

        try {
            $input = $request->all();

            $rules = [
                'name'   => 'required|min:2|max:150',
                'phone'  => 'required|min:8|max:25',
                'status' => 'required|in:active,inactive',
            ];

            if (!empty($input['email'])) {
                $rules['email'] = 'email|max:150';
            }

            $validated = $this->validate($input, $rules);

            $phone = preg_replace('/[^\d+]/', '', trim((string)$input['phone']));
            $alternatePhone = !empty($input['alternate_phone']) ? preg_replace('/[^\d+]/', '', trim((string)$input['alternate_phone'])) : null;
            $email = !empty($input['email']) ? strtolower(trim((string)$input['email'])) : null;
            $dateOfBirth = !empty($input['date_of_birth']) ? date('Y-m-d', strtotime($input['date_of_birth'])) : null;
            $marketingConsent = !empty($input['marketing_consent']) ? 1 : 0;

            $updateData = [
                'title'             => !empty($input['title']) ? trim((string)$input['title']) : null,
                'name'              => trim((string)$input['name']),
                'date_of_birth'     => $dateOfBirth,
                'gender'            => !empty($input['gender']) ? $input['gender'] : null,
                'phone'             => $phone,
                'alternate_phone'   => $alternatePhone,
                'email'             => $email,
                'address_line'      => !empty($input['address_line']) ? trim((string)$input['address_line']) : null,
                'city'              => !empty($input['city']) ? trim((string)$input['city']) : null,
                'state'             => !empty($input['state']) ? trim((string)$input['state']) : null,
                'postal_code'       => !empty($input['postal_code']) ? trim((string)$input['postal_code']) : null,
                'country'           => !empty($input['country']) ? trim((string)$input['country']) : 'India',
                'id_type'           => !empty($input['id_type']) ? trim((string)$input['id_type']) : null,
                'id_number'         => !empty($input['id_number']) ? trim((string)$input['id_number']) : null,
                'id_expiry'         => !empty($input['id_expiry']) ? date('Y-m-d', strtotime($input['id_expiry'])) : null,
                'notes'             => !empty($input['notes']) ? trim((string)$input['notes']) : null,
                'status'            => $input['status'] ?? 'active',
                'marketing_consent' => $marketingConsent,
                'updated_at'        => date('Y-m-d H:i:s'),
            ];

            $newAvatar = $this->saveUploadedFileOrBase64(
                $request,
                $input,
                'profile_photo',
                'profile_photo_base64',
                'customers/avatars',
                [
                    'image/jpeg'  => 'jpg',
                    'image/pjpeg' => 'jpg',
                    'image/png'   => 'png',
                    'image/x-png' => 'png',
                    'image/webp'  => 'webp',
                ]
            );
            if ($newAvatar) {
                $updateData['profile_photo'] = $newAvatar;
            }

            $newDoc = $this->saveUploadedFileOrBase64(
                $request,
                $input,
                'id_document_file',
                'id_document_file_base64',
                'customers/documents',
                [
                    'application/pdf'   => 'pdf',
                    'application/x-pdf' => 'pdf',
                    'image/jpeg'        => 'jpg',
                    'image/pjpeg'       => 'jpg',
                    'image/png'         => 'png',
                    'image/x-png'       => 'png',
                    'image/webp'        => 'webp',
                ]
            );
            if ($newDoc) {
                $updateData['id_document_file'] = $newDoc;
            }

            $this->customerModel->update($id, $updateData);

            $this->auditLog->record(
                'customer_updated',
                'customers',
                $id,
                ['status' => $customer['status']],
                ['status' => $input['status'] ?? 'active', 'updated_by' => Session::getUserId()]
            );

            Session::setFlash('success', "Customer profile [{$customer['customer_code']}] updated successfully.");
            return $this->redirect(url('/office/customers/' . $id));

        } catch (ValidationException $e) {
            Session::setFlash('form_errors', $e->getErrors());
            Session::setFlash('form_old', $request->all());
            return $this->redirect(url('/office/customers/' . $id . '/edit'));
        } catch (Throwable $e) {
            Logger::error('Customer update error: ' . $e->getMessage());
            Session::setFlash('error', 'Failed to update customer profile.');
            return $this->redirect(url('/office/customers/' . $id . '/edit'));
        }
    }

    /**
     * Process file upload supporting both multipart $_FILES and Base64 payloads with public mirroring
     */
    private function saveUploadedFileOrBase64(Request $request, array $input, string $fileKey, string $b64Key, string $subDir, array $allowedMimes): ?string
    {
        $savedRelPath = null;
        $projectRoot = dirname(__DIR__, 2);

        // 1. Genuine multipart file upload
        if ($request->hasFile($fileKey)) {
            try {
                $savedRelPath = $request->storeUploadedFile($fileKey, $subDir);
            } catch (\Throwable $e) {
                Logger::warning("File upload error on key {$fileKey}: " . $e->getMessage());
            }
        }

        // 2. Base64 fallback if file was serialized by client into hidden input
        $b64Val = $input[$b64Key] ?? null;
        if (empty($savedRelPath) && !empty($b64Val) && str_starts_with($b64Val, 'data:')) {
            try {
                $commaPos = strpos($b64Val, ',');
                if ($commaPos !== false) {
                    $header = substr($b64Val, 0, $commaPos);
                    $dataPart = substr($b64Val, $commaPos + 1);

                    if (preg_match('/^data:([a-zA-Z0-9\/\-\+\.]+);base64/i', $header, $m)) {
                        $mime = strtolower(trim($m[1]));
                        if ($mime === 'image/jpg') $mime = 'image/jpeg';
                        if ($mime === 'application/x-pdf') $mime = 'application/pdf';

                        if (isset($allowedMimes[$mime])) {
                            $ext = $allowedMimes[$mime];
                            $binary = base64_decode($dataPart);
                            if ($binary !== false && strlen($binary) > 0 && strlen($binary) <= 10485760) {
                                $prefix = str_contains($subDir, 'avatar') ? 'avatar_' : 'doc_';
                                $filename = $prefix . bin2hex(random_bytes(16)) . '.' . $ext;
                                $savedRelPath = trim($subDir, '/') . '/' . $filename;

                                $privDir = $projectRoot . '/storage/private_uploads/' . trim($subDir, '/');
                                if (!is_dir($privDir)) @mkdir($privDir, 0777, true);
                                file_put_contents($privDir . '/' . $filename, $binary);
                            }
                        } else {
                            Logger::warning("Base64 mime type '{$mime}' not in allowed list for {$b64Key}");
                        }
                    }
                }
            } catch (\Throwable $e) {
                Logger::warning("Base64 save error on key {$b64Key}: " . $e->getMessage());
            }
        }

        // 3. Mirror saved file to public/assets/uploads and public/storage for direct Apache delivery
        if ($savedRelPath) {
            $source = $projectRoot . '/storage/private_uploads/' . $savedRelPath;
            if (file_exists($source)) {
                $pubUploadsFile = $projectRoot . '/public/assets/uploads/' . $savedRelPath;
                $pubUploadsDir = dirname($pubUploadsFile);
                if (!is_dir($pubUploadsDir)) @mkdir($pubUploadsDir, 0777, true);
                @copy($source, $pubUploadsFile);

                $pubStorageFile = $projectRoot . '/public/storage/' . $savedRelPath;
                $pubStorageDir = dirname($pubStorageFile);
                if (!is_dir($pubStorageDir)) @mkdir($pubStorageDir, 0777, true);
                @copy($source, $pubStorageFile);
            }
        }

        return $savedRelPath;
    }
}
