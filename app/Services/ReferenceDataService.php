<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Config;
use PDO;

class ReferenceDataService
{
    private static ?array $versionCache = null;

    /**
     * Get all active reference data versions and ETags
     */
    public static function getVersions(): array
    {
        if (self::$versionCache !== null) {
            return self::$versionCache;
        }

        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->query("SELECT `data_key`, `version`, `etag`, `updated_at` FROM `reference_versions`");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $versions = [];
            foreach ($rows as $row) {
                $versions[$row['data_key']] = [
                    'version'    => (int)$row['version'],
                    'etag'       => $row['etag'],
                    'updated_at' => $row['updated_at'],
                ];
            }
            self::$versionCache = $versions;
            return $versions;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Get version metadata for a single reference key
     */
    public static function getVersion(string $key): array
    {
        $versions = self::getVersions();
        return $versions[$key] ?? [
            'version'    => 1,
            'etag'       => md5($key . '_v1_static'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Invalidate and increment version for a specific reference data domain
     */
    public static function invalidate(string $key): void
    {
        self::$versionCache = null;
        try {
            $pdo = Database::getConnection();
            $newEtag = md5($key . '_' . microtime(true) . '_' . bin2hex(random_bytes(8)));
            $stmt = $pdo->prepare("INSERT INTO `reference_versions` (`data_key`, `version`, `etag`, `updated_at`)
                VALUES (?, 1, ?, NOW())
                ON DUPLICATE KEY UPDATE `version` = `version` + 1, `etag` = VALUES(`etag`), `updated_at` = NOW()");
            $stmt->execute([$key, $newEtag]);
        } catch (\Throwable $e) {
            \App\Core\Logger::error("ReferenceDataService: Failed to invalidate cache for key '{$key}': " . $e->getMessage());
        }
    }

    /**
     * 1. Safe Reference Data: Locations (Country, States, Cities)
     * Suggested expiry: 24 hours
     */
    public static function getLocations(): array
    {
        return [
            'default_country' => 'India',
            'countries' => [
                'India', 'United Arab Emirates', 'Saudi Arabia', 'Singapore',
                'Malaysia', 'Thailand', 'United Kingdom', 'United States',
                'Canada', 'Australia', 'Germany', 'France'
            ],
            'states' => [
                'Maharashtra', 'Gujarat', 'Goa', 'Karnataka', 'Delhi',
                'Rajasthan', 'Madhya Pradesh', 'Kerala', 'Tamil Nadu',
                'Uttar Pradesh', 'Punjab', 'West Bengal', 'Telangana',
                'Andhra Pradesh', 'Haryana', 'Himachal Pradesh', 'Uttarakhand',
                'Jammu and Kashmir', 'Assam', 'Odisha', 'Bihar', 'Jharkhand',
                'Chhattisgarh', 'Chandigarh', 'Puducherry'
            ],
            'cities_by_state' => [
                'Maharashtra' => ['Mumbai', 'Pune', 'Nagpur', 'Nashik', 'Thane', 'Navi Mumbai', 'Aurangabad', 'Solapur', 'Kolhapur', 'Shirdi', 'Mahabaleshwar', 'Lonavala', 'Alibaug', 'Ratnagiri'],
                'Gujarat'     => ['Ahmedabad', 'Surat', 'Vadodara', 'Rajkot', 'Gandhinagar', 'Bhavnagar', 'Jamnagar', 'Junagadh', 'Bhuj', 'Somnath', 'Dwarka'],
                'Goa'         => ['Panaji', 'Margao', 'Vasco da Gama', 'Mapusa', 'Ponda', 'Calangute', 'Candolim'],
                'Karnataka'   => ['Bengaluru', 'Mysuru', 'Hubballi', 'Mangaluru', 'Belagavi', 'Hampi', 'Coorg', 'Udupi'],
                'Delhi'       => ['New Delhi', 'North Delhi', 'South Delhi', 'East Delhi', 'West Delhi', 'Central Delhi'],
                'Rajasthan'   => ['Jaipur', 'Udaipur', 'Jodhpur', 'Jaisalmer', 'Ajmer', 'Pushkar', 'Bikaner', 'Mount Abu', 'Kota'],
                'Madhya Pradesh' => ['Indore', 'Bhopal', 'Gwalior', 'Jabalpur', 'Ujjain', 'Khajuraho'],
                'Kerala'      => ['Kochi', 'Thiruvananthapuram', 'Kozhikode', 'Munnar', 'Alleppey', 'Wayanad'],
                'Tamil Nadu'  => ['Chennai', 'Coimbatore', 'Madurai', 'Ooty', 'Kodaikanal', 'Kanyakumari', 'Rameswaram'],
                'Uttar Pradesh' => ['Lucknow', 'Agra', 'Varanasi', 'Noida', 'Greater Noida', 'Prayagraj', 'Mathura', 'Ayodhya'],
                'Punjab'      => ['Amritsar', 'Ludhiana', 'Jalandhar', 'Patiala', 'Bathinda'],
                'Haryana'     => ['Gurugram', 'Faridabad', 'Panipat', 'Ambala', 'Karnal'],
                'West Bengal' => ['Kolkata', 'Darjeeling', 'Siliguri', 'Howrah', 'Durgapur'],
                'Telangana'   => ['Hyderabad', 'Warangal', 'Nizamabad', 'Karimnagar'],
            ],
        ];
    }

    /**
     * 2. Safe Reference Data: Vehicle Categories & Specs
     * Suggested expiry: 30 minutes
     */
    public static function getVehicleCategories(): array
    {
        return [
            'categories' => [
                ['id' => 'sedan', 'name' => 'Sedan', 'seating_range' => '4 Passengers', 'icon' => 'fa-car-side'],
                ['id' => 'suv', 'name' => 'SUV / MUV', 'seating_range' => '6-7 Passengers', 'icon' => 'fa-truck-pickup'],
                ['id' => 'tempo_traveller', 'name' => 'Tempo Traveller', 'seating_range' => '12-20 Passengers', 'icon' => 'fa-van-shuttle'],
                ['id' => 'mini_bus', 'name' => 'Mini Bus', 'seating_range' => '21-32 Passengers', 'icon' => 'fa-bus-simple'],
                ['id' => 'luxury_coach', 'name' => 'Luxury Coach / Volvo Bus', 'seating_range' => '35-50 Passengers', 'icon' => 'fa-bus'],
                ['id' => 'hatchback', 'name' => 'Hatchback', 'seating_range' => '4 Passengers', 'icon' => 'fa-car'],
            ],
            'fuel_types' => ['Diesel', 'Petrol', 'CNG', 'Electric', 'Hybrid'],
            'transmission_types' => ['Manual', 'Automatic'],
            'seating_capacities' => [4, 6, 7, 10, 12, 17, 20, 26, 32, 45, 50],
        ];
    }

    /**
     * 3. Safe Reference Data: Payment Method Options
     * Suggested expiry: 30 minutes
     */
    public static function getPaymentMethods(): array
    {
        return [
            [
                'id'          => 'cash',
                'name'        => 'Cash',
                'label'       => 'Cash on Hand',
                'icon'        => 'fa-solid fa-money-bill-wave',
                'badge_class' => 'badge-cash'
            ],
            [
                'id'          => 'upi',
                'name'        => 'UPI / QR Code',
                'label'       => 'Instant UPI / QR Code',
                'icon'        => 'fa-solid fa-qrcode',
                'badge_class' => 'badge-upi'
            ],
            [
                'id'          => 'bank_transfer',
                'name'        => 'Bank Transfer',
                'label'       => 'NEFT / RTGS / IMPS Bank Transfer',
                'icon'        => 'fa-solid fa-building-columns',
                'badge_class' => 'badge-bank'
            ],
            [
                'id'          => 'card',
                'name'        => 'Card',
                'label'       => 'Credit / Debit Card (POS/Terminal)',
                'icon'        => 'fa-solid fa-credit-card',
                'badge_class' => 'badge-card'
            ],
            [
                'id'          => 'cheque',
                'name'        => 'Cheque',
                'label'       => 'Cheque / Demand Draft',
                'icon'        => 'fa-solid fa-money-check',
                'badge_class' => 'badge-cheque'
            ],
        ];
    }

    /**
     * 4. Safe Reference Data: Dropdown Options (Tours, Departments, Document Types)
     * Suggested expiry: 30 minutes
     */
    public static function getDropdownOptions(): array
    {
        return [
            'tour_categories' => [
                'Heritage', 'Coastal & Beaches', 'Wildlife & Nature',
                'Hill Station', 'Pilgrimage & Spiritual', 'Adventure',
                'Corporate & Events', 'Customized Family Tour'
            ],
            'booking_statuses' => [
                ['id' => 'pending', 'label' => 'Pending Confirmation', 'color' => '#F59E0B'],
                ['id' => 'confirmed', 'label' => 'Confirmed & Reserved', 'color' => '#10B981'],
                ['id' => 'in_progress', 'label' => 'Trip In Progress', 'color' => '#3B82F6'],
                ['id' => 'completed', 'label' => 'Completed Trip', 'color' => '#059669'],
                ['id' => 'cancelled', 'label' => 'Cancelled', 'color' => '#EF4444'],
            ],
            'departments' => [
                'Fleet Operations', 'Dispatch & Scheduling', 'Chauffeur Services',
                'Finance & Accounts', 'Customer Relationship', 'Executive Administration'
            ],
            'document_types' => [
                'Aadhaar Card', 'Driving License', 'Permanent Account Number (PAN)',
                'Passport', 'Voter Identity Card'
            ],
        ];
    }

    /**
     * 5. Safe Reference Data: Public Company Branding
     * Saved company profile in database is the single source of truth.
     * Suggested expiry: 30 minutes
     */
    public static function getPublicCompanyBranding(): array
    {
        $hasName = has_company_name();
        return [
            'company_name'     => $hasName ? company_display_name() : '',
            'tagline'          => company_tagline() ?: '',
            'logo_url'         => company_logo_url() ?: '',
            'has_logo'         => has_company_logo(),
            'support_email'    => company_email() ?: (string)Config::get('app.support_email', ''),
            'support_phone'    => company_phone() ?: (string)Config::get('app.support_phone', ''),
            'website'          => company_website() ?: (string)Config::get('app.url', ''),
            'address'          => company_address() ?: (string)Config::get('app.company_address', ''),
            'currency'         => Config::get('app.currency', 'INR'),
            'currency_symbol'  => Config::get('app.currency_symbol', '₹'),
        ];
    }

    /**
     * 6. Safe Reference Data: Non-sensitive Application Configuration
     * Suggested expiry: 15–30 minutes
     */
    public static function getAppConfig(): array
    {
        return [
            'app_name'            => (string)Config::get('app.name', 'Tours & Travels Management System'),
            'app_version'         => (string)Config::get('app.version', '2.0.0'),
            'timezone'            => (string)Config::get('app.timezone', 'Asia/Kolkata'),
            'currency'            => (string)Config::get('app.currency', 'INR'),
            'currency_symbol'     => (string)Config::get('app.currency_symbol', '₹'),
            'supported_languages' => \App\Core\Language::getSupported(),
            'default_language'    => 'en',
            'cache_api_version'   => 'v6.0',
        ];
    }
}

