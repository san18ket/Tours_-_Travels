<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\ValidationException;
use App\Models\Vehicle;
use App\Models\AuditLog;
use Throwable;

class VehicleController extends Controller
{
    private Vehicle $vehicleModel;
    private AuditLog $auditLog;

    public function __construct()
    {
        $this->vehicleModel = new Vehicle();
        $this->auditLog = new AuditLog();
    }

    /**
     * Display Commercial Vehicle Directory with search, type filter, status filter & pagination
     */
    public function index(Request $request): Response
    {
        $search = trim((string)$request->get('search', ''));
        $type = trim((string)$request->get('type', ''));
        $status = trim((string)$request->get('status', ''));
        $page = max(1, (int)$request->get('page', 1));
        $perPage = in_array((int)$request->get('per_page'), [10, 25, 50, 100], true) ? (int)$request->get('per_page') : 10;

        $totalVehicles = $this->vehicleModel->countFiltered($search, $type, $status);
        $vehicles = $this->vehicleModel->getPaginated($search, $type, $status, $page, $perPage);
        $totalPages = max(1, (int)ceil($totalVehicles / $perPage));

        // Metric summaries
        $totalAvailable = $this->vehicleModel->count("`status` = 'available'");
        $totalOnTrip = $this->vehicleModel->count("`status` = 'on_trip'");
        $totalMaintenance = $this->vehicleModel->count("`status` = 'maintenance'");

        return $this->render('office/vehicles/index', [
            'vehicles'         => $vehicles,
            'totalVehicles'    => $totalVehicles,
            'totalAvailable'   => $totalAvailable,
            'totalOnTrip'      => $totalOnTrip,
            'totalMaintenance' => $totalMaintenance,
            'currentPage'      => $page,
            'totalPages'       => $totalPages,
            'search'           => $search,
            'selectedType'     => $type,
            'selectedStatus'   => $status,
            'perPage'          => $perPage,
            'title'            => 'Fleet Vehicles Directory',
        ], 'layouts/office');
    }

    /**
     * Display Register New Vehicle form
     */
    public function create(Request $request): Response
    {
        return $this->render('office/vehicles/create', [
            'errors' => Session::getFlash('form_errors') ?? [],
            'old'    => Session::getFlash('form_old') ?? [],
            'title'  => 'Register Commercial Vehicle',
        ], 'layouts/office');
    }

    /**
     * Store newly registered fleet vehicle
     */
    public function store(Request $request): Response
    {
        try {
            $input = $request->all();

            $data = $this->validate($input, [
                'vehicle_number'   => 'required|min:4|max:30',
                'make_model'       => 'required|min:3|max:100',
                'vehicle_type'     => 'required|in:sedan,suv,tempo_traveller,luxury_bus,van',
                'seating_capacity' => 'required|numeric',
                'fuel_type'        => 'required|in:diesel,petrol,cng,electric',
                'odometer_km'      => 'required|numeric',
            ]);

            $cleanNumber = strtoupper(trim(preg_replace('/\s+/', ' ', $data['vehicle_number'])));
            $existing = $this->vehicleModel->findBy('vehicle_number', $cleanNumber);
            if ($existing) {
                throw new ValidationException("Commercial registration number '{$cleanNumber}' already exists in the fleet.", [
                    'vehicle_number' => ['This registration plate is already registered.']
                ]);
            }

            $vehicleId = $this->vehicleModel->create([
                'vehicle_number'      => $cleanNumber,
                'make_model'          => trim($data['make_model']),
                'vehicle_type'        => $data['vehicle_type'],
                'seating_capacity'    => (int)$data['seating_capacity'],
                'fuel_type'           => $data['fuel_type'],
                'status'              => 'available',
                'odometer_km'         => (int)$data['odometer_km'],
                'registration_expiry' => !empty($input['registration_expiry']) ? $input['registration_expiry'] : null,
                'insurance_expiry'    => !empty($input['insurance_expiry']) ? $input['insurance_expiry'] : null,
                'created_at'          => date('Y-m-d H:i:s'),
                'updated_at'          => date('Y-m-d H:i:s'),
            ]);

            $this->auditLog->record(
                'vehicle_created',
                'vehicles',
                $vehicleId,
                null,
                ['vehicle_number' => $cleanNumber, 'make_model' => $data['make_model']],
                Session::getUserId()
            );

            return $this->redirect(url('/office/vehicles'), 'success', "Vehicle {$cleanNumber} ({$data['make_model']}) registered successfully into fleet.");

        } catch (ValidationException $e) {
            Session::setFlash('form_errors', $e->getErrors());
            Session::setFlash('form_old', $request->all());
            Session::setFlash('danger', $e->getMessage());
            return $this->redirect(url('/office/vehicles/create'));
        } catch (Throwable $e) {
            Session::setFlash('form_old', $request->all());
            Session::setFlash('danger', 'Error registering vehicle: ' . $e->getMessage());
            return $this->redirect(url('/office/vehicles/create'));
        }
    }

    /**
     * Update vehicle readiness / maintenance status
     */
    public function updateStatus(Request $request, array $params): Response
    {
        $id = (int)($params['id'] ?? 0);
        $status = $request->input('status');

        if (!in_array($status, ['available', 'maintenance', 'retired'], true)) {
            return $this->redirect(url('/office/vehicles'), 'danger', 'Invalid vehicle status update specified.');
        }

        $vehicle = $this->vehicleModel->find($id);
        if (!$vehicle) {
            return $this->redirect(url('/office/vehicles'), 'danger', 'Vehicle not found.');
        }

        if ($vehicle['status'] === 'on_trip' && $status !== 'on_trip') {
            return $this->redirect(url('/office/vehicles'), 'danger', "Cannot change status of vehicle {$vehicle['vehicle_number']} while actively executing a trip.");
        }

        $this->vehicleModel->update($id, ['status' => $status]);

        $this->auditLog->record(
            'vehicle_status_updated',
            'vehicles',
            $id,
            ['status' => $vehicle['status']],
            ['status' => $status],
            Session::getUserId()
        );

        return $this->redirect(url('/office/vehicles'), 'success', "Vehicle {$vehicle['vehicle_number']} status updated to {$status}.");
    }
}
