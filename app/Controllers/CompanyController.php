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
     * List Companies / View Configured Company Profile
     */
    public function index(Request $request): Response
    {
        $search = trim((string)$request->get('search', ''));
        $page = max(1, (int)$request->get('page', 1));
        $perPage = max(5, min(50, (int)$request->get('per_page', 10)));
        $offset = ($page - 1) * $perPage;

        $companies = $this->companyModel->allPaginated($search, $perPage, $offset);
        $totalCompanies = $this->companyModel->countTotal($search);
        $totalPages = max(1, (int)ceil($totalCompanies / $perPage));

        $primaryCompany = $this->companyModel->getPrimary();

        return $this->view('office/company/index', [
            'title'          => __('company.management', [], 'Company Management'),
            'companies'      => $companies,
            'primaryCompany' => $primaryCompany,
            'totalCompanies' => $totalCompanies,
            'currentPage'    => $page,
            'totalPages'     => $totalPages,
            'perPage'        => $perPage,
            'search'         => $search,
        ], 'office');
    }

    /**
     * Add Company Form
     */
    public function create(Request $request): Response
    {
        // Check if primary company profile already exists
        $primary = $this->companyModel->getPrimary();
        if ($primary) {
            Session::flash('info', 'A primary company profile is already configured for this installation. You can edit the existing profile below.');
            return Response::redirect(url('/office/company/' . $primary['id'] . '/edit'));
        }

        return $this->view('office/company/create', [
            'title'   => __('company.add_company', [], 'Add Company Profile'),
            'old'     => Session::getFlash('old', []),
            'errors'  => Session::getFlash('errors', []),
        ], 'office');
    }

    /**
     * Store Company Profile
     */
    public function store(Request $request): Response
    {
        $input = $request->all();
        $errors = $this->validateCompanyInput($input);

        // Single primary company profile per installation check
        if ($this->companyModel->hasPrimary()) {
            $errors['company_name'] = ['A company profile already exists. Please edit the existing installation profile instead.'];
        }

        if (!empty($errors)) {
            if ($request->isAjax()) {
                return Response::json(['success' => false, 'errors' => $errors], 422);
            }
            Session::flash('errors', $errors);
            Session::flash('old', $input);
            return Response::redirect(url('/office/company/create'));
        }

        // Process Secure Logo Upload
        $logoPath = null;
        try {
            $logoPath = $this->handleLogoUpload($request, $input);
        } catch (\InvalidArgumentException $e) {
            $errors['logo'] = [$e->getMessage()];
            if ($request->isAjax()) {
                return Response::json(['success' => false, 'errors' => $errors], 422);
            }
            Session::flash('errors', $errors);
            Session::flash('old', $input);
            return Response::redirect(url('/office/company/create'));
        }

        $cleanWebsite = !empty($input['website']) ? $this->sanitizeUrl($input['website']) : null;

        $companyId = $this->companyModel->create([
            'company_name' => trim($input['company_name']),
            'tagline'      => !empty($input['tagline']) ? trim($input['tagline']) : null,
            'logo'         => $logoPath,
            'email'        => strtolower(trim($input['email'])),
            'phone'        => !empty($input['phone']) ? trim($input['phone']) : null,
            'website'      => $cleanWebsite,
            'address'      => !empty($input['address']) ? trim($input['address']) : null,
            'status'       => in_array($input['status'] ?? 'active', ['active', 'inactive']) ? $input['status'] : 'active',
            'is_primary'   => 1,
            'created_at'   => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);

        CompanySettingsService::clearCache();

        $this->auditLog->log(
            Session::getUserId(),
            'company_create',
            "Created primary company profile '{$input['company_name']}' [ID: {$companyId}]"
        );

        if ($request->isAjax()) {
            return Response::json([
                'success'  => true,
                'message'  => 'Company profile created successfully.',
                'redirect' => url('/office/company'),
            ]);
        }

        Session::flash('success', 'Company profile initialized and saved successfully.');
        return Response::redirect(url('/office/company'));
    }

    /**
     * Edit Company Profile Form
     */
    public function edit(Request $request, array $params = []): Response
    {
        $id = !empty($params['id']) ? (int)$params['id'] : null;
        $company = $id ? $this->companyModel->find($id) : $this->companyModel->getPrimary();

        if (!$company) {
            Session::flash('warning', 'No company profile configured yet. Please initialize your company profile.');
            return Response::redirect(url('/office/company/create'));
        }

        return $this->view('office/company/edit', [
            'title'   => __('company.edit_company', [], 'Edit Company Profile'),
            'company' => $company,
            'old'     => Session::getFlash('old', $company),
            'errors'  => Session::getFlash('errors', []),
        ], 'office');
    }

    /**
     * Update Company Profile
     */
    public function update(Request $request, array $params = []): Response
    {
        $id = !empty($params['id']) ? (int)$params['id'] : null;
        $company = $id ? $this->companyModel->find($id) : $this->companyModel->getPrimary();

        if (!$company) {
            Session::flash('danger', 'Company profile not found.');
            return Response::redirect(url('/office/company'));
        }

        $input = $request->all();
        $errors = $this->validateCompanyInput($input, (int)$company['id']);

        if (!empty($errors)) {
            if ($request->isAjax()) {
                return Response::json(['success' => false, 'errors' => $errors], 422);
            }
            Session::flash('errors', $errors);
            Session::flash('old', $input);
            return Response::redirect(url('/office/company/' . $company['id'] . '/edit'));
        }

        // Handle Logo Removal Request
        $logoPath = $company['logo'];
        if (!empty($input['remove_logo']) && $input['remove_logo'] == '1') {
            $this->deletePhysicalLogo($company['logo']);
            $logoPath = null;
        }

        // Handle Logo Upload / Replacement
        try {
            $newLogo = $this->handleLogoUpload($request, $input);
            if (!empty($newLogo)) {
                // Remove old logo if replacing
                if (!empty($company['logo']) && $company['logo'] !== $newLogo) {
                    $this->deletePhysicalLogo($company['logo']);
                }
                $logoPath = $newLogo;
            }
        } catch (\InvalidArgumentException $e) {
            $errors['logo'] = [$e->getMessage()];
            if ($request->isAjax()) {
                return Response::json(['success' => false, 'errors' => $errors], 422);
            }
            Session::flash('errors', $errors);
            Session::flash('old', $input);
            return Response::redirect(url('/office/company/' . $company['id'] . '/edit'));
        }

        $cleanWebsite = !empty($input['website']) ? $this->sanitizeUrl($input['website']) : null;

        $this->companyModel->update((int)$company['id'], [
            'company_name' => trim($input['company_name']),
            'tagline'      => !empty($input['tagline']) ? trim($input['tagline']) : null,
            'logo'         => $logoPath,
            'email'        => strtolower(trim($input['email'])),
            'phone'        => !empty($input['phone']) ? trim($input['phone']) : null,
            'website'      => $cleanWebsite,
            'address'      => !empty($input['address']) ? trim($input['address']) : null,
            'status'       => in_array($input['status'] ?? 'active', ['active', 'inactive']) ? $input['status'] : 'active',
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);

        CompanySettingsService::clearCache();

        $this->auditLog->log(
            Session::getUserId(),
            'company_update',
            "Updated company profile '{$input['company_name']}' [ID: {$company['id']}]"
        );

        if ($request->isAjax()) {
            return Response::json([
                'success'  => true,
                'message'  => 'Company profile updated successfully.',
                'redirect' => url('/office/company'),
            ]);
        }

        Session::flash('success', 'Company profile and dynamic branding updated successfully.');
        return Response::redirect(url('/office/company'));
    }

    /**
     * View Company Profile Details
     */
    public function show(Request $request, array $params = []): Response
    {
        $id = !empty($params['id']) ? (int)$params['id'] : null;
        $company = $id ? $this->companyModel->find($id) : $this->companyModel->getPrimary();

        if (!$company) {
            Session::flash('warning', 'Company profile not found.');
            return Response::redirect(url('/office/company'));
        }

        return $this->view('office/company/show', [
            'title'   => $company['company_name'],
            'company' => $company,
        ], 'office');
    }

    /**
     * Toggle Active / Inactive Status
     */
    public function toggleStatus(Request $request, array $params = []): Response
    {
        $id = (int)($params['id'] ?? $request->post('id', 0));
        $company = $this->companyModel->find($id);

        if (!$company) {
            if ($request->isAjax()) {
                return Response::json(['success' => false, 'message' => 'Profile not found.'], 404);
            }
            Session::flash('danger', 'Company profile not found.');
            return Response::redirect(url('/office/company'));
        }

        $newStatus = ($company['status'] === 'active') ? 'inactive' : 'active';
        $this->companyModel->update($id, [
            'status'     => $newStatus,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        CompanySettingsService::clearCache();

        $this->auditLog->log(
            Session::getUserId(),
            'company_status_toggle',
            "Toggled company profile status to '{$newStatus}' [ID: {$id}]"
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
     * Remove Company Logo Action
     */
    public function removeLogo(Request $request, array $params = []): Response
    {
        $id = (int)($params['id'] ?? $request->post('id', 0));
        $company = $id ? $this->companyModel->find($id) : $this->companyModel->getPrimary();

        if ($company && !empty($company['logo'])) {
            $this->deletePhysicalLogo($company['logo']);
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
        return Response::redirect(url('/office/company/' . ($company['id'] ?? '') . '/edit'));
    }

    /**
     * Validate Company Form Inputs
     */
    private function validateCompanyInput(array $input, ?int $currentId = null): array
    {
        $errors = [];

        // 1. Company Name
        $name = trim($input['company_name'] ?? '');
        if (empty($name)) {
            $errors['company_name'] = ['Company Name is required.'];
        } elseif (mb_strlen($name) < 2 || mb_strlen($name) > 150) {
            $errors['company_name'] = ['Company Name must be between 2 and 150 characters.'];
        }

        // 2. Official Email
        $email = trim($input['email'] ?? '');
        if (empty($email)) {
            $errors['email'] = ['Official Company Email is required.'];
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = ['Please enter a valid official company email address.'];
        }

        // 3. Phone Number (Optional format check)
        $phone = trim($input['phone'] ?? '');
        if (!empty($phone) && !preg_match('/^[0-9+\s\-().]{7,30}$/', $phone)) {
            $errors['phone'] = ['Please provide a valid company contact phone number.'];
        }

        // 4. Website URL (Optional format check)
        $website = trim($input['website'] ?? '');
        if (!empty($website)) {
            $testUrl = str_starts_with($website, 'http://') || str_starts_with($website, 'https://')
                ? $website
                : 'https://' . $website;
            if (!filter_var($testUrl, FILTER_VALIDATE_URL)) {
                $errors['website'] = ['Please provide a valid website URL (e.g. https://www.yourcompany.com).'];
            }
        }

        // 5. Tagline
        $tagline = trim($input['tagline'] ?? '');
        if (!empty($tagline) && mb_strlen($tagline) > 255) {
            $errors['tagline'] = ['Tagline must not exceed 255 characters.'];
        }

        return $errors;
    }

    /**
     * Handle Secure Logo Upload (MIME inspection, file size, safe extension, unique random name)
     */
    private function handleLogoUpload(Request $request, array $input): ?string
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

        // 1. Genuine Multipart File Upload
        if ($request->hasFile('logo')) {
            $file = $request->getFile('logo');
            if ($file && $file['error'] === UPLOAD_ERR_OK) {
                if ($file['size'] > $maxSizeBytes) {
                    throw new \InvalidArgumentException('Uploaded logo exceeds maximum permitted size of 5MB.');
                }

                // Deep MIME inspection via finfo
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $mime = strtolower(trim($finfo->file($file['tmp_name'])));

                if (!isset($allowedMimes[$mime])) {
                    throw new \InvalidArgumentException('Unsupported image format. Allowed formats: PNG, JPG, JPEG, WEBP.');
                }

                $ext = $allowedMimes[$mime];
                $filename = 'logo_' . bin2hex(random_bytes(16)) . '.' . $ext;
                $destination = $targetDir . '/' . $filename;

                if (!move_uploaded_file($file['tmp_name'], $destination)) {
                    if (!@copy($file['tmp_name'], $destination)) {
                        throw new \RuntimeException('Failed to save uploaded logo securely.');
                    }
                }

                return 'company/' . $filename;
            }
        }

        // 2. Base64 Serialized Dropzone Upload Fallback
        $b64Val = $input['logo_base64'] ?? null;
        if (!empty($b64Val) && str_starts_with($b64Val, 'data:image/')) {
            $commaPos = strpos($b64Val, ',');
            if ($commaPos !== false) {
                $header = substr($b64Val, 0, $commaPos);
                $dataPart = substr($b64Val, $commaPos + 1);

                if (preg_match('/^data:(image\/[a-zA-Z0-9\-\+\.]+);base64/i', $header, $m)) {
                    $mime = strtolower(trim($m[1]));
                    if ($mime === 'image/jpg') $mime = 'image/jpeg';

                    if (!isset($allowedMimes[$mime])) {
                        throw new \InvalidArgumentException('Unsupported base64 image format. Allowed formats: PNG, JPG, JPEG, WEBP.');
                    }

                    $ext = $allowedMimes[$mime];
                    $binary = base64_decode($dataPart);
                    if ($binary !== false && strlen($binary) > 0) {
                        if (strlen($binary) > $maxSizeBytes) {
                            throw new \InvalidArgumentException('Base64 logo exceeds maximum permitted size of 5MB.');
                        }
                        $filename = 'logo_' . bin2hex(random_bytes(16)) . '.' . $ext;
                        file_put_contents($targetDir . '/' . $filename, $binary);
                        return 'company/' . $filename;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Delete physical logo file from storage
     */
    private function deletePhysicalLogo(?string $relPath): void
    {
        if (empty($relPath)) {
            return;
        }
        $projectRoot = dirname(__DIR__, 2);
        $fullPath = $projectRoot . '/public/storage/' . ltrim(str_replace('\\', '/', $relPath), '/');
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
