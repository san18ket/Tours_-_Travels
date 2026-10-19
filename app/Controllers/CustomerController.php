<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Logger;
use App\Core\ValidationException;
use App\Models\Customer;
use App\Models\AuditLog;
use Throwable;

class CustomerController extends Controller
{
    private Customer $customerModel;
    private AuditLog $auditLog;

    public function __construct()
    {
        $this->customerModel = new Customer();
        $this->auditLog = new AuditLog();
    }

    /**
     * Display Customer Directory with search, filters, and pagination
     */
    public function index(Request $request): Response
    {
        $search = trim((string)$request->get('search', ''));
        $status = trim((string)$request->get('status', ''));
        $page = max(1, (int)$request->get('page', 1));
        $perPage = 12;

        $totalCustomers = $this->customerModel->countFiltered($search, $status);
        $customers = $this->customerModel->getPaginated($search, $status, $page, $perPage);
        $totalPages = max(1, (int)ceil($totalCustomers / $perPage));

        // Metric summaries
        $totalActive = $this->customerModel->count("`status` = 'active'");
        $totalInactive = $this->customerModel->count("`status` = 'inactive'");

        return $this->render('office/customers/index', [
            'customers'       => $customers,
            'totalCustomers'  => $totalCustomers,
            'totalActive'     => $totalActive,
            'totalInactive'   => $totalInactive,
            'currentPage'     => $page,
            'totalPages'      => $totalPages,
            'search'          => $search,
            'selectedStatus'  => $status,
            'title'           => 'Customer Directory',
        ], 'layouts/office');
    }

    /**
     * Display the Premium Customer Registration form
     * Target page: /office/users/create & /office/customers/create
     */
    public function create(Request $request): Response
    {
        $generatedCode = $this->customerModel->generateCustomerCode();

        return $this->render('office/customers/create', [
            'title'          => 'Add New Customer Profile',
            'customerCode'   => $generatedCode,
            'errors'         => Session::getFlash('form_errors') ?? [],
            'old'            => Session::getFlash('form_old') ?? [],
            'duplicates'     => Session::getFlash('potential_duplicates') ?? [],
        ], 'layouts/office');
    }

