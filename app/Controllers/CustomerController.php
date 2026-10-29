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
        $metrics = $this->getCustomerMetrics();

        return $this->render('office/customers/index', [
            'customers'          => $customers,
            'totalCustomers'     => $totalCustomers,
            'totalAll'           => $metrics['total'],
            'totalActive'        => $metrics['active'],
            'totalInactive'      => $metrics['inactive'],
            'totalWithBookings'  => $metrics['with_bookings'],
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
                'title'  => 'required|in:Mr,Mrs,Ms,Dr,Prof',
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
     * Delete customer profile and all related physical assets & data
     */
    public function delete(Request $request, array|int|string $params): Response
    {
        $id = is_array($params) ? (int)($params['id'] ?? 0) : (int)$params;
        $customer = $this->customerModel->find($id);

        if (!$customer) {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => 'Customer profile not found.'], 404);
            }
            Session::setFlash('error', 'Customer profile not found.');
            return $this->redirect(url('/office/customers'));
        }

        try {
            // 1. Physically delete customer image (profile photo / avatar) and identity documents
            $this->deleteCustomerAssociatedFiles($customer);

            // 2. Dissociate and clean any linked booking manifests and invoice ledgers
            $pdo = \App\Core\Database::getConnection();
            $pdo->prepare("UPDATE `bookings` SET `customer_profile_id` = NULL WHERE `customer_profile_id` = ?")->execute([$id]);
            $pdo->prepare("UPDATE `invoices` SET `customer_profile_id` = NULL WHERE `customer_profile_id` = ?")->execute([$id]);

            // 3. If customer has a linked user login account, clean orphan references and delete account
            if (!empty($customer['user_id'])) {
                $userId = (int)$customer['user_id'];
                $pdo->prepare("UPDATE `bookings` SET `customer_id` = NULL WHERE `customer_id` = ?")->execute([$userId]);
                $pdo->prepare("UPDATE `invoices` SET `customer_id` = NULL WHERE `customer_id` = ?")->execute([$userId]);
                $pdo->prepare("DELETE FROM `users` WHERE `id` = ? AND `role` = 'customer'")->execute([$userId]);
            }

            // 4. Delete the primary customer database record
            $this->customerModel->delete($id);

            // 5. Audit Logging
            $this->auditLog->record(
                'customer_deleted',
                'customers',
                $id,
                [
                    'customer_code'     => $customer['customer_code'] ?? null,
                    'name'              => $customer['name'] ?? null,
                    'profile_photo'     => $customer['profile_photo'] ?? null,
                    'id_document_file'  => $customer['id_document_file'] ?? null,
                    'user_id'           => $customer['user_id'] ?? null,
                    'deleted_by'        => Session::getUserId(),
                ],
                null
            );

            $msg = "Customer '" . ($customer['name'] ?? 'Record') . "' and all related assets deleted successfully.";
            Session::setFlash('success', $msg);

            if ($request->isAjax()) {
                return $this->json([
                    'success'  => true,
                    'message'  => $msg,
                    'redirect' => url('/office/customers')
                ]);
            }

            return $this->redirect(url('/office/customers'));
        } catch (Throwable $e) {
            Logger::error('Customer delete error: ' . $e->getMessage(), [
                'customer_id' => $id,
                'file'        => $e->getFile(),
                'line'        => $e->getLine(),
            ]);
            $errMsg = 'Failed to delete customer profile. Please try again.';
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => $errMsg], 500);
            }
            Session::setFlash('error', $errMsg);
            return $this->redirect(url('/office/customers'));
        }
    }

    /**
     * Toggle customer active / inactive status
     */
    public function toggleStatus(Request $request, array|int|string $params): Response
    {
        $id = is_array($params) ? (int)($params['id'] ?? 0) : (int)$params;
        $customer = $this->customerModel->find($id);

        if (!$customer) {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => 'Customer profile not found.'], 404);
            }
            Session::setFlash('error', 'Customer profile not found.');
            return $this->redirect(url('/office/customers'));
        }

        try {
            $inputStatus = (string)($request->input('status') ?? '');
            $desiredStatus = in_array($inputStatus, ['active', 'inactive'], true) ? $inputStatus : null;

            $newStatus = $this->customerModel->toggleStatus($id, $desiredStatus);

            $this->auditLog->record(
                'customer_status_toggled',
                'customers',
                $id,
                ['old_status' => $customer['status']],
                ['new_status' => $newStatus, 'updated_by' => Session::getUserId()]
            );

            $actionWord = ($newStatus === 'active') ? 'activated' : 'deactivated';
            $msg = "Customer '{$customer['name']}' has been {$actionWord} successfully.";
            Session::setFlash('success', $msg);

            if ($request->isAjax()) {
                return $this->json([
                    'success'    => true,
                    'status'     => $newStatus,
                    'is_active'  => ($newStatus === 'active'),
                    'label'      => ucfirst($newStatus),
                    'message'    => $msg,
                    'metrics'    => $this->getCustomerMetrics(),
                ]);
            }

            $referer = $_SERVER['HTTP_REFERER'] ?? url('/office/customers');
            return $this->redirect($referer);

        } catch (Throwable $e) {
            Logger::error('Customer status toggle error: ' . $e->getMessage(), ['customer_id' => $id]);
            $errMsg = 'Failed to update customer status: ' . $e->getMessage();
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => $errMsg], 500);
            }
            Session::setFlash('error', $errMsg);
            return $this->redirect(url('/office/customers/' . $id));
        }
    }

    /**
     * Get aggregate KPI customer metrics for top cards and mobile pills
     */
    private function getCustomerMetrics(): array
    {
        return [
            'total'         => $this->customerModel->count(),
            'active'        => $this->customerModel->count("`status` = 'active'"),
            'inactive'      => $this->customerModel->count("`status` = 'inactive'"),
            'with_bookings' => $this->customerModel->count("EXISTS (SELECT 1 FROM `bookings` b WHERE b.customer_profile_id = `customers`.`id` OR (b.customer_profile_id IS NULL AND b.customer_id = `customers`.`user_id`))"),
        ];
    }

    /**

     * Delete physical files (profile pictures, scans, document attachments) from disk
     */
    private function deleteCustomerAssociatedFiles(array $customer): void
    {
        $projectRoot = dirname(__DIR__, 2);
        $fileKeys = ['profile_photo', 'avatar', 'id_document_file'];

        foreach ($fileKeys as $key) {
            $pathVal = $customer[$key] ?? null;
            if (empty($pathVal) || !is_string($pathVal)) {
                continue;
            }

            $cleaned = str_replace('\\', '/', $pathVal);
            $cleaned = preg_replace('#^(\.\./|/)+#', '', $cleaned);
            $cleaned = preg_replace('#^(assets/uploads/|storage/private_uploads/|storage/|public/storage/|public/assets/uploads/)#', '', $cleaned);

            $candidateLocations = [
                $projectRoot . '/storage/private_uploads/' . $cleaned,
                $projectRoot . '/public/assets/uploads/' . $cleaned,
                $projectRoot . '/public/storage/' . $cleaned,
                $projectRoot . '/storage/uploads/' . $cleaned,
                $projectRoot . '/public/' . ltrim($cleaned, '/'),
            ];

            if (file_exists($pathVal) && is_file($pathVal)) {
                $candidateLocations[] = $pathVal;
            }

            foreach ($candidateLocations as $filePath) {
                if (!empty($filePath) && file_exists($filePath) && is_file($filePath)) {
                    try {
                        @unlink($filePath);
                    } catch (\Throwable $e) {
                        Logger::warning("Could not unlink customer file [{$filePath}]: " . $e->getMessage());
                    }
                }
            }
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

                                $targetDir = $projectRoot . '/public/storage/' . trim($subDir, '/');
                                if (!is_dir($targetDir)) @mkdir($targetDir, 0777, true);
                                file_put_contents($targetDir . '/' . $filename, $binary);
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

        return $savedRelPath;
    }
}
