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
     * Backward-Compatible Company Route Entry Point
     * Cleanly redirects /office/company to /office/company/details (Option A)
     */
    public function index(Request $request): Response
    {
        return Response::redirect(url('/office/company/details'));
    }

    /**
     * Dedicated Company Details Page (/office/company/details)
     * Strictly company/business information (Logo, Name, Owner, Status, Contact, Address, Tax, Documents)
     */
    public function details(Request $request): Response
    {
        $primaryCompany = $this->companyModel->getPrimary();

        $flashSuccess = Session::getFlash('success');
        $flashError   = Session::getFlash('error') ?? Session::getFlash('danger');
        $flashWarning = Session::getFlash('warning');
        $flashInfo    = Session::getFlash('info');

        $flashes = [];
        if (!empty($flashSuccess)) $flashes['success'] = $flashSuccess;
        if (!empty($flashError))   $flashes['error']   = $flashError;
        if (!empty($flashWarning)) $flashes['warning'] = $flashWarning;
        if (!empty($flashInfo))    $flashes['info']    = $flashInfo;

        $activeStep = (int)(Session::getFlash('active_step') ?? $request->query('step', 1));
        if ($activeStep < 1 || $activeStep > 5) {
            $activeStep = 1;
        }

        return $this->view('office/company/details', [
            'title'          => __('company.details', [], 'Company Details'),
            'company'        => $primaryCompany,
            'primaryCompany' => $primaryCompany,
            'activeStep'     => $activeStep,
            'flashes'        => $flashes,
            'old'            => Session::getFlash('old') ?? ($primaryCompany ?? []),
            'errors'         => Session::getFlash('errors') ?? [],
            'success'        => $flashSuccess,
        ], 'office');
    }

    /**
     * Dedicated App Details Page (/office/company/app)
     * Strictly application and PWA information (App Name, Same-as-Company switch, App Version *, PWA App Icon)
     */
    public function app(Request $request): Response
    {
        $primaryCompany = $this->companyModel->getPrimary();

        $flashSuccess = Session::getFlash('success');
        $flashError   = Session::getFlash('error') ?? Session::getFlash('danger');
        $flashWarning = Session::getFlash('warning');
        $flashInfo    = Session::getFlash('info');

        $flashes = [];
        if (!empty($flashSuccess)) $flashes['success'] = $flashSuccess;
        if (!empty($flashError))   $flashes['error']   = $flashError;
        if (!empty($flashWarning)) $flashes['warning'] = $flashWarning;
        if (!empty($flashInfo))    $flashes['info']    = $flashInfo;

        $activeStep = (int)(Session::getFlash('active_step') ?? $request->query('step', 1));
        if ($activeStep < 1 || $activeStep > 2) {
            $activeStep = 1;
        }

        return $this->view('office/company/app', [
            'title'          => __('company.app_details', [], 'App Details'),
            'company'        => $primaryCompany,
            'primaryCompany' => $primaryCompany,
            'activeStep'     => $activeStep,
            'initialStep'    => $activeStep,
            'flashes'        => $flashes,
            'old'            => Session::getFlash('old') ?? ($primaryCompany ?? []),
            'errors'         => Session::getFlash('errors') ?? [],
            'success'        => $flashSuccess,
        ], 'office');
    }

    /**
     * Save Company Details (/office/company/details/save)
     * Standard Core PHP POST: Validate -> Save -> Redirect 302 -> GET /office/company/details
     */
    public function saveDetails(Request $request): Response
    {
        $input = $request->all();
        $existing = $this->companyModel->getPrimary();
        $isExisting = !empty($existing);

        $section = $input['section'] ?? 'all';
        $validSections = ['company_info', 'contact_info', 'office_address', 'business_tax', 'documents', 'all'];
        if (!in_array($section, $validSections, true)) {
            $section = 'all';
        }

        $errors = $this->validateDetailsInput($input, true, $section);

        // Determine image fields for this section
        $imageFields = [];
        if ($section === 'company_info') {
            $imageFields = ['logo' => 'logo_'];
        } elseif ($section === 'documents') {
            $imageFields = [
                'logo'            => 'logo_',
                'paid_stamp'      => 'stamp_paid_',
                'signature'       => 'sig_auth_',
                'signature_stamp' => 'sig_stamp_',
            ];
        } elseif ($section === 'all') {
            $imageFields = [
                'logo'            => 'logo_',
                'paid_stamp'      => 'stamp_paid_',
                'signature'       => 'sig_auth_',
                'signature_stamp' => 'sig_stamp_',
            ];
        }

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
            // Clean up newly uploaded files on error
            foreach ($uploadedImages as $tempPath) {
                $this->deletePhysicalFile($tempPath);
            }

            if ($request->isAjax() && empty($input['native_submit'])) {
                return Response::json(['success' => false, 'errors' => $errors, 'section' => $section], 422);
            }

            $errorStep = 1;
            if (isset($errors['phone']) || isset($errors['alternate_phone']) || isset($errors['email']) || isset($errors['website'])) {
                $errorStep = 2;
            } elseif (isset($errors['address']) || isset($errors['city']) || isset($errors['state']) || isset($errors['pin_code'])) {
                $errorStep = 3;
            } elseif (isset($errors['tagline']) || isset($errors['gst_number']) || isset($errors['pan_number'])) {
                $errorStep = 4;
            } elseif (isset($errors['paid_stamp']) || isset($errors['signature']) || isset($errors['signature_stamp'])) {
                $errorStep = 5;
            }

            $firstErrorMsg = 'Please correct the highlighted errors and try again.';
            if (!empty($errors)) {
                $firstVal = reset($errors);
                $firstErrorMsg = is_array($firstVal) ? reset($firstVal) : $firstVal;
            }

            Session::flash('error', $firstErrorMsg);
            Session::flash('errors', $errors);
            Session::flash('old', $input);
            Session::flash('active_step', $errorStep);
            return Response::redirect(url('/office/company/details'));
        }

        // Initialize data array
        if ($isExisting) {
            $data = $existing;
            $data['updated_at'] = date('Y-m-d H:i:s');
        } else {
            $data = [
                'company_name'    => '',
                'app_name_source' => 'company_name',
                'app_name'        => null,
                'app_version'     => '2.4.0',
                'owner_name'      => null,
                'tagline'         => null,
                'primary_color'   => '#176B5B',
                'secondary_color' => '#16443B',
                'accent_color'    => '#D6B77A',
                'logo'            => null,
                'app_icon_source' => 'company_logo',
                'app_icon'        => null,
                'paid_stamp'      => null,
                'signature'       => null,
                'signature_stamp' => null,
                'email'           => null,
                'phone'           => null,
                'alternate_phone' => null,
                'website'         => null,
                'gst_number'      => null,
                'pan_number'      => null,
                'address'         => null,
                'city'            => null,
                'state'           => null,
                'pin_code'        => null,
                'status'          => 'active',
                'is_primary'      => 1,
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ];
        }

        // 1. Company Information
        if ($section === 'company_info' || $section === 'all') {
            if (array_key_exists('company_name', $input)) {
                $data['company_name'] = trim($input['company_name'] ?? '');
            }
            if (array_key_exists('owner_name', $input)) {
                $data['owner_name'] = !empty($input['owner_name']) ? trim($input['owner_name']) : null;
            }
            if (array_key_exists('status', $input)) {
                $data['status'] = in_array($input['status'] ?? 'active', ['active', 'inactive'], true) ? ($input['status'] ?? 'active') : 'active';
            }
        }

        // 2. Contact Information
        if ($section === 'contact_info' || $section === 'all') {
            if (array_key_exists('owner_name', $input) && $section === 'contact_info') {
                $data['owner_name'] = !empty($input['owner_name']) ? trim($input['owner_name']) : null;
            }
            if (array_key_exists('phone', $input)) {
                $data['phone'] = !empty($input['phone']) ? trim($input['phone']) : null;
            }
            if (array_key_exists('alternate_phone', $input)) {
                $data['alternate_phone'] = !empty($input['alternate_phone']) ? trim($input['alternate_phone']) : null;
            }
            if (array_key_exists('email', $input)) {
                $data['email'] = !empty($input['email']) ? strtolower(trim($input['email'])) : null;
            }
            if (array_key_exists('website', $input)) {
                $data['website'] = !empty($input['website']) ? $this->sanitizeUrl($input['website']) : null;
            }
        }

        // 3. Office Address
        if ($section === 'office_address' || $section === 'all') {
            if (array_key_exists('address', $input)) {
                $data['address'] = !empty($input['address']) ? trim($input['address']) : null;
            }
            if (array_key_exists('city', $input)) {
                $data['city'] = !empty($input['city']) ? trim($input['city']) : null;
            }
            if (array_key_exists('state', $input)) {
                $data['state'] = !empty($input['state']) ? trim($input['state']) : null;
            }
            if (array_key_exists('pin_code', $input)) {
                $data['pin_code'] = !empty($input['pin_code']) ? trim($input['pin_code']) : null;
            }
        }

        // 4. Business & Tax
        if ($section === 'business_tax' || $section === 'all') {
            if (array_key_exists('tagline', $input)) {
                $data['tagline'] = !empty($input['tagline']) ? trim($input['tagline']) : null;
            }
            if (array_key_exists('gst_number', $input)) {
                $data['gst_number'] = !empty($input['gst_number']) ? strtoupper(trim($input['gst_number'])) : null;
            }
            if (array_key_exists('pan_number', $input)) {
                $data['pan_number'] = !empty($input['pan_number']) ? strtoupper(trim($input['pan_number'])) : null;
            }
        }

        // 5. Image removals or replacements
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
                if ($isExisting && !empty($existing[$field]) && $existing[$field] !== $uploadedImages[$field]) {
                    $filesToDelete[] = $existing[$field];
                }
                $data[$field] = $uploadedImages[$field];
            } else {
                if ($isExisting) {
                    $data[$field] = $existing[$field] ?? null;
                }
            }
        }

        $sectionLabels = [
            'company_info'   => 'Company Information',
            'contact_info'   => 'Contact Information',
            'office_address' => 'Office Address',
            'business_tax'   => 'Business & Tax Information',
            'documents'      => 'Invoice & Document Branding',
            'all'            => 'Company Details',
        ];
        $secName = $sectionLabels[$section] ?? 'Company Details';

        if ($isExisting) {
            $companyId = (int)$existing['id'];
            $this->companyModel->update($companyId, $data);
            $action = 'company_update';
            $logMsg = "Updated {$secName} for '{$data['company_name']}' [ID: {$companyId}]";
            $flashMsg = "{$secName} updated successfully.";
        } else {
            $data['is_primary'] = 1;
            $data['created_at'] = date('Y-m-d H:i:s');
            $companyId = $this->companyModel->create($data);
            $action = 'company_create';
            $logMsg = "Created primary company profile '{$data['company_name']}' [ID: {$companyId}]";
            $flashMsg = 'Company profile created successfully.';
        }

        foreach ($filesToDelete as $oldFile) {
            $this->deletePhysicalFile($oldFile);
        }

        CompanySettingsService::clearCache();
        CompanySettingsService::syncPwaIcons();
        \App\Services\ReferenceDataService::invalidate('company_branding');

        $this->auditLog->log(
            Session::getUserId() ?? 1,
            $action,
            $logMsg
        );

        if ($request->isAjax() && empty($input['native_submit'])) {
            return Response::json([
                'success'      => true,
                'message'      => $flashMsg,
                'section'      => $section,
                'company_name' => CompanySettingsService::getCompanyName(),
                'status'       => CompanySettingsService::getStatus(),
                'logo_url'     => CompanySettingsService::hasLogo() ? storage_url(CompanySettingsService::getLogoPath()) : null,
                'redirect'     => url('/office/company/details'),
            ]);
        }

        Session::flash('success', $flashMsg);
        return Response::redirect(url('/office/company/details'));
    }

    /**
     * Save App Details (/office/company/app/save)
     * Standard Core PHP POST: Validate -> Save -> Redirect 302 -> GET /office/company/app
     * Enforces mandatory semantic App Version (MAJOR.MINOR.PATCH)
     */
    public function saveApp(Request $request): Response
    {
        $input = $request->all();
        $existing = $this->companyModel->getPrimary();
        $isExisting = !empty($existing);

        $errors = [];

        // 1. Validate App Name & Source
        // By default, Company Name is the App Name. Entering another app name sets it as custom.
        $companyName = trim($isExisting ? ($existing['company_name'] ?? '') : '');
        $rawAppName = trim($input['app_name'] ?? '');
        $rawAppSource = $input['app_name_source'] ?? null;

        $appName = null;
        if ($rawAppName !== '' && ($companyName === '' || strcasecmp($rawAppName, $companyName) !== 0)) {
            $appNameSource = 'custom';
            $appName = $rawAppName;
            if (mb_strlen($appName) < 2 || mb_strlen($appName) > 150) {
                $errors['app_name'] = ['App Name must be between 2 and 150 characters.'];
            }
        } else {
            $appNameSource = 'company_name';
            $appName = null;
        }

        // 2. Validate App Version (Mandatory Semantic Versioning)
        $appVersion = trim($input['app_version'] ?? '');
        if (empty($appVersion)) {
            $errors['app_version'] = ['App Version is required and cannot be empty (e.g. 2.4.0).'];
        } elseif (!CompanySettingsService::isValidSemanticVersion($appVersion)) {
            $errors['app_version'] = ['App Version must follow semantic versioning format MAJOR.MINOR.PATCH (e.g. 2.4.0, 1.0.0).'];
        } else {
            // Version cannot decrease: only stay equal or increase
            $currentVersion = trim((string)($existing['app_version'] ?? ''));
            if ($currentVersion !== '' && CompanySettingsService::isValidSemanticVersion($currentVersion)) {
                if (CompanySettingsService::compareSemanticVersions($appVersion, $currentVersion) < 0) {
                    $errors['app_version'] = ["App Version cannot be decreased. The new version ({$appVersion}) cannot be lower than the current version ({$currentVersion}). Version can only be increased."];
                }
            }
        }

        // 3. Handle App Icon Source & Upload
        $rawIconSource = $input['app_icon_source'] ?? ($isExisting ? ($existing['app_icon_source'] ?? 'company_logo') : 'company_logo');
        $appIconSource = in_array($rawIconSource, ['company_logo', 'custom'], true) ? $rawIconSource : 'company_logo';
        if (!empty($input['remove_app_icon']) && ($input['remove_app_icon'] == '1' || $input['remove_app_icon'] === 'true')) {
            $appIconSource = 'company_logo';
        }

        $uploadedAppIcon = null;
        try {
            $uploadedAppIcon = $this->handleImageUpload($request, $input, 'app_icon', 'app_icon_');
            if ($uploadedAppIcon !== null) {
                $appIconSource = 'custom';
            }
        } catch (\InvalidArgumentException $e) {
            $errors['app_icon'] = [$e->getMessage()];
        } catch (\Throwable $e) {
            $errors['app_icon'] = ['Failed to upload app icon: ' . $e->getMessage()];
        }

        if (!empty($errors)) {
            if ($uploadedAppIcon !== null) {
                $this->deletePhysicalFile($uploadedAppIcon);
            }
            if ($request->isAjax() && empty($input['native_submit'])) {
                return Response::json(['success' => false, 'errors' => $errors], 422);
            }
            $errorStep = 1;
            Session::flash('errors', $errors);
            Session::flash('old', $input);
            Session::flash('active_step', $errorStep);
            return Response::redirect(url('/office/company/app'));
        }

        // Prepare update/creation payload
        $filesToDelete = [];
        if ($isExisting) {
            $data = $existing;
            $data['app_name_source'] = $appNameSource;
            $data['app_name']        = ($appNameSource === 'custom') ? $appName : null;
            $data['app_version']     = $appVersion;
            $data['app_icon_source'] = $appIconSource;
            $data['updated_at']      = date('Y-m-d H:i:s');

            if (!empty($input['remove_app_icon']) && ($input['remove_app_icon'] == '1' || $input['remove_app_icon'] === 'true')) {
                if (!empty($existing['app_icon'])) {
                    $filesToDelete[] = $existing['app_icon'];
                }
                $data['app_icon'] = null;
                $data['app_icon_source'] = 'company_logo';
                $projectRoot = dirname(__DIR__, 2);
                $pwaDir = $projectRoot . '/public/storage/company/pwa';
                if (is_dir($pwaDir)) {
                    $oldPwaFiles = glob($pwaDir . '/*');
                    if ($oldPwaFiles) {
                        foreach ($oldPwaFiles as $f) {
                            if (is_file($f)) {
                                @unlink($f);
                            }
                        }
                    }
                }
            } elseif ($uploadedAppIcon !== null) {
                if (!empty($existing['app_icon']) && $existing['app_icon'] !== $uploadedAppIcon) {
                    $filesToDelete[] = $existing['app_icon'];
                }
                $data['app_icon'] = $uploadedAppIcon;
            }

            $companyId = (int)$existing['id'];
            $this->companyModel->update($companyId, $data);
            $action = 'app_settings_update';
            $logMsg = "Updated App Details: Name={$appNameSource}, Version={$appVersion}, Icon={$appIconSource} [ID: {$companyId}]";
        } else {
            $data = [
                'company_name'    => 'Tours & Travels',
                'app_name_source' => $appNameSource,
                'app_name'        => ($appNameSource === 'custom') ? $appName : null,
                'app_version'     => $appVersion,
                'app_icon_source' => $appIconSource,
                'app_icon'        => $uploadedAppIcon,
                'status'          => 'active',
                'is_primary'      => 1,
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ];
            $companyId = $this->companyModel->create($data);
            $action = 'app_settings_create';
            $logMsg = "Created primary company profile with App Version {$appVersion} [ID: {$companyId}]";
        }

        foreach ($filesToDelete as $oldFile) {
            $this->deletePhysicalFile($oldFile);
        }

        CompanySettingsService::clearCache();
        CompanySettingsService::syncPwaIcons();
        \App\Services\ReferenceDataService::invalidate('company_branding');

        $this->auditLog->log(
            Session::getUserId() ?? 1,
            $action,
            $logMsg
        );

        $flashMsg = 'App details updated successfully.';
        if ($request->isAjax() && empty($input['native_submit'])) {
            return Response::json([
                'success'            => true,
                'message'            => $flashMsg,
                'effective_app_name' => CompanySettingsService::getEffectiveAppName(),
                'app_name_source'    => CompanySettingsService::getAppNameSource(),
                'app_version'        => CompanySettingsService::getAppVersion(),
                'pwa_icon_url'       => CompanySettingsService::getPwaIconPreviewUrl(),
                'redirect'           => url('/office/company/app'),
            ]);
        }

        Session::flash('success', $flashMsg);
        return Response::redirect(url('/office/company/app'));
    }

    /**
     * Backward Compatibility Wrapper for /office/company/save and /office/company POST
     */
    public function save(Request $request): Response
    {
        $input = $request->all();
        $section = $input['section'] ?? '';

        if (in_array($section, ['brand_identity', 'app', 'app_identity', 'app_version', 'app_icon'], true) ||
            (!empty($input['app_version']) && empty($input['company_name']))) {
            return $this->saveApp($request);
        }

        return $this->saveDetails($request);
    }

    /**
     * Public Uncached Version Check Endpoint for PWA Mandatory Updates
     * Accessible at /api/v1/app/version and /pwa/version-check
     */
    public function versionCheck(Request $request): Response
    {
        $currentVersion = CompanySettingsService::getAppVersion();
        $minimumVersion = CompanySettingsService::getMinimumAppVersion();
        $appName        = CompanySettingsService::getEffectiveAppName();
        $pwaIconUrl     = CompanySettingsService::getPwaIconPreviewUrl();
        $releaseId      = md5($currentVersion . '_' . $appName);

        return Response::json([
            'success'         => true,
            'current_version' => $currentVersion,
            'minimum_version' => $minimumVersion,
            'app_name'        => $appName,
            'pwa_icon_url'    => $pwaIconUrl,
            'release_id'      => $releaseId,
            'checked_at'      => date('c'),
        ], 200, [
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'        => 'no-cache',
            'Expires'       => '0',
        ]);
    }

    /**
     * Add Company Form (Backward compatible alias)
     */
    public function create(Request $request): Response
    {
        return Response::redirect(url('/office/company/details'));
    }

    /**
     * Store Company Profile (Backward compatible endpoint)
     */
    public function store(Request $request): Response
    {
        return $this->save($request);
    }

    /**
     * Edit Company Profile Form (Backward compatible alias)
     */
    public function edit(Request $request, array $params = []): Response
    {
        return Response::redirect(url('/office/company/details'));
    }

    /**
     * Update Company Profile (Backward compatible endpoint)
     */
    public function update(Request $request, array $params = []): Response
    {
        return $this->save($request);
    }

    /**
     * View Company Profile Details
     */
    public function show(Request $request, array $params = []): Response
    {
        return Response::redirect(url('/office/company/details'));
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
            return Response::redirect(url('/office/company/details'));
        }

        $newStatus = ($company['status'] === 'active') ? 'inactive' : 'active';
        $this->companyModel->update((int)$company['id'], [
            'status'     => $newStatus,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        CompanySettingsService::clearCache();
        CompanySettingsService::syncPwaIcons();
        \App\Services\ReferenceDataService::invalidate('company_branding');

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
        return Response::redirect(url('/office/company/details'));
    }

    /**
     * Remove Specific Document Image or Logo via AJAX or POST
     */
    public function removeImage(Request $request): Response
    {
        $type = trim((string)$request->post('type', $request->get('type', 'logo')));
        $allowedTypes = ['logo', 'app_icon', 'paid_stamp', 'signature', 'signature_stamp'];

        $referer = (string)($request->header('referer') ?? '');
        $redirectUrl = str_contains($referer, '/office/company/app') ? url('/office/company/app') : url('/office/company/details');

        if (!in_array($type, $allowedTypes)) {
            if ($request->isAjax()) {
                return Response::json(['success' => false, 'message' => 'Invalid image type requested.'], 400);
            }
            Session::flash('danger', 'Invalid image type.');
            return Response::redirect($redirectUrl);
        }

        $company = $this->companyModel->getPrimary();
        if ($company && !empty($company[$type])) {
            $this->deletePhysicalFile($company[$type]);
            $updatePayload = [
                $type        => null,
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            if ($type === 'app_icon') {
                $updatePayload['app_icon_source'] = 'company_logo';
                $pwaDir = dirname(__DIR__, 2) . '/public/storage/company/pwa';
                if (is_dir($pwaDir)) {
                    $oldPwa = glob($pwaDir . '/*');
                    if ($oldPwa) {
                        foreach ($oldPwa as $f) {
                            if (is_file($f)) {
                                @unlink($f);
                            }
                        }
                    }
                }
                $looseIcons = glob(dirname(__DIR__, 2) . '/public/storage/company/app_icon_*');
                if ($looseIcons) {
                    foreach ($looseIcons as $f) {
                        if (is_file($f)) {
                            @unlink($f);
                        }
                    }
                }
            } elseif ($type === 'logo') {
                $logoDir = dirname(__DIR__, 2) . '/public/storage/company/logo';
                if (is_dir($logoDir)) {
                    $oldLogos = glob($logoDir . '/*');
                    if ($oldLogos) {
                        foreach ($oldLogos as $f) {
                            if (is_file($f)) {
                                @unlink($f);
                            }
                        }
                    }
                }
            }
            $this->companyModel->update((int)$company['id'], $updatePayload);
            CompanySettingsService::clearCache();
            CompanySettingsService::syncPwaIcons();
            \App\Services\ReferenceDataService::invalidate('company_branding');
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
        return Response::redirect($redirectUrl);
    }

    /**
     * Remove Company Logo Action
     */
    public function removeLogo(Request $request, array $params = []): Response
    {
        try {
            $company = $this->companyModel->getPrimary();

            if ($company) {
                if (!empty($company['logo'])) {
                    $this->deletePhysicalFile($company['logo']);
                }

                // Professional cleanup: ensure company/logo folder has 0 leftover or duplicate files
                $projectRoot = dirname(__DIR__, 2);
                $logoDir = $projectRoot . '/public/storage/company/logo';
                if (is_dir($logoDir)) {
                    $existingLogos = glob($logoDir . '/*');
                    if ($existingLogos) {
                        foreach ($existingLogos as $oldFile) {
                            if (is_file($oldFile)) {
                                @unlink($oldFile);
                            }
                        }
                    }
                }
                $looseLogos = glob($projectRoot . '/public/storage/company/logo_*');
                if ($looseLogos) {
                    foreach ($looseLogos as $oldFile) {
                        if (is_file($oldFile)) {
                            @unlink($oldFile);
                        }
                    }
                }

                $this->companyModel->update((int)$company['id'], [
                    'logo'       => null,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                CompanySettingsService::clearCache();
                CompanySettingsService::syncPwaIcons();
                \App\Services\ReferenceDataService::invalidate('company_branding');

                $this->auditLog->log(
                    Session::getUserId() ?? 1,
                    'company_logo_remove',
                    "Removed company logo for '" . ($company['company_name'] ?? 'Company') . "'"
                );
            }

            $msg = 'Logo removed successfully.';

            if ($request->isAjax()) {
                return Response::json([
                    'success' => true,
                    'message' => $msg,
                ]);
            }

            Session::flash('success', $msg);
            return Response::redirect(url('/office/company/details'));
        } catch (\Throwable $e) {
            error_log('Failed to remove company logo: ' . $e->getMessage());
            $errMsg = 'Unable to remove the logo. Please try again.';

            if ($request->isAjax()) {
                return Response::json([
                    'success' => false,
                    'message' => $errMsg,
                ], 500);
            }

            Session::flash('danger', $errMsg);
            return Response::redirect(url('/office/company/details'));
        }
    }

    /**
     * Remove Custom PWA App Icon Action
     * Deletes physical file and cleans up storage/company/pwa folder professionally
     */
    public function removeAppIcon(Request $request, array $params = []): Response
    {
        try {
            $company = $this->companyModel->getPrimary();

            if ($company) {
                if (!empty($company['app_icon'])) {
                    $this->deletePhysicalFile($company['app_icon']);
                }

                // Professional cleanup: ensure company/pwa folder has 0 leftover or duplicate icon files
                $projectRoot = dirname(__DIR__, 2);
                $pwaDir = $projectRoot . '/public/storage/company/pwa';
                if (is_dir($pwaDir)) {
                    $existingIcons = glob($pwaDir . '/*');
                    if ($existingIcons) {
                        foreach ($existingIcons as $oldFile) {
                            if (is_file($oldFile)) {
                                @unlink($oldFile);
                            }
                        }
                    }
                }
                $looseIcons = glob($projectRoot . '/public/storage/company/app_icon_*');
                if ($looseIcons) {
                    foreach ($looseIcons as $oldFile) {
                        if (is_file($oldFile)) {
                            @unlink($oldFile);
                        }
                    }
                }

                $this->companyModel->update((int)$company['id'], [
                    'app_icon'        => null,
                    'app_icon_source' => 'company_logo',
                    'updated_at'      => date('Y-m-d H:i:s'),
                ]);
                CompanySettingsService::clearCache();
                CompanySettingsService::syncPwaIcons();
                \App\Services\ReferenceDataService::invalidate('company_branding');

                $this->auditLog->log(
                    Session::getUserId() ?? 1,
                    'company_app_icon_remove',
                    "Removed custom PWA app icon for '" . ($company['company_name'] ?? 'Company') . "'"
                );
            }

            $fallbackLogoUrl = CompanySettingsService::hasLogo() 
                ? CompanySettingsService::getLogoUrl() 
                : asset('icons/icon.svg');
            $previewUrl = CompanySettingsService::getPwaIconPreviewUrl();

            $msg = 'PWA App icon removed successfully.';

            if ($request->isAjax()) {
                return Response::json([
                    'success'           => true,
                    'message'           => $msg,
                    'fallback_logo_url' => $fallbackLogoUrl,
                    'pwa_preview_url'   => $previewUrl,
                ]);
            }

            Session::flash('success', $msg);
            return Response::redirect(url('/office/company/app'));
        } catch (\Throwable $e) {
            error_log('Failed to remove custom PWA app icon: ' . $e->getMessage());
            $errMsg = 'Unable to remove the app icon. Please try again.';

            if ($request->isAjax()) {
                return Response::json([
                    'success' => false,
                    'message' => $errMsg,
                ], 500);
            }

            Session::flash('danger', $errMsg);
            return Response::redirect(url('/office/company/app'));
        }
    }

    /**
     * Validate Company Details Inputs by Section (Business/Company fields only)
     */
    private function validateDetailsInput(array $input, bool $isWebForm = true, string $section = 'all'): array
    {
        $errors = [];

        // 1. Company Information Section
        if ($section === 'company_info' || $section === 'all') {
            $name = trim($input['company_name'] ?? '');
            if (empty($name)) {
                $errors['company_name'] = ['Company Name is required.'];
            } elseif (mb_strlen($name) < 2 || mb_strlen($name) > 150) {
                $errors['company_name'] = ['Company Name must be between 2 and 150 characters.'];
            }
        }

        // 2. Contact Information Section
        if ($section === 'contact_info' || $section === 'all') {
            $owner = trim($input['owner_name'] ?? '');
            if (!empty($owner) && mb_strlen($owner) > 150) {
                $errors['owner_name'] = ['Owner / Contact Person must not exceed 150 characters.'];
            }

            $phone = trim($input['phone'] ?? '');
            if (empty($phone)) {
                if ($isWebForm && ($section === 'contact_info' || array_key_exists('phone', $input))) {
                    $errors['phone'] = ['Mobile Number is required.'];
                }
            } else {
                $digitsOnly = preg_replace('/\D/', '', $phone);
                if (!preg_match('/^[0-9+\s\-().]{7,30}$/', $phone) || strlen($digitsOnly) < 10) {
                    $errors['phone'] = ['Please enter a valid 10-digit mobile number.'];
                }
            }

            $altPhone = trim($input['alternate_phone'] ?? '');
            if (!empty($altPhone)) {
                $digitsAlt = preg_replace('/\D/', '', $altPhone);
                if (!preg_match('/^[0-9+\s\-().]{7,30}$/', $altPhone) || strlen($digitsAlt) < 10) {
                    $errors['alternate_phone'] = ['Please provide a valid alternate mobile number (at least 10 digits).'];
                }
            }

            $email = trim($input['email'] ?? '');
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = ['Please enter a valid email address.'];
            }

            $website = trim($input['website'] ?? '');
            if (!empty($website)) {
                $testUrl = str_starts_with($website, 'http://') || str_starts_with($website, 'https://')
                    ? $website
                    : 'https://' . $website;
                if (!filter_var($testUrl, FILTER_VALIDATE_URL)) {
                    $errors['website'] = ['Please enter a valid website URL (e.g. https://www.yourcompany.com).'];
                }
            }
        }

        // 3. Office Address Section
        if ($section === 'office_address' || $section === 'all') {
            $address = trim($input['address'] ?? '');
            if (empty($address) && $isWebForm && ($section === 'office_address' || array_key_exists('address', $input))) {
                $errors['address'] = ['Office Address is required.'];
            }

            $city = trim($input['city'] ?? '');
            if (empty($city) && $isWebForm && ($section === 'office_address' || array_key_exists('city', $input))) {
                $errors['city'] = ['City is required.'];
            }

            $state = trim($input['state'] ?? '');
            if (empty($state) && $isWebForm && ($section === 'office_address' || array_key_exists('state', $input))) {
                $errors['state'] = ['State is required.'];
            }

            $pin = trim($input['pin_code'] ?? '');
            if (empty($pin) && $isWebForm && ($section === 'office_address' || array_key_exists('pin_code', $input))) {
                $errors['pin_code'] = ['PIN Code is required.'];
            } elseif (!empty($pin)) {
                if (!preg_match('/^[0-9]{6}$/', $pin)) {
                    $errors['pin_code'] = ['Please enter a valid 6-digit PIN code.'];
                }
            }
        }

        // 4. Business & Tax Section
        if ($section === 'business_tax' || $section === 'all') {
            $tagline = trim($input['tagline'] ?? '');
            if (!empty($tagline) && mb_strlen($tagline) > 255) {
                $errors['tagline'] = ['Tagline must not exceed 255 characters.'];
            }

            $gst = strtoupper(trim($input['gst_number'] ?? ''));
            if (!empty($gst)) {
                if (!preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/i', $gst) && !preg_match('/^[A-Z0-9]{15}$/i', $gst)) {
                    $errors['gst_number'] = ['Please enter a valid 15-character GSTIN (e.g. 27AABCM8899Q1ZX).'];
                }
            }

            $pan = strtoupper(trim($input['pan_number'] ?? ''));
            if (!empty($pan)) {
                if (!preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/i', $pan) && !preg_match('/^[A-Z0-9]{10}$/i', $pan)) {
                    $errors['pan_number'] = ['Please enter a valid 10-character PAN number (e.g. ABCDE1234F).'];
                }
            }
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

        // Professional canonical subfolder structure (Issue 5)
        // Logo in 'company/logo', stamps in 'company/stamps', PWA app icon in 'company/pwa'
        $subfolder = 'company';
        if ($fieldName === 'logo') {
            $subfolder = 'company/logo';
        } elseif ($fieldName === 'app_icon') {
            $subfolder = 'company/pwa';
        } elseif (in_array($fieldName, ['paid_stamp', 'signature', 'signature_stamp'], true)) {
            $subfolder = 'company/stamps';
        }

        $targetDir = $projectRoot . '/public/storage/' . $subfolder;
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0777, true);
        }

        // Professional guarantee: store ONLY ONE active file in respective directory (no duplicates)
        if ($fieldName === 'logo') {
            $existingLogos = glob($targetDir . '/*');
            if ($existingLogos) {
                foreach ($existingLogos as $oldFile) {
                    if (is_file($oldFile)) {
                        @unlink($oldFile);
                    }
                }
            }
            // Also clean up any loose legacy duplicate logos directly in company/
            $looseLogos = glob($projectRoot . '/public/storage/company/logo_*');
            if ($looseLogos) {
                foreach ($looseLogos as $oldFile) {
                    if (is_file($oldFile)) {
                        @unlink($oldFile);
                    }
                }
            }
        } elseif ($fieldName === 'app_icon') {
            // For app_icon: safe replacement sequence will prune only after new file is validated & confirmed
        } elseif ($fieldName === 'paid_stamp') {
            $existingStamps = glob($targetDir . '/stamp_paid_*');
            if ($existingStamps) {
                foreach ($existingStamps as $oldFile) {
                    if (is_file($oldFile)) {
                        @unlink($oldFile);
                    }
                }
            }
        } elseif ($fieldName === 'signature') {
            $existingSigs = glob($targetDir . '/sig_auth_*');
            if ($existingSigs) {
                foreach ($existingSigs as $oldFile) {
                    if (is_file($oldFile)) {
                        @unlink($oldFile);
                    }
                }
            }
        } elseif ($fieldName === 'signature_stamp') {
            $existingSigStamps = glob($targetDir . '/sig_stamp_*');
            if ($existingSigStamps) {
                foreach ($existingSigStamps as $oldFile) {
                    if (is_file($oldFile)) {
                        @unlink($oldFile);
                    }
                }
            }
        }

        // 1. Multipart File Upload
        if ($request->hasFile($fieldName)) {
            $file = $request->getFile($fieldName);
            if ($file && $file['error'] === UPLOAD_ERR_OK && !empty($file['tmp_name'])) {
                if ($file['size'] > $maxSizeBytes) {
                    throw new \InvalidArgumentException(ucwords(str_replace('_', ' ', $fieldName)) . ' exceeds maximum permitted size of 5MB.');
                }

                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $mime = strtolower(trim($finfo->file($file['tmp_name'])));

                if (!isset($allowedMimes[$mime])) {
                    throw new \InvalidArgumentException('Unsupported image format for ' . str_replace('_', ' ', $fieldName) . '. Allowed formats: PNG, JPG, JPEG, WEBP.');
                }

                $imageInfo = @getimagesize($file['tmp_name']);
                if ($imageInfo === false) {
                    throw new \InvalidArgumentException('Uploaded file for ' . str_replace('_', ' ', $fieldName) . ' is not a valid image.');
                }

                $ext = $allowedMimes[$mime];
                if ($fieldName === 'app_icon') {
                    // Safe replacement sequence: write to temporary file first so existing icon remains intact if write fails
                    $tmpFilename = 'app-icon.tmp.' . bin2hex(random_bytes(8)) . '.' . $ext;
                    $tmpDestination = $targetDir . '/' . $tmpFilename;

                    if (!move_uploaded_file($file['tmp_name'], $tmpDestination)) {
                        if (!@copy($file['tmp_name'], $tmpDestination)) {
                            throw new \RuntimeException('Failed to save uploaded app icon securely.');
                        }
                    }

                    $verified = @getimagesize($tmpDestination);
                    if ($verified === false || filesize($tmpDestination) === 0) {
                        if (file_exists($tmpDestination)) {
                            @unlink($tmpDestination);
                        }
                        throw new \InvalidArgumentException('Uploaded file for app icon is not a valid image.');
                    }

                    // Prune old files in targetDir now that new file is verified
                    $existingPwaFiles = glob($targetDir . '/*');
                    if ($existingPwaFiles) {
                        foreach ($existingPwaFiles as $oldFile) {
                            if (is_file($oldFile) && $oldFile !== $tmpDestination) {
                                @unlink($oldFile);
                            }
                        }
                    }
                    $looseAppIcons = glob($projectRoot . '/public/storage/company/app_icon_*');
                    if ($looseAppIcons) {
                        foreach ($looseAppIcons as $oldFile) {
                            if (is_file($oldFile)) {
                                @unlink($oldFile);
                            }
                        }
                    }

                    $canonicalFilename = 'app-icon.' . $ext;
                    $canonicalDestination = $targetDir . '/' . $canonicalFilename;
                    @rename($tmpDestination, $canonicalDestination);

                    return $subfolder . '/' . $canonicalFilename;
                } else {
                    $filename = $prefix . bin2hex(random_bytes(16)) . '.' . $ext;
                    $destination = $targetDir . '/' . $filename;

                    if (!move_uploaded_file($file['tmp_name'], $destination)) {
                        if (!@copy($file['tmp_name'], $destination)) {
                            throw new \RuntimeException('Failed to save uploaded ' . str_replace('_', ' ', $fieldName) . ' securely.');
                        }
                    }

                    return $subfolder . '/' . $filename;
                }
            }
        }

        // 2. Base64 Serialized Upload Fallback (for programmatic tests & drag-and-drop)
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

                        if ($fieldName === 'app_icon') {
                            $tmpFilename = 'app-icon.tmp.' . bin2hex(random_bytes(8)) . '.' . $ext;
                            $tmpDestination = $targetDir . '/' . $tmpFilename;
                            file_put_contents($tmpDestination, $binary);

                            $verified = @getimagesize($tmpDestination);
                            if ($verified === false || filesize($tmpDestination) === 0) {
                                if (file_exists($tmpDestination)) {
                                    @unlink($tmpDestination);
                                }
                                throw new \InvalidArgumentException('Uploaded base64 file for app icon is not a valid image.');
                            }

                            // Prune old files in targetDir now that new file is verified
                            $existingPwaFiles = glob($targetDir . '/*');
                            if ($existingPwaFiles) {
                                foreach ($existingPwaFiles as $oldFile) {
                                    if (is_file($oldFile) && $oldFile !== $tmpDestination) {
                                        @unlink($oldFile);
                                    }
                                }
                            }
                            $looseAppIcons = glob($projectRoot . '/public/storage/company/app_icon_*');
                            if ($looseAppIcons) {
                                foreach ($looseAppIcons as $oldFile) {
                                    if (is_file($oldFile)) {
                                        @unlink($oldFile);
                                    }
                                }
                            }

                            $canonicalFilename = 'app-icon.' . $ext;
                            $canonicalDestination = $targetDir . '/' . $canonicalFilename;
                            @rename($tmpDestination, $canonicalDestination);

                            return $subfolder . '/' . $canonicalFilename;
                        } else {
                            $filename = $prefix . bin2hex(random_bytes(16)) . '.' . $ext;
                            file_put_contents($targetDir . '/' . $filename, $binary);
                            return $subfolder . '/' . $filename;
                        }
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

        $cleanPath = str_replace(['..', '\\', "\0"], ['', '/', ''], $relPath);
        $cleanPath = ltrim($cleanPath, '/');

        if (str_starts_with($cleanPath, 'public/')) {
            $cleanPath = substr($cleanPath, 7);
        }
        if (str_starts_with($cleanPath, 'storage/')) {
            $cleanPath = substr($cleanPath, 8);
        }

        if (!str_starts_with($cleanPath, 'company/')) {
            return;
        }

        $projectRoot = dirname(__DIR__, 2);
        $candidates = [
            $projectRoot . '/public/storage/' . $cleanPath,
            $projectRoot . '/public/' . $cleanPath,
        ];

        foreach ($candidates as $fullPath) {
            if (file_exists($fullPath) && is_file($fullPath)) {
                @unlink($fullPath);
            }
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

    /**
     * Dynamic PWA Icon Renderer
     * Serves requested dimension on-the-fly directly from single source without duplicate disk storage
     */
    public function pwaIcon(Request $request): Response
    {
        $size = (int)$request->query('size', 192);
        if ($size < 32 || $size > 1024) {
            $size = 192;
        }

        $sourceRelPath = CompanySettingsService::getActiveAppIconSourcePath();
        $projectRoot = dirname(__DIR__, 2);
        $fullSource = null;

        if (!empty($sourceRelPath)) {
            $cleanPath = ltrim(str_replace('\\', '/', $sourceRelPath), '/');
            $cleanPath = preg_replace('#^(public/storage/|storage/|public/)#', '', $cleanPath);
            $candidate = $projectRoot . '/public/storage/' . $cleanPath;
            if (file_exists($candidate) && is_file($candidate)) {
                $fullSource = $candidate;
            }
        }

        if (!$fullSource) {
            $defaultCandidate = $projectRoot . '/public/assets/icons/icon-192.png';
            if (file_exists($defaultCandidate)) {
                $fullSource = $defaultCandidate;
            }
        }

        if ($fullSource && function_exists('imagecreatefromstring')) {
            $data = @file_get_contents($fullSource);
            if ($data !== false) {
                $srcImg = @imagecreatefromstring($data);
                if ($srcImg) {
                    $srcW = imagesx($srcImg);
                    $srcH = imagesy($srcImg);

                    $canvas = imagecreatetruecolor($size, $size);
                    imagealphablending($canvas, false);
                    imagesavealpha($canvas, true);
                    $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
                    imagefilledrectangle($canvas, 0, 0, $size, $size, $transparent);
                    imagealphablending($canvas, true);

                    $safeAreaSize = $size * 0.88;
                    $scale = min($safeAreaSize / $srcW, $safeAreaSize / $srcH);
                    $dstW = max(1, (int)round($srcW * $scale));
                    $dstH = max(1, (int)round($srcH * $scale));
                    $dstX = (int)round(($size - $dstW) / 2);
                    $dstY = (int)round(($size - $dstH) / 2);

                    imagecopyresampled($canvas, $srcImg, $dstX, $dstY, 0, 0, $dstW, $dstH, $srcW, $srcH);
                    imagealphablending($canvas, false);
                    imagesavealpha($canvas, true);

                    ob_start();
                    imagepng($canvas, null, 9);
                    $pngData = ob_get_clean();
                    imagedestroy($canvas);
                    imagedestroy($srcImg);

                    return new Response($pngData, 200, [
                        'Content-Type'  => 'image/png',
                        'Cache-Control' => 'public, max-age=604800, immutable',
                    ]);
                }
            }
        }

        if ($fullSource && file_exists($fullSource)) {
            $mime = mime_content_type($fullSource) ?: 'image/png';
            return new Response(file_get_contents($fullSource), 200, [
                'Content-Type'  => $mime,
                'Cache-Control' => 'public, max-age=604800, immutable',
            ]);
        }

        return new Response('Icon not found', 404);
    }
}
