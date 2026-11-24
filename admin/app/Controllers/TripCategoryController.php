<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\ValidationException;
use App\Models\TripCategory;
use App\Models\AuditLog;
use Throwable;

class TripCategoryController extends Controller
{
    private TripCategory $categoryModel;
    private AuditLog $auditLog;

    public function __construct()
    {
        $this->categoryModel = new TripCategory();
        $this->auditLog = new AuditLog();
    }

    /**
     * Display Trip Categories list with management table & mobile cards
     */
    public function index(Request $request): Response
    {
        // Single optimized query with LEFT JOIN booking count (eliminates N+1 query loop)
        $categories = $this->categoryModel->getAllWithUsageCount();

        $totalCount = count($categories);
        $activeCount = count(array_filter($categories, fn($c) => ($c['status'] ?? '') === 'active'));
        $inactiveCount = $totalCount - $activeCount;

        return $this->render('office/trip_categories/index', [
            'categories'    => $categories,
            'totalCount'    => $totalCount,
            'activeCount'   => $activeCount,
            'inactiveCount' => $inactiveCount,
            'title'         => 'Trip Categories Management',
        ], 'layouts/office');
    }

    /**
     * Store a new Trip Category
     */
    public function store(Request $request): Response
    {
        try {
            $input = $request->all();
            $data = $this->validate($input, [
                'name' => 'required|min:2|max:100',
            ]);

            $name = trim($data['name']);
            $existing = $this->categoryModel->findByName($name);
            if ($existing) {
                throw new ValidationException(__('trip_category.name_exists', ['name' => $name]));
            }

            $slug = TripCategory::slugify($name);
            $existingSlug = $this->categoryModel->findBySlug($slug);
            if ($existingSlug) {
                $slug .= '-' . bin2hex(random_bytes(2));
            }

            $description = trim((string)($input['description'] ?? ''));
            $status = in_array($input['status'] ?? 'active', ['active', 'inactive'], true) ? $input['status'] : 'active';
            $sortOrder = max(0, (int)($input['sort_order'] ?? 0));

            $newId = $this->categoryModel->create([
                'name'        => $name,
                'slug'        => $slug,
                'description' => !empty($description) ? $description : null,
                'status'      => $status,
                'sort_order'  => $sortOrder,
            ]);

            $this->auditLog->record('trip_category_created', 'trip_categories', $newId, null, [
                'name' => $name,
                'slug' => $slug
            ]);

            $msg = __('trip_category.created_success', ['name' => $name]);
            Session::setFlash('success', $msg);

            if ($request->isAjax()) {
                return $this->json(['success' => true, 'message' => $msg, 'redirect' => url('/office/trip-categories')]);
            }
            return $this->redirect(url('/office/trip-categories'));
        } catch (ValidationException $e) {
            Session::setFlash('danger', $e->getMessage());
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return $this->redirect(url('/office/trip-categories'));
        } catch (Throwable $e) {
            Session::setFlash('danger', __('common.something_went_wrong'));
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => __('common.something_went_wrong')], 500);
            }
            return $this->redirect(url('/office/trip-categories'));
        }
    }

    /**
     * Update an existing Trip Category
     */
    public function update(Request $request, array $params): Response
    {
        $id = (int)($params['id'] ?? 0);
        $category = $this->categoryModel->find($id);

        if (!$category) {
            Session::setFlash('danger', __('trip_category.not_found'));
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => __('trip_category.not_found')], 404);
            }
            return $this->redirect(url('/office/trip-categories'));
        }

        try {
            $input = $request->all();
            $data = $this->validate($input, [
                'name' => 'required|min:2|max:100',
            ]);

            $name = trim($data['name']);
            $existing = $this->categoryModel->findByName($name);
            if ($existing && (int)$existing['id'] !== $id) {
                throw new ValidationException(__('trip_category.name_exists', ['name' => $name]));
            }

            $description = trim((string)($input['description'] ?? ''));
            $status = in_array($input['status'] ?? $category['status'], ['active', 'inactive'], true) ? $input['status'] : 'active';
            $sortOrder = isset($input['sort_order']) ? max(0, (int)$input['sort_order']) : (int)$category['sort_order'];

            $this->categoryModel->update($id, [
                'name'        => $name,
                'description' => !empty($description) ? $description : null,
                'status'      => $status,
                'sort_order'  => $sortOrder,
                'updated_at'  => date('Y-m-d H:i:s'),
            ]);

            $this->auditLog->record('trip_category_updated', 'trip_categories', $id, $category, [
                'name'   => $name,
                'status' => $status
            ]);

            $msg = __('trip_category.updated_success', ['name' => $name]);
            Session::setFlash('success', $msg);

            if ($request->isAjax()) {
                return $this->json(['success' => true, 'message' => $msg, 'redirect' => url('/office/trip-categories')]);
            }
            return $this->redirect(url('/office/trip-categories'));
        } catch (ValidationException $e) {
            Session::setFlash('danger', $e->getMessage());
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return $this->redirect(url('/office/trip-categories'));
        } catch (Throwable $e) {
            Session::setFlash('danger', __('common.something_went_wrong'));
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => __('common.something_went_wrong')], 500);
            }
            return $this->redirect(url('/office/trip-categories'));
        }
    }

    /**
     * AJAX Toggle category status (active <-> inactive)
     */
    public function toggleStatus(Request $request, array $params): Response
    {
        $id = (int)($params['id'] ?? 0);
        $category = $this->categoryModel->find($id);

        if (!$category) {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => __('trip_category.not_found')], 404);
            }
            Session::setFlash('danger', __('trip_category.not_found'));
            return $this->redirect(url('/office/trip-categories'));
        }

        try {
            $newStatus = ($category['status'] === 'active') ? 'inactive' : 'active';
            $this->categoryModel->update($id, [
                'status'     => $newStatus,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $this->auditLog->record('trip_category_status_toggled', 'trip_categories', $id, ['status' => $category['status']], ['status' => $newStatus]);
            $msg = __('trip_category.status_changed', ['name' => $category['name'], 'status' => ucfirst($newStatus)]);

            if ($request->isAjax()) {
                return $this->json([
                    'success'   => true,
                    'status'    => $newStatus,
                    'is_active' => ($newStatus === 'active'),
                    'message'   => $msg,
                ]);
            }

            Session::setFlash('success', $msg);
            return $this->redirect(url('/office/trip-categories'));
        } catch (Throwable $e) {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => __('common.something_went_wrong')], 500);
            }
            Session::setFlash('danger', __('common.something_went_wrong'));
            return $this->redirect(url('/office/trip-categories'));
        }
    }

    /**
     * Delete or Disable Category
     * (If category is in use by bookings, soft-disable it to preserve historical integrity)
     */
    public function delete(Request $request, array|int|string $params): Response
    {
        $id = is_array($params) ? (int)($params['id'] ?? 0) : (int)$params;
        $category = $this->categoryModel->find($id);

        if (!$category) {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => __('trip_category.not_found')], 404);
            }
            Session::setFlash('danger', __('trip_category.not_found'));
            return $this->redirect(url('/office/trip-categories'));
        }

        try {
            $usageCount = $this->categoryModel->countBookingsUsing($id);
            if ($usageCount > 0) {
                // Do not hard delete! Disable it so historical bookings remain valid
                $this->categoryModel->update($id, [
                    'status'     => 'inactive',
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

                $msg = __('trip_category.disabled_due_to_usage', ['name' => $category['name'], 'count' => $usageCount]);
                Session::setFlash('info', $msg);

                if ($request->isAjax()) {
                    return $this->json([
                        'success'  => true,
                        'disabled' => true,
                        'message'  => $msg,
                        'redirect' => url('/office/trip-categories'),
                    ]);
                }
                return $this->redirect(url('/office/trip-categories'));
            }

            // If not used in any booking, safe to delete
            $this->categoryModel->delete($id);
            $this->auditLog->record('trip_category_deleted', 'trip_categories', $id, $category, null);

            $msg = __('trip_category.deleted_success', ['name' => $category['name']]);
            Session::setFlash('success', $msg);

            if ($request->isAjax()) {
                return $this->json([
                    'success'  => true,
                    'message'  => $msg,
                    'redirect' => url('/office/trip-categories'),
                ]);
            }
            return $this->redirect(url('/office/trip-categories'));
        } catch (Throwable $e) {
            $errMsg = __('trip_category.failed_to_delete');
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => $errMsg], 500);
            }
            Session::setFlash('danger', $errMsg);
            return $this->redirect(url('/office/trip-categories'));
        }
    }
}

