<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\ReferenceDataService;

class ReferenceDataController extends Controller
{
    /**
     * Helper to send cached JSON with ETag and HTTP 304 revalidation support.
     * Releases PHP session lock immediately to prevent serialization of concurrent requests.
     */
    private function serveReference(Request $request, string $key, array $data, int $maxAgeSeconds = 1800): Response
    {
        // 1. Release session lock immediately for read-only concurrency
        Session::closeWrite();

        // 2. Fetch current version & ETag for this reference domain
        $verInfo = ReferenceDataService::getVersion($key);
        $etag = '"' . ($verInfo['etag'] ?? md5($key . '_v1')) . '"';
        $isPublic = in_array($key, ['company_branding', 'app_config'], true);
        $cacheScope = $isPublic ? 'public' : 'private';

        // 3. ETag revalidation check (If-None-Match)
        $clientEtag = $request->getHeader('If-None-Match') ?? $request->getHeader('if-none-match');
        if ($clientEtag !== null && trim($clientEtag) === $etag) {
            $resp = new Response('', 304);
            $resp->setHeader('ETag', $etag);
            $resp->setHeader('Cache-Control', "{$cacheScope}, max-age={$maxAgeSeconds}, stale-while-revalidate=300");
            return $resp;
        }

        // 4. Return payload with version headers
        $response = $this->json([
            'success'     => true,
            'key'         => $key,
            'version'     => $verInfo['version'] ?? 1,
            'etag'        => trim($etag, '"'),
            'updated_at'  => $verInfo['updated_at'] ?? date('Y-m-d H:i:s'),
            'data'        => $data,
        ]);

        $response->setHeader('ETag', $etag);
        $response->setHeader('Cache-Control', "{$cacheScope}, max-age={$maxAgeSeconds}, stale-while-revalidate=300");
        return $response;
    }

    /**
     * GET /api/reference/versions
     * Check current versions of all reference domains for lightweight validation
     */
    public function versions(Request $request): Response
    {
        Session::closeWrite();
        $versions = ReferenceDataService::getVersions();
        $etag = '"' . md5(json_encode($versions)) . '"';

        $clientEtag = $request->getHeader('If-None-Match') ?? $request->getHeader('if-none-match');
        if ($clientEtag !== null && trim($clientEtag) === $etag) {
            $resp = new Response('', 304);
            $resp->setHeader('ETag', $etag);
            $resp->setHeader('Cache-Control', 'public, max-age=300, stale-while-revalidate=60');
            return $resp;
        }

        $resp = $this->json([
            'success'  => true,
            'versions' => $versions,
        ]);
        $resp->setHeader('ETag', $etag);
        $resp->setHeader('Cache-Control', 'public, max-age=300, stale-while-revalidate=60');
        return $resp;
    }

    /**
     * GET /api/reference/locations
     * Country, states, cities (TTL: 24h = 86400s)
     */
    public function locations(Request $request): Response
    {
        return $this->serveReference($request, 'locations', ReferenceDataService::getLocations(), 86400);
    }

    /**
     * GET /api/reference/vehicle-categories
     * Vehicle categories & specs (TTL: 30m = 1800s)
     */
    public function vehicleCategories(Request $request): Response
    {
        return $this->serveReference($request, 'vehicle_categories', ReferenceDataService::getVehicleCategories(), 1800);
    }

    /**
     * GET /api/reference/payment-methods
     * Payment methods (TTL: 30m = 1800s)
     */
    public function paymentMethods(Request $request): Response
    {
        return $this->serveReference($request, 'payment_methods', ReferenceDataService::getPaymentMethods(), 1800);
    }

    /**
     * GET /api/reference/dropdown-options
     * Safe dropdown options (TTL: 30m = 1800s)
     */
    public function dropdownOptions(Request $request): Response
    {
        return $this->serveReference($request, 'dropdown_options', ReferenceDataService::getDropdownOptions(), 1800);
    }

    /**
     * GET /api/reference/company-public
     * Public branding (TTL: 30m = 1800s)
     */
    public function companyPublic(Request $request): Response
    {
        return $this->serveReference($request, 'company_branding', ReferenceDataService::getPublicCompanyBranding(), 1800);
    }

    /**
     * GET /api/reference/app-config
     * Non-sensitive app config (TTL: 30m = 1800s)
     */
    public function appConfig(Request $request): Response
    {
        return $this->serveReference($request, 'app_config', ReferenceDataService::getAppConfig(), 1800);
    }
}

