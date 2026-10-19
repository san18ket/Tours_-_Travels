<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Vehicle;
use App\Models\DriverProfile;
use App\Models\User;
use App\Models\AuditLog;
use Throwable;

class FleetController extends Controller
{
    private Vehicle $vehicleModel;
    private DriverProfile $driverModel;
    private User $userModel;
    private AuditLog $auditLog;

    public function __construct()
    {
        $this->vehicleModel = new Vehicle();
        $this->driverModel = new DriverProfile();
        $this->userModel = new User();
        $this->auditLog = new AuditLog();
    }

    public function index(Request $request): Response
    {
        $vehicles = $this->vehicleModel->all('id DESC');
        $drivers = $this->driverModel->getAllWithUserInfo();

        return $this->render('office/fleet/index', [
            'vehicles' => $vehicles,
            'drivers'  => $drivers,
            'title'    => 'Fleet & Driver Roster Management',
        ], 'layouts/office');
    }

    public function createVehicle(Request $request): Response
    {
        return $this->render('office/fleet/create_vehicle', [
            'title' => 'Register New Fleet Vehicle',
        ], 'layouts/office');
    }

    public function storeVehicle(Request $request): Response
    {
        try {
            $data = $this->validate($request->all(), [
                'vehicle_number'   => 'required|min:4|max:30',
                'make_model'       => 'required|min:3|max:100',
                'vehicle_type'     => 'required|in:sedan,suv,tempo_traveller,luxury_bus,van',
                'seating_capacity' => 'required|numeric',
                'fuel_type'        => 'required|in:diesel,petrol,cng,electric',
                'odometer_km'      => 'required|numeric',
            ]);

            $existing = $this->vehicleModel->findBy('vehicle_number', $data['vehicle_number']);
            if ($existing) {
                throw new \RuntimeException("Vehicle registration number {$data['vehicle_number']} already exists.");
            }

            $vehicleId = $this->vehicleModel->create([
                'vehicle_number'      => strtoupper(trim($data['vehicle_number'])),
                'make_model'          => $data['make_model'],
                'vehicle_type'        => $data['vehicle_type'],
                'seating_capacity'    => (int)$data['seating_capacity'],
                'fuel_type'           => $data['fuel_type'],
                'status'              => 'available',
                'odometer_km'         => (int)$data['odometer_km'],
                'registration_expiry' => $request->input('registration_expiry'),
                'insurance_expiry'    => $request->input('insurance_expiry'),
            ]);

            $this->auditLog->record('vehicle_created', 'vehicles', $vehicleId, null, ['vehicle_number' => $data['vehicle_number']]);
            Session::setFlash('success', 'Vehicle registered successfully into fleet.');
            return $this->redirect(url('/office/fleet'));
        } catch (Throwable $e) {
            Session::setFlash('danger', $e->getMessage());
            return $this->redirect(url('/office/fleet/vehicles/create'));
        }
    }

    public function updateVehicleStatus(Request $request, array $params): Response
    {
        $id = (int)($params['id'] ?? 0);
        $status = $request->input('status');

        if (!in_array($status, ['available', 'maintenance', 'retired'], true)) {
            Session::setFlash('danger', 'Invalid vehicle status update.');
            return $this->redirect(url('/office/fleet'));
        }

        $vehicle = $this->vehicleModel->find($id);
        if ($vehicle && $vehicle['status'] === 'on_trip' && $status !== 'on_trip') {
            Session::setFlash('danger', 'Cannot change status of a vehicle currently executing an active trip.');
            return $this->redirect(url('/office/fleet'));
        }

        $this->vehicleModel->update($id, ['status' => $status]);
        $this->auditLog->record('vehicle_status_updated', 'vehicles', $id, ['status' => $vehicle['status']], ['status' => $status]);

        Session::setFlash('success', "Vehicle status updated to {$status}.");
        return $this->redirect(url('/office/fleet'));
    }

    public function toggleDriverDuty(Request $request, array $params): Response
    {
        $id = (int)($params['id'] ?? 0);
        $driver = $this->driverModel->find($id);
        if (!$driver) {
            Session::setFlash('danger', 'Driver profile not found.');
            return $this->redirect(url('/office/fleet'));
        }

        if ($driver['duty_status'] === 'on_trip') {
            Session::setFlash('danger', 'Cannot toggle duty status while driver is on an active trip.');
            return $this->redirect(url('/office/fleet'));
        }

        $newDuty = ($driver['duty_status'] === 'on_duty') ? 'off_duty' : 'on_duty';
        $this->driverModel->update($id, ['duty_status' => $newDuty]);
        $this->auditLog->record('driver_duty_toggled', 'driver_profiles', $id, ['duty_status' => $driver['duty_status']], ['duty_status' => $newDuty]);

        Session::setFlash('success', "Driver duty updated to {$newDuty}.");
        return $this->redirect(url('/office/fleet'));
    }
}