    /**
     * Store new customer profile
     * Strictly creates a customer profile without login credentials or administrative roles
     */
    public function store(Request $request): Response
    {
        try {
            $input = $request->all();
            if (empty($input['phone']) && !empty($input['primary_mobile'])) {
                $input['phone'] = $input['primary_mobile'];
            }

            // 1. Server-side validation
            $rules = [
                'name'   => 'required|min:2|max:150',
                'phone'  => 'required|min:8|max:25',
                'status' => 'required|in:active,inactive',
            ];

            if (!empty($input['email'])) {
                $rules['email'] = 'email|max:150';
            }

            if (!empty($input['gender'])) {
                $rules['gender'] = 'in:male,female,non_binary,prefer_not_to_say,other';
            }

            $validated = $this->validate($input, $rules);

            // 2. Normalization
            $phone = preg_replace('/[^\d+]/', '', trim((string)$input['phone']));
            if (strlen($phone) < 8) {
                throw new ValidationException('Please provide a valid contact phone number.', [
                    'phone' => ['Primary mobile number is invalid.']
                ]);
            }

            $alternatePhone = !empty($input['alternate_phone']) ? preg_replace('/[^\d+]/', '', trim((string)$input['alternate_phone'])) : null;
            $email = !empty($input['email']) ? strtolower(trim((string)$input['email'])) : null;

            // 3. Duplicate detection flag (do not auto-merge; prompt staff if potential duplicate detected)
            $confirmDuplicate = !empty($input['confirm_duplicate']) && $input['confirm_duplicate'] === '1';
            if (!$confirmDuplicate) {
                $potentialDuplicates = $this->customerModel->findPotentialDuplicates($phone, $email);
                if (!empty($potentialDuplicates)) {
                    Session::setFlash('form_errors', [
                        'duplicate_warning' => 'A customer record with matching contact details already exists. Please review below.'
                    ]);
                    Session::setFlash('potential_duplicates', $potentialDuplicates);
                    Session::setFlash('form_old', $input);
                    return $this->redirect(url('/office/customers/create'));
                }
            }

            // 4. Guaranteed unguessable/unique reference code generated on server
            $customerCode = $this->customerModel->generateCustomerCode();

            $dateOfBirth = !empty($input['date_of_birth']) ? date('Y-m-d', strtotime($input['date_of_birth'])) : null;
            $marketingConsent = !empty($input['marketing_consent']) ? 1 : 0;

            // 5. Database insertion (Customer profile ONLY, no login credentials, no admin roles)
            $customerId = $this->customerModel->create([
                'customer_code'     => $customerCode,
                'user_id'           => null, // Staff-created customers do not have an office login account
                'title'             => !empty($input['title']) ? trim((string)$input['title']) : null,
                'name'              => trim((string)$input['name']),
                'date_of_birth'     => $dateOfBirth,
                'gender'            => !empty($input['gender']) ? $input['gender'] : null,
                'phone'             => $phone,
                'alternate_phone'   => $alternatePhone,
                'email'             => $email,
                'address_line'      => !empty($input['address_line']) ? trim((string)$input['address_line']) : null,
                'city'              => !empty($input['city']) ? trim((string)$input['city']) : null,
                'state'             => !empty($input['state']) ? trim((string)$input['state']) : null,
                'postal_code'       => !empty($input['postal_code']) ? trim((string)$input['postal_code']) : null,
                'country'           => !empty($input['country']) ? trim((string)$input['country']) : 'India',
                'status'            => $input['status'] ?? 'active',
                'marketing_consent' => $marketingConsent,
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s'),
            ]);

            // 6. Security & Audit Logging (No sensitive personal data logged)
            $this->auditLog->record(
                'customer_registered',
                'customers',
                $customerId,
                null,
                [
                    'customer_code' => $customerCode,
                    'status'        => $input['status'] ?? 'active',
                    'created_by'    => Session::getUserId(),
                ]
            );

            Session::setFlash('success', "Customer profile [{$customerCode}] for {$input['name']} registered successfully!");
            return $this->redirect(url('/office/customers/' . $customerId));

        } catch (ValidationException $e) {
            Session::setFlash('form_errors', $e->getErrors());
            Session::setFlash('form_old', $request->all());
            return $this->redirect(url('/office/customers/create'));
        } catch (Throwable $e) {
            Logger::error('Customer registration error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            Session::setFlash('error', 'An internal error occurred while registering customer profile. Please try again.');
            Session::setFlash('form_old', $request->all());
            return $this->redirect(url('/office/customers/create'));
        }
    }

    /**
     * Display Customer Profile and comprehensive travel booking ledger
     */
    public function show(Request $request, array $params): Response
    {
        $id = (int)($params['id'] ?? 0);
        $customer = $this->customerModel->find($id);

        if (!$customer) {
            Session::setFlash('error', 'Customer profile not found.');
            return $this->redirect(url('/office/customers'));
        }

        $bookings = $this->customerModel->getBookingHistory($id);

        return $this->render('office/customers/show', [
            'customer' => $customer,
            'bookings' => $bookings,
            'title'    => "Customer Profile — {$customer['name']} ({$customer['customer_code']})",
        ], 'layouts/office');
    }

    /**
     * Display edit customer profile form
     */
    public function edit(Request $request, array $params): Response
    {
        $id = (int)($params['id'] ?? 0);
        $customer = $this->customerModel->find($id);

        if (!$customer) {
            Session::setFlash('error', 'Customer profile not found.');
            return $this->redirect(url('/office/customers'));
        }

        return $this->render('office/customers/edit', [
            'customer' => $customer,
            'title'    => "Edit Customer Profile — {$customer['customer_code']}",
            'errors'   => Session::getFlash('form_errors') ?? [],
            'old'      => Session::getFlash('form_old') ?? [],
        ], 'layouts/office');
    }

    /**
     * Update customer profile
     */
    public function update(Request $request, array $params): Response
    {
        $id = (int)($params['id'] ?? 0);
        $customer = $this->customerModel->find($id);

        if (!$customer) {
            Session::setFlash('error', 'Customer profile not found.');
            return $this->redirect(url('/office/customers'));
        }

        try {
            $input = $request->all();

            $rules = [
                'name'   => 'required|min:2|max:150',
                'phone'  => 'required|min:8|max:25',
                'status' => 'required|in:active,inactive',
            ];

            if (!empty($input['email'])) {
                $rules['email'] = 'email|max:150';
            }

            $validated = $this->validate($input, $rules);

            $phone = preg_replace('/[^\d+]/', '', trim((string)$input['phone']));
            $alternatePhone = !empty($input['alternate_phone']) ? preg_replace('/[^\d+]/', '', trim((string)$input['alternate_phone'])) : null;
            $email = !empty($input['email']) ? strtolower(trim((string)$input['email'])) : null;
            $dateOfBirth = !empty($input['date_of_birth']) ? date('Y-m-d', strtotime($input['date_of_birth'])) : null;
            $marketingConsent = !empty($input['marketing_consent']) ? 1 : 0;

            $this->customerModel->update($id, [
                'title'             => !empty($input['title']) ? trim((string)$input['title']) : null,
                'name'              => trim((string)$input['name']),
                'date_of_birth'     => $dateOfBirth,
                'gender'            => !empty($input['gender']) ? $input['gender'] : null,
                'phone'             => $phone,
                'alternate_phone'   => $alternatePhone,
                'email'             => $email,
                'address_line'      => !empty($input['address_line']) ? trim((string)$input['address_line']) : null,
                'city'              => !empty($input['city']) ? trim((string)$input['city']) : null,
                'state'             => !empty($input['state']) ? trim((string)$input['state']) : null,
                'postal_code'       => !empty($input['postal_code']) ? trim((string)$input['postal_code']) : null,
                'country'           => !empty($input['country']) ? trim((string)$input['country']) : 'India',
                'status'            => $input['status'] ?? 'active',
                'marketing_consent' => $marketingConsent,
                'updated_at'        => date('Y-m-d H:i:s'),
            ]);

            $this->auditLog->record(
                'customer_updated',
                'customers',
                $id,
                ['status' => $customer['status']],
                ['status' => $input['status'] ?? 'active', 'updated_by' => Session::getUserId()]
            );

            Session::setFlash('success', "Customer profile [{$customer['customer_code']}] updated successfully.");
            return $this->redirect(url('/office/customers/' . $id));

        } catch (ValidationException $e) {
            Session::setFlash('form_errors', $e->getErrors());
            Session::setFlash('form_old', $request->all());
            return $this->redirect(url('/office/customers/' . $id . '/edit'));
        } catch (Throwable $e) {
            Logger::error('Customer update error: ' . $e->getMessage());
            Session::setFlash('error', 'Failed to update customer profile.');
            return $this->redirect(url('/office/customers/' . $id . '/edit'));
        }
    }
}
