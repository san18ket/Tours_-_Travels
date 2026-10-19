<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\DriverProfile;
use App\Models\Dispatch;
use App\Services\DispatchService;
use Throwable;

class DriverPortalController extends Controller
{
    private DriverProfile $driverModel;
    private Dispatch $dispatchModel;
    private DispatchService $dispatchService;

    public function __construct()
    {
        $this->driverModel = new DriverProfile();
        $this->dispatchModel = new Dispatch();
        $this->dispatchService = new DispatchService();
    }

    private function getAuthenticatedDriver(): array
    {
        $userId = Session::getUserId();
        $driver = $this->driverModel->findByUserId($userId);
        if (!$driver) {
            throw new \RuntimeException("Driver profile record not found for your account.");
        }
        return $driver;
    }

    public function duty(Request $request): Response
    {
        try {
            $driver = $this->getAuthenticatedDriver();
            $activeTrip = $this->dispatchModel->getActiveAssignmentForDriver((int)$driver['id']);
            $history = $this->dispatchModel->getDriverHistory((int)$driver['id']);
            $recentCompleted = array_filter($history, fn($h) => $h['status'] === 'completed');
            $recentCompleted = array_slice($recentCompleted, 0, 5);

            return $this->render('driver/duty', [
                'driver'          => $driver,
                'activeTrip'      => $activeTrip,
                'recentCompleted' => $recentCompleted,
                'title'           => 'Chauffeur Duty Console',
            ], 'layouts/driver');
        } catch (Throwable $e) {
            Session::setFlash('danger', $e->getMessage());
            return $this->redirect(url('/login'));
        }
    }

    public function toggleDuty(Request $request): Response
    {
        try {
            $driver = $this->getAuthenticatedDriver();
            if ($driver['duty_status'] === 'on_trip') {
                Session::setFlash('danger', 'Cannot change duty status while executing an active trip.');
                return $this->redirect(url('/driver/duty'));
            }

            $newStatus = ($driver['duty_status'] === 'on_duty') ? 'off_duty' : 'on_duty';
            $this->driverModel->update($driver['id'], ['duty_status' => $newStatus]);

            Session::setFlash('success', 'Your duty status is now: ' . strtoupper(str_replace('_', ' ', $newStatus)));
        } catch (Throwable $e) {
            Session::setFlash('danger', $e->getMessage());
        }

        return $this->redirect(url('/driver/duty'));
    }

    public function acceptTrip(Request $request, array $params): Response
    {
        $dispatchId = (int)($params['id'] ?? 0);
        try {
            $driver = $this->getAuthenticatedDriver();
            $this->dispatchService->driverAccept($dispatchId, (int)$driver['id']);
            Session::setFlash('success', 'Trip assignment accepted. Proceed to pickup location.');
        } catch (Throwable $e) {
            Session::setFlash('danger', $e->getMessage());
        }

        return $this->redirect(url('/driver/duty'));
    }

    public function startTrip(Request $request, array $params): Response
    {
        $dispatchId = (int)($params['id'] ?? 0);
        try {
            $driver = $this->getAuthenticatedDriver();
            $odo = (int)$request->input('start_odometer', 0);
            $notes = $request->input('driver_notes');

            if ($odo <= 0) {
                throw new \RuntimeException('Please enter a valid starting odometer reading.');
            }

            $this->dispatchService->startTrip($dispatchId, (int)$driver['id'], $odo, $notes);
            Session::setFlash('success', 'Trip commenced! Live telemetry is now transmitting.');
        } catch (Throwable $e) {
            Session::setFlash('danger', $e->getMessage());
        }

        return $this->redirect(url('/driver/duty'));
    }

    public function completeTrip(Request $request, array $params): Response
    {
        $dispatchId = (int)($params['id'] ?? 0);
        try {
            $driver = $this->getAuthenticatedDriver();
            $odo = (int)$request->input('end_odometer', 0);
            $notes = $request->input('driver_notes');

            if ($odo <= 0) {
                throw new \RuntimeException('Please enter a valid ending odometer reading.');
            }

            $this->dispatchService->completeTrip($dispatchId, (int)$driver['id'], $odo, $notes);
            Session::setFlash('success', 'Trip completed successfully! Vehicle returned to available fleet.');
        } catch (Throwable $e) {
            Session::setFlash('danger', $e->getMessage());
        }

        return $this->redirect(url('/driver/duty'));
    }

    public function history(Request $request): Response
    {
        try {
            $driver = $this->getAuthenticatedDriver();
            $history = $this->dispatchModel->getDriverHistory((int)$driver['id']);

            return $this->render('driver/history', [
                'driver'  => $driver,
                'history' => $history,
                'title'   => 'My Completed Trips',
            ], 'layouts/driver');
        } catch (Throwable $e) {
            Session::setFlash('danger', $e->getMessage());
            return $this->redirect(url('/driver/duty'));
        }
    }
}
