<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Tour;
use App\Models\AuditLog;
use Throwable;

class TourController extends Controller
{
    private Tour $tourModel;
    private AuditLog $auditLog;

    public function __construct()
    {
        $this->tourModel = new Tour();
        $this->auditLog = new AuditLog();
    }

    public function index(Request $request): Response
    {
        $category = $request->input('category');
        $status = $request->input('status');
        $search = $request->input('q');

        $conditions = ["1=1"];
        $params = [];

        if ($category) {
            $conditions[] = "`category` = ?";
            $params[] = $category;
        }

        if ($status) {
            $conditions[] = "`status` = ?";
            $params[] = $status;
        }

        if ($search) {
            $conditions[] = "(`title` LIKE ? OR `slug` LIKE ? OR `pickup_city` LIKE ? OR `destination_city` LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $sql = implode(' AND ', $conditions);
        $page = max(1, (int)$request->input('page', 1));
        $perPage = in_array((int)$request->input('per_page'), [10, 25, 50, 100], true) ? (int)$request->input('per_page') : 10;
        $offset = ($page - 1) * $perPage;

        $totalRecords = $this->tourModel->count($sql, $params);
        $tours = $this->tourModel->where($sql, $params, 'id DESC', $perPage, $offset);
        $totalPages = max(1, (int)ceil($totalRecords / $perPage));

        return $this->render('office/tours/index', [
            'tours'            => $tours,
            'selectedCategory' => $category,
            'selectedStatus'   => $status,
            'search'           => $search,
            'currentPage'      => $page,
            'totalPages'       => $totalPages,
            'totalRecords'     => $totalRecords,
            'perPage'          => $perPage,
            'title'            => 'Tour Packages Management',
        ], 'layouts/office');
    }

    public function create(Request $request): Response
    {
        return $this->render('office/tours/create', [
            'title' => 'Create New Tour Package',
        ], 'layouts/office');
    }

    public function store(Request $request): Response
    {
        try {
            $data = $this->validate($request->all(), [
                'title'            => 'required|min:3|max:150',
                'category'         => 'required|in:heritage,hill_station,pilgrimage,wildlife,beach,custom',
                'duration_days'    => 'required|numeric',
                'duration_nights'  => 'required|numeric',
                'pickup_city'      => 'required|max:100',
                'destination_city' => 'required|max:100',
                'base_price'       => 'required|numeric',
                'max_passengers'   => 'required|numeric',
            ]);

            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['title']), '-'));
            $existing = $this->tourModel->findBySlug($slug);
            if ($existing) {
                $slug .= '-' . rand(100, 999);
            }

            $tourId = $this->tourModel->create([
                'slug'             => $slug,
                'title'            => $data['title'],
                'category'         => $data['category'],
                'duration_days'    => (int)$data['duration_days'],
                'duration_nights'  => (int)$data['duration_nights'],
                'pickup_city'      => $data['pickup_city'],
                'destination_city' => $data['destination_city'],
                'base_price'       => (float)$data['base_price'],
                'max_passengers'   => (int)$data['max_passengers'],
                'description'      => $request->input('description'),
                'inclusions'       => $request->input('inclusions'),
                'exclusions'       => $request->input('exclusions'),
                'status'           => $request->input('status', 'published'),
            ]);

            $this->auditLog->record('tour_created', 'tours', $tourId, null, ['title' => $data['title']]);
            Session::setFlash('success', 'Tour package created successfully.');
            return $this->redirect(url('/office/tours'));
        } catch (Throwable $e) {
            Session::setFlash('danger', $e->getMessage());
            return $this->redirect(url('/office/tours/create'));
        }
    }

    public function edit(Request $request, array $params): Response
    {
        $id = (int)($params['id'] ?? 0);
        $tour = $this->tourModel->find($id);
        if (!$tour) {
            Session::setFlash('danger', 'Tour package not found.');
            return $this->redirect(url('/office/tours'));
        }

        return $this->render('office/tours/edit', [
            'tour'  => $tour,
            'title' => 'Edit Tour Package',
        ], 'layouts/office');
    }

    public function update(Request $request, array $params): Response
    {
        $id = (int)($params['id'] ?? 0);
        $tour = $this->tourModel->find($id);
        if (!$tour) {
            Session::setFlash('danger', 'Tour package not found.');
            return $this->redirect(url('/office/tours'));
        }

        try {
            $data = $this->validate($request->all(), [
                'title'            => 'required|min:3|max:150',
                'category'         => 'required|in:heritage,hill_station,pilgrimage,wildlife,beach,custom',
                'duration_days'    => 'required|numeric',
                'duration_nights'  => 'required|numeric',
                'pickup_city'      => 'required|max:100',
                'destination_city' => 'required|max:100',
                'base_price'       => 'required|numeric',
                'max_passengers'   => 'required|numeric',
            ]);

            $this->tourModel->update($id, [
                'title'            => $data['title'],
                'category'         => $data['category'],
                'duration_days'    => (int)$data['duration_days'],
                'duration_nights'  => (int)$data['duration_nights'],
                'pickup_city'      => $data['pickup_city'],
                'destination_city' => $data['destination_city'],
                'base_price'       => (float)$data['base_price'],
                'max_passengers'   => (int)$data['max_passengers'],
                'description'      => $request->input('description'),
                'inclusions'       => $request->input('inclusions'),
                'exclusions'       => $request->input('exclusions'),
                'status'           => $request->input('status', 'published'),
            ]);

            $this->auditLog->record('tour_updated', 'tours', $id, $tour, $data);
            Session::setFlash('success', 'Tour package updated successfully.');
            return $this->redirect(url('/office/tours'));
        } catch (Throwable $e) {
            Session::setFlash('danger', $e->getMessage());
            return $this->redirect(url("/office/tours/{$id}/edit"));
        }
    }
}
