<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Logger;
use App\Core\Config;
use App\Models\CompanyProfile;
use App\Models\AuditLog;
use App\Services\CompanySettingsService;

class CompanyController extends Controller
{
    private CompanyProfile $companyModel;
    private AuditLog $auditLog;

    public function __construct()
    {
        $this->companyModel = new CompanyProfile();
        $this->auditLog = new AuditLog();
    }

    /**
     * Single Company Profile Page (Office -> Company)
     * Automatically loads existing profile and all 4 image previews, or displays an empty form if none exists.
     */
    public function index(Request $request): Response
    {
        $primaryCompany = $this->companyModel->getPrimary();

        return $this->view('office/company/index', [
            'title'          => __('company.management', [], 'Company Profile'),
            'company'        => $primaryCompany,
            'primaryCompany' => $primaryCompany,
            'old'            => Session::getFlash('old', $primaryCompany ?? []),
            'errors'         => Session::getFlash('errors', []),
            'success'        => Session::getFlash('success', null),
        ], 'office');
    }

    /**
     * Save Company Profile (Create on first save, Update on subsequent saves)
     * Handles text fields, independent upload/replace of 4 images, and independent removal.
     */
    public function save(Request $request): Response
    {
        $input = $request->all();
        $existing = $this->companyModel->getPrimary();
        $isExisting = !empty($existing);

        $errors = $this->validateCompanyInput($input, true);

        // Process image uploads before saving
        $imageFields = [
            'logo'            => 'logo_',
            'paid_stamp'      => 'stamp_paid_',
            'signature'       => 'sig_auth_',
            'signature_stamp' => 'sig_stamp_',
        ];

        $uploadedImages = [];
        foreach ($imageFields as $field => $prefix) {
            try {
                $newPath = $this->handleImageUpload($request, $input, $field, $prefix);
                if ($newPath !== null) {
                    $uploadedImages[$field] = $newPath;
                }
            } catch (\InvalidArgumentException $e) {
                $errors[$field] = [$e->getMessage()];
            } catch (\Throwable $e) {
                $errors[$field] = ['Failed to upload ' . str_replace('_', ' ', $field) . ': ' . $e->getMessage()];
            }
        }

        if (!empty($errors)) {
            // Clean up newly uploaded files if validation failed
            foreach ($uploadedImages as $tempPath) {
                $this->deletePhysicalFile($tempPath);
            }

            if ($request->isAjax()) {
                return Response::json(['success' => false, 'errors' => $errors], 422);
            }
            Session::flash('errors', $errors);
            Session::flash('old', $input);
            return Response::redirect(url('/office/company'));
        }

        // Prepare data for save
        $cleanWebsite = !empty($input['website']) ? $this->sanitizeUrl($input['website']) : null;
        $cleanEmail = !empty($input['email']) ? strtolower(trim($input['email'])) : null;
        $cleanGst = !empty($input['gst_number']) ? strtoupper(trim($input['gst_number'])) : null;
        $cleanPan = !empty($input['pan_number']) ? strtoupper(trim($input['pan_number'])) : null;

        $data = [
            'company_name'    => trim($input['company_name']),
            'owner_name'      => !empty($input['owner_name']) ? trim($input['owner_name']) : null,
            'tagline'         => !empty($input['tagline']) ? trim($input['tagline']) : null,
            'email'           => $cleanEmail,
            'phone'           => !empty($input['phone']) ? trim($input['phone']) : null,
            'alternate_phone' => !empty($input['alternate_phone']) ? trim($input['alternate_phone']) : null,
            'website'         => $cleanWebsite,
            'gst_number'      => $cleanGst,
            'pan_number'      => $cleanPan,
            'address'         => !empty($input['address']) ? trim($input['address']) : null,
            'city'            => !empty($input['city']) ? trim($input['city']) : null,
            'state'           => !empty($input['state']) ? trim($input['state']) : null,
            'pin_code'        => !empty($input['pin_code']) ? trim($input['pin_code']) : null,
            'status'          => in_array($input['status'] ?? 'active', ['active', 'inactive']) ? $input['status'] : 'active',
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        // Handle image removals or updates
        $filesToDelete = [];
        foreach ($imageFields as $field => $prefix) {
            $removeKey = 'remove_' . $field;
            $isRemoveRequested = !empty($input[$removeKey]) && ($input[$removeKey] == '1' || $input[$removeKey] === 'true');

            if ($isRemoveRequested) {
                if ($isExisting && !empty($existing[$field])) {
                    $filesToDelete[] = $existing[$field];
                }
                $data[$field] = null;
            } elseif (isset($uploadedImages[$field])) {
                // Replacement: delete previous file after successful upload
                if ($isExisting && !empty($existing[$field]) && $existing[$field] !== $uploadedImages[$field]) {
                    $filesToDelete[] = $existing[$field];
                }
                $data[$field] = $uploadedImages[$field];
            } else {
                // Preserve existing image
                if ($isExisting) {
                    $data[$field] = $existing[$field] ?? null;
                }
            }
        }

        if ($isExisting) {
            // Update existing primary profile
            $companyId = (int)$existing['id'];
            $this->companyModel->update($companyId, $data);
            $action = 'company_update';
            $logMsg = "Updated company profile '{$data['company_name']}' [ID: {$companyId}]";
            $flashMsg = 'Company profile updated successfully.';
        } else {
            // Create single primary profile
            $data['is_primary'] = 1;
            $data['created_at'] = date('Y-m-d H:i:s');
            $companyId = $this->companyModel->create($data);
            $action = 'company_create';
            $logMsg = "Created primary company profile '{$data['company_name']}' [ID: {$companyId}]";
            $flashMsg = 'Company profile saved successfully.';
        }

        // Safely delete superseded / removed physical files
        foreach ($filesToDelete as $oldFile) {
            $this->deletePhysicalFile($oldFile);
        }

        CompanySettingsService::clearCache();

        $this->auditLog->log(
            Session::getUserId() ?? 1,
            $action,
            $logMsg
        );

        if ($request->isAjax()) {
            return Response::json([
                'success'  => true,
                'message'  => $flashMsg,
                'redirect' => url('/office/company'),
            ]);
        }

        Session::flash('success', $flashMsg);
        return Response::redirect(url('/office/company'));
    }

    /**
     * Add Company Form (Backward compatible alias to single profile page)
     */
    public function create(Request $request): Response
    {
        $primary = $this->companyModel->getPrimary();

        return $this->view('office/company/index', [
            'title'          => __('company.management', [], 'Company Profile'),
            'company'        => $primary,
            'primaryCompany' => $primary,
            'old'            => Session::getFlash('old', $primary ?? []),
            'errors'         => Session::getFlash('errors', []),
        ], 'office');
    }

    /**
     * Store Company Profile (Backward compatible endpoint)
     */
    public function store(Request $request): Response
    {
        $input = $request->all();
        $isAjax = $request->isAjax();

        // Compatibility check for duplicate primary tests
        if ($this->companyModel->hasPrimary() && empty($input['id'])) {
            $errors = ['company_name' => ['A company profile already exists. Please edit the existing installation profile instead.']];
            if ($isAjax) {
                return Response::json(['success' => false, 'errors' => $errors], 422);
            }
            Session::flash('errors', $errors);
            Session::flash('old', $input);
            return Response::redirect(url('/office/company'));
        }

        return $this->save($request);
    }

    /**
     * Edit Company Profile Form (Backward compatible alias)
     */
    public function edit(Request $request, array $params = []): Response
    {
        $id = !empty($params['id']) ? (int)$params['id'] : null;
        $company = $id ? $this->companyModel->find($id) : $this->companyModel->getPrimary();

        return $this->view('office/company/index', [
            'title'          => __('company.management', [], 'Company Profile'),
            'company'        => $company,
            'primaryCompany' => $company,
            'old'            => Session::getFlash('old', $company ?? []),
            'errors'         => Session::getFlash('errors', []),
        ], 'office');
    }

    /**
     * Update Company Profile (Backward compatible endpoint)
     */
    public function update(Request $request, array $params = []): Response
    {
        $id = !empty($params['id']) ? (int)$params['id'] : null;
        $company = $id ? $this->companyModel->find($id) : $this->companyModel->getPrimary();

        if (!$company) {
            if ($request->isAjax()) {
                return Response::json(['success' => false, 'message' => 'Company profile not found.'], 404);
            }
            Session::flash('danger', 'Company profile not found.');
            return Response::redirect(url('/office/company'));
        }

        return $this->save($request);
    }

    /**
     * View Company Profile Details
     */
    public function show(Request $request, array $params = []): Response
    {
        return Response::redirect(url('/office/company'));
    }

    /**
     * Toggle Active / Inactive Status
     */
    public function toggleStatus(Request $request, array $params = []): Response
    {
        $company = $this->companyModel->getPrimary();

        if (!$company) {
            if ($request->isAjax()) {
                return Response::json(['success' => false, 'message' => 'Profile not found.'], 404);
            }
            Session::flash('danger', 'Company profile not found.');
            return Response::redirect(url('/office/company'));
        }

        $newStatus = ($company['status'] === 'active') ? 'inactive' : 'active';
        $this->companyModel->update((int)$company['id'], [
            'status'     => $newStatus,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        CompanySettingsService::clearCache();

        $this->auditLog->log(
            Session::getUserId() ?? 1,
            'company_status_toggle',
            "Toggled company profile status to '{$newStatus}' [ID: {$company['id']}]"
        );

        if ($request->isAjax()) {
            return Response::json([
                'success'    => true,
                'message'    => "Company status updated to {$newStatus}.",
                'new_status' => $newStatus,
            ]);
        }

        Session::flash('success', "Company profile status updated to " . ucfirst($newStatus) . ".");
        return Response::redirect(url('/office/company'));
    }

    /**
     * Remove Specific Document Image or Logo via AJAX or POST
     */
    public function removeImage(Request $request): Response
    {
        $type = trim((string)$request->post('type', $request->get('type', 'logo')));
        $allowedTypes = ['logo', 'paid_stamp', 'signature', 'signature_stamp'];

        if (!in_array($type, $allowedTypes)) {
            if ($request->isAjax()) {
                return Response::json(['success' => false, 'message' => 'Invalid image type requested.'], 400);
            }
            Session::flash('danger', 'Invalid image type.');
            return Response::redirect(url('/office/company'));
        }

        $company = $this->companyModel->getPrimary();
        if ($company && !empty($company[$type])) {
            $this->deletePhysicalFile($company[$type]);
            $this->companyModel->update((int)$company['id'], [
                $type        => null,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            CompanySettingsService::clearCache();
        }

        $friendlyName = ucwords(str_replace('_', ' ', $type));
        $msg = "{$friendlyName} removed successfully.";

        if ($request->isAjax()) {
            return Response::json([
                'success' => true,
                'message' => $msg,
                'type'    => $type,
            ]);
        }

        Session::flash('success', $msg);
        return Response::redirect(url('/office/company'));
    }

    /**
     * Remove Company Logo Action (Backward compatibility)
     */
    public function removeLogo(Request $request, array $params = []): Response
    {
        $company = $this->companyModel->getPrimary();

        if ($company && !empty($company['logo'])) {
            $this->deletePhysicalFile($company['logo']);
            $this->companyModel->update((int)$company['id'], [
                'logo'       => null,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            CompanySettingsService::clearCache();
        }

        if ($request->isAjax()) {
            return Response::json([
                'success' => true,
                'message' => 'Company logo removed successfully.',
            ]);
        }

        Session::flash('success', 'Company logo removed successfully.');
        return Response::redirect(url('/office/company'));
    }

    /**
     * Validate Company Form Inputs
     */
    private function validateCompanyInput(array $input, bool $isWebForm = true): array
    {
        $errors = [];

        // 1. Company Name (Required)
        $name = trim($input['company_name'] ?? '');
        if (empty($name)) {
            $errors['company_name'] = ['Company Name is required.'];
        } elseif (mb_strlen($name) < 2 || mb_strlen($name) > 150) {
            $errors['company_name'] = ['Company Name must be between 2 and 150 characters.'];
        }

        // 2. Owner / Contact Person (Optional)
        $owner = trim($input['owner_name'] ?? '');
        if (!empty($owner) && mb_strlen($owner) > 150) {
            $errors['owner_name'] = ['Owner / Contact Person must not exceed 150 characters.'];
        }

        // 3. Mobile Number (Required on web form, optional if partial API)
        $phone = trim($input['phone'] ?? '');
        if (empty($phone)) {
            if ($isWebForm && isset($input['phone'])) {
                $errors['phone'] = ['Mobile Number is required.'];
            }
        } else {
            // Must contain 10-15 digits, allowing spaces, dashes, +, parentheses
            $digitsOnly = preg_replace('/\D/', '', $phone);
            if (!preg_match('/^[0-9+\s\-().]{7,30}$/', $phone) || strlen($digitsOnly) < 10) {
                $errors['phone'] = ['Please enter a valid 10-digit mobile number.'];
            }
        }

        // 4. Alternate Mobile Number (Optional)
        $altPhone = trim($input['alternate_phone'] ?? '');
        if (!empty($altPhone)) {
            $digitsAlt = preg_replace('/\D/', '', $altPhone);
            if (!preg_match('/^[0-9+\s\-().]{7,30}$/', $altPhone) || strlen($digitsAlt) < 10) {
                $errors['alternate_phone'] = ['Please provide a valid alternate mobile number (at least 10 digits).'];
            }
        }

        // 5. Email Address (Optional)
        $email = trim($input['email'] ?? '');
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = ['Please enter a valid email address.'];
        }

        // 6. Website URL (Optional)
        $website = trim($input['website'] ?? '');
        if (!empty($website)) {
            $testUrl = str_starts_with($website, 'http://') || str_starts_with($website, 'https://')
                ? $website
                : 'https://' . $website;
            if (!filter_var($testUrl, FILTER_VALIDATE_URL)) {
                $errors['website'] = ['Please enter a valid website URL (e.g. https://www.yourcompany.com).'];
            }
        }

        // 7. GST Number (Optional: 15 alphanumeric characters)
        $gst = strtoupper(trim($input['gst_number'] ?? ''));
        if (!empty($gst)) {
            if (!preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/i', $gst) && !preg_match('/^[A-Z0-9]{15}$/i', $gst)) {
                $errors['gst_number'] = ['Please enter a valid 15-character GSTIN (e.g. 27AABCM8899Q1ZX).'];
            }
        }

        // 8. PAN Number (Optional: 10 alphanumeric characters)
        $pan = strtoupper(trim($input['pan_number'] ?? ''));
        if (!empty($pan)) {
            if (!preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/i', $pan) && !preg_match('/^[A-Z0-9]{10}$/i', $pan)) {
                $errors['pan_number'] = ['Please enter a valid 10-character PAN number (e.g. ABCDE1234F).'];
            }
        }

        // 9. Office Address (Required on web form)
        $address = trim($input['address'] ?? '');
        if (empty($address) && $isWebForm && array_key_exists('address', $input)) {
            $errors['address'] = ['Office Address is required.'];
        }

        // 10. City (Required on web form)
        $city = trim($input['city'] ?? '');
        if (empty($city) && $isWebForm && array_key_exists('city', $input)) {
            $errors['city'] = ['City is required.'];
        }

        // 11. State (Required on web form)
        $state = trim($input['state'] ?? '');
        if (empty($state) && $isWebForm && array_key_exists('state', $input)) {
            $errors['state'] = ['State is required.'];
        }

        // 12. PIN Code (Required on web form: 6 digits)
        $pin = trim($input['pin_code'] ?? '');
        if (empty($pin) && $isWebForm && array_key_exists('pin_code', $input)) {
            $errors['pin_code'] = ['PIN Code is required.'];
        } elseif (!empty($pin)) {
            if (!preg_match('/^[1-9][0-9]{5}$/', $pin)) {
                $errors['pin_code'] = ['Please enter a valid 6-digit PIN code.'];
            }
        }

        // 13. Tagline (Optional)
        $tagline = trim($input['tagline'] ?? '');
        if (!empty($tagline) && mb_strlen($tagline) > 255) {
            $errors['tagline'] = ['Tagline must not exceed 255 characters.'];
        }

        return $errors;
    }

    /**
     * Handle Secure Image Upload (MIME inspection, content inspection, size, safe extension, unique random name)
     */
    private function handleImageUpload(Request $request, array $input, string $fieldName, string $prefix = 'img_'): ?string
    {
        $allowedMimes = [
            'image/jpeg' => 'jpg',
            'image/pjpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
        ];
        $maxSizeBytes = 5 * 1024 * 1024; // 5 MB

        $projectRoot = dirname(__DIR__, 2);
        $targetDir = $projectRoot . '/public/storage/company';
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0777, true);
        }

        // 1. Multipart File Upload
        if ($request->hasFile($fieldName)) {
            $file = $request->getFile($fieldName);
            if ($file && $file['error'] === UPLOAD_ERR_OK && !empty($file['tmp_name'])) {
                if ($file['size'] > $maxSizeBytes) {
                    throw new \InvalidArgumentException(ucwords(str_replace('_', ' ', $fieldName)) . ' exceeds maximum permitted size of 5MB.');
                }

                // Deep MIME inspection via finfo
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $mime = strtolower(trim($finfo->file($file['tmp_name'])));

                if (!isset($allowedMimes[$mime])) {
                    throw new \InvalidArgumentException('Unsupported image format for ' . str_replace('_', ' ', $fieldName) . '. Allowed formats: PNG, JPG, JPEG, WEBP.');
                }

                // Verify genuine image using getimagesize
                $imageInfo = @getimagesize($file['tmp_name']);
                if ($imageInfo === false) {
                    throw new \InvalidArgumentException('Uploaded file for ' . str_replace('_', ' ', $fieldName) . ' is not a valid image.');
                }

                $ext = $allowedMimes[$mime];
                $filename = $prefix . bin2hex(random_bytes(16)) . '.' . $ext;
                $destination = $targetDir . '/' . $filename;

                if (!move_uploaded_file($file['tmp_name'], $destination)) {
                    if (!@copy($file['tmp_name'], $destination)) {
                        throw new \RuntimeException('Failed to save uploaded ' . str_replace('_', ' ', $fieldName) . ' securely.');
                    }
                }

                return 'company/' . $filename;
            }
        }

        // 2. Base64 Serialized Upload Fallback (for drag-and-drop or programmatic tests)
        $base64Field = $fieldName . '_base64';
        $b64Val = $input[$base64Field] ?? null;
        if (!empty($b64Val)) {
            if (!str_starts_with($b64Val, 'data:image/')) {
                throw new \InvalidArgumentException('Unsupported image format for ' . str_replace('_', ' ', $fieldName) . '. Allowed formats: PNG, JPG, JPEG, WEBP.');
            }

            $commaPos = strpos($b64Val, ',');
            if ($commaPos !== false) {
                $header = substr($b64Val, 0, $commaPos);
                $dataPart = substr($b64Val, $commaPos + 1);

                if (preg_match('/^data:(image\/[a-zA-Z0-9\-\+\.]+);base64/i', $header, $m)) {
                    $mime = strtolower(trim($m[1]));
                    if ($mime === 'image/jpg') $mime = 'image/jpeg';

                    if (!isset($allowedMimes[$mime])) {
                        throw new \InvalidArgumentException('Unsupported base64 image format for ' . str_replace('_', ' ', $fieldName) . '. Allowed formats: PNG, JPG, JPEG, WEBP.');
                    }

                    $ext = $allowedMimes[$mime];
                    $binary = base64_decode($dataPart);
                    if ($binary !== false && strlen($binary) > 0) {
                        if (strlen($binary) > $maxSizeBytes) {
                            throw new \InvalidArgumentException('Image for ' . str_replace('_', ' ', $fieldName) . ' exceeds maximum permitted size of 5MB.');
                        }
                        $filename = $prefix . bin2hex(random_bytes(16)) . '.' . $ext;
                        file_put_contents($targetDir . '/' . $filename, $binary);
                        return 'company/' . $filename;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Safely delete physical file from storage/company
     */
    private function deletePhysicalFile(?string $relPath): void
    {
        if (empty($relPath)) {
            return;
        }

        // Sanitize path to prevent directory traversal
        $cleanPath = str_replace(['..', '\\', "\0"], ['', '/', ''], $relPath);
        $cleanPath = ltrim($cleanPath, '/');

        // Allow deletion only if located within company storage
        if (!str_starts_with($cleanPath, 'company/')) {
            return;
        }

        $projectRoot = dirname(__DIR__, 2);
        $fullPath = $projectRoot . '/public/storage/' . $cleanPath;

        if (file_exists($fullPath) && is_file($fullPath)) {
            @unlink($fullPath);
        }
    }

    /**
     * Ensure URL has a valid scheme
     */
    private function sanitizeUrl(string $url): string
    {
        $url = trim($url);
        if (!preg_match('#^https?://#i', $url)) {
            return 'https://' . $url;
        }
        return $url;
    }
}
