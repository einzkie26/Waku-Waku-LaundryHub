<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Order;
use App\Models\User;
use App\Services\AdminReportService;
use App\Services\LaundryOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-07 10:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function user(array $attributes = []): User
    {
        return User::create(array_merge([
            'name' => 'Aki Tanaka', 'username' => 'aki-'.Str::random(8),
            'phone' => '09123456789', 'address' => '12 Green Street',
            'password' => 'laundry-test', 'role' => 'customer',
        ], $attributes));
    }

    private function admin(): User
    {
        $admin = $this->user(['name' => 'Mika Santos', 'role' => 'admin']);
        $this->actingAs($admin);
        return $admin;
    }

    private function employee(): Employee
    {
        return Employee::create([
            'name' => 'Lia Reyes', 'position' => 'Staff', 'contact' => '09112223333',
            'email' => Str::random(8).'@example.test', 'hire_date' => '2026-01-01',
            'shift' => 'Morning', 'status' => 'Active', 'password' => Hash::make('staff-test'),
        ]);
    }

    private function details(array $overrides = []): array
    {
        return array_merge([
            'service' => 'Wash & Fold', 'laundry_style' => 'Regular Wash', 'fabric_type' => 'Mixed',
            'weight' => 2.5, 'date' => '2026-11-08', 'time' => '09:00',
            'additional_services' => ['Fabric Softener'], 'payment_status' => 'pending',
        ], $overrides);
    }

    private function order(array $attributes = []): Order
    {
        $customer = $this->user();
        return app(LaundryOrderService::class)->create($this->details($attributes), $customer->id, 'customer', $customer->id);
    }

    private function element(string $html, string $id): \DOMElement
    {
        $document = new \DOMDocument();
        @$document->loadHTML($html);
        $element = (new \DOMXPath($document))->query('//*[@id="'.$id.'"]')->item(0);
        $this->assertInstanceOf(\DOMElement::class, $element, 'Missing element: '.$id);

        return $element;
    }

    public function test_admin_pages_require_an_admin_account(): void
    {
        $this->get('/admin/dashboard')->assertRedirect(route('login.form'));
        $this->actingAs($this->user());
        foreach (['/admin/dashboard', '/admin/orders', '/admin/customers', '/admin/employees', '/admin/reports/revenue-report', '/admin/settings/profile'] as $path) {
            $this->get($path)->assertRedirect(route('landing'));
        }
    }

    public function test_every_admin_screen_renders_with_an_empty_database(): void
    {
        $this->admin();
        foreach ([
            '/admin/dashboard', '/admin/orders', '/admin/orders/create', '/admin/customers',
            '/admin/customers/create', '/admin/employees',
            '/admin/reports/revenue-report', '/admin/reports/service-stats', '/admin/reports/attendance',
            '/admin/settings/profile',
        ] as $path) {
            $this->get($path)->assertOk()->assertSee('admin-workspace.css')->assertDontSee('adminlte.min.css');
        }
    }

    public function test_populated_customer_employee_and_order_details_render(): void
    {
        $this->admin();
        $order = $this->order();
        $employee = $this->employee();
        foreach ([route('orders.show', $order->id), route('admin.customers.show', $order->user_id), route('admin.customers.edit', $order->user_id), route('employees.show', $employee->id)] as $path) {
            $this->get($path)->assertOk();
        }
        $this->get(route('orders.create', ['customer' => $order->user_id]))->assertOk()->assertSee($order->user->name);
    }

    public function test_revenue_uses_payment_dates_includes_extras_and_excludes_refunds_and_unpaid_orders(): void
    {
        $this->admin();
        $paid = $this->order();
        $paid->update(['payment_status' => 'paid', 'payment_method' => 'Cash', 'paid_at' => now(), 'created_at' => now()->subMonths(2)]);
        $refunded = $this->order();
        $refunded->update(['payment_status' => 'paid', 'paid_at' => now(), 'status' => 'Refunded']);
        $unpaid = $this->order();
        $report = app(AdminReportService::class)->revenue('week');

        $this->assertEquals(155, $report['totalRevenue']);
        $this->assertSame(1, $report['totalPaidOrders']);
        $this->assertCount(7, $report['series']);
        $this->assertEquals(155 / 7, $report['averageDaily']);
        $this->get('/admin/reports/revenue-report')->assertOk()->assertSee($paid->reference_no)
            ->assertDontSee($refunded->reference_no)->assertDontSee($unpaid->reference_no)->assertSee('155.00');
        $this->get('/admin/dashboard')->assertOk()->assertSee('155.00');
    }

    public function test_revenue_zero_fills_days_and_supports_monthly_and_legacy_payments(): void
    {
        $this->admin();
        $order = $this->order();
        $order->update(['payment_status' => 'paid', 'paid_at' => null]);
        $report = app(AdminReportService::class)->revenue('month');
        $this->assertCount(30, $report['series']);
        $this->assertEquals(155, $report['totalRevenue']);
        $this->assertEquals(0, $report['series']->first()['revenue']);
        $this->get('/admin/reports/revenue-report?period=month')->assertOk();
        $this->get('/admin/dashboard?period=month')->assertOk();
    }

    public function test_revenue_pdf_download_is_a_real_pdf(): void
    {
        $this->admin();
        $order = $this->order();
        $order->update(['payment_status' => 'paid', 'paid_at' => now()]);
        $response = $this->get('/admin/reports/revenue-report/download?period=week');
        $response->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_order_filters_combine_search_progress_service_and_payment(): void
    {
        $this->admin();
        $matching = $this->order();
        $matching->update(['status' => 'Ready', 'payment_status' => 'paid', 'paid_at' => now()]);
        $other = $this->order(['service' => 'Ironing']);
        $query = http_build_query(['q' => $matching->reference_no, 'status' => 'Ready', 'payment' => 'paid', 'service' => 'Wash & Fold']);
        $this->get('/admin/orders?'.$query)->assertOk()->assertSee($matching->reference_no)->assertDontSee($other->reference_no);
    }

    public function test_admin_order_creation_uses_shared_prices_and_records_admin_history(): void
    {
        $admin = $this->admin();
        $customer = $this->user();
        $this->post('/admin/orders', $this->details(['user_id' => $customer->id, 'total_amount' => 1]))
            ->assertRedirect()->assertSessionHasNoErrors();
        $order = Order::sole();
        $this->assertEquals(155, $order->total_amount);
        $this->assertSame('admin', $order->operator_type);
        $this->assertDatabaseHas('order_status_histories', ['order_id' => $order->id, 'changed_by_type' => 'admin', 'changed_by_id' => $admin->id, 'status' => 'Pending']);
    }

    public function test_admin_order_validation_preserves_field_errors(): void
    {
        $this->admin();
        $this->post('/admin/orders', $this->details(['user_id' => $this->user()->id, 'service' => 'Unknown', 'weight' => 0]))
            ->assertSessionHasErrors(['service', 'weight']);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_admin_progress_uses_the_next_action_and_rejects_stale_forms(): void
    {
        $this->admin();
        $order = $this->order();
        $this->patch(route('orders.updateStatus', $order->id), ['current_status' => 'Pending'])->assertRedirect();
        $this->assertSame('Picked Up', $order->fresh()->status);
        $this->patchJson(route('orders.updateStatus', $order->id), ['current_status' => 'Pending'])->assertUnprocessable();
        $this->assertSame('Picked Up', $order->fresh()->status);
    }

    public function test_admin_payment_keeps_progress_and_does_not_overwrite_an_existing_payment(): void
    {
        $this->admin();
        $order = $this->order();
        $this->post(route('orders.pay', $order->id), ['payment_method' => 'GCash'])->assertSessionHasErrors('payment_reference');
        $this->post(route('orders.pay', $order->id), ['payment_method' => 'GCash', 'payment_reference' => 'GC-001'])->assertRedirect();
        $this->assertSame('Pending', $order->fresh()->status);
        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->post(route('orders.pay', $order->id), ['payment_method' => 'Cash'])->assertRedirect();
        $this->assertSame('GCash', $order->fresh()->payment_method);
        $this->assertSame('GC-001', $order->fresh()->payment_reference);
    }

    public function test_pickup_rescheduling_is_limited_to_orders_awaiting_collection(): void
    {
        $this->admin();
        $order = $this->order();
        $data = ['new_delivery_date' => '2026-11-09', 'new_pickup_time' => '14:00'];
        $this->post(route('orders.reschedule', $order->id), $data)->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('2026-11-09', $order->fresh()->date);
        $this->assertSame('14:00', $order->fresh()->time);
        $order->update(['status' => 'Picked Up']);
        $this->post(route('orders.reschedule', $order->id), $data)->assertSessionHasErrors('date');
    }

    public function test_refund_records_a_full_refund_and_removes_it_from_collected_totals(): void
    {
        $this->admin();
        $order = $this->order();
        $order->update(['payment_status' => 'paid', 'payment_method' => 'Cash', 'paid_at' => now()]);
        $this->post(route('orders.refund', $order->id), ['refund_amount' => 50, 'refund_reason' => 'Test'])->assertSessionHasErrors('refund_amount');
        $this->assertSame('Pending', $order->fresh()->status);
        $this->post(route('orders.refund', $order->id), ['refund_amount' => 155, 'refund_reason' => 'Customer refunded in cash'])
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('Refunded', $order->fresh()->status);
        $this->assertDatabaseHas('order_status_histories', ['order_id' => $order->id, 'status' => 'Refunded', 'changed_by_type' => 'admin']);
        $this->assertEquals(0, app(AdminReportService::class)->revenue('week')['totalRevenue']);
    }

    public function test_employees_can_be_created_and_access_toggled_without_deleting_history(): void
    {
        $this->admin();
        $this->post('/admin/employees', [
            'name' => 'New Teammate', 'contact' => '09111222333', 'position' => 'Laundry attendant',
            'email' => 'teammate@example.test', 'hire_date' => '2026-11-07', 'shift' => 'Morning',
            'password' => 'team-password', 'password_confirmation' => 'team-password',
        ])->assertRedirect(route('employees.list'))->assertSessionHasNoErrors();
        $employee = Employee::sole();
        $this->assertTrue(Hash::check('team-password', $employee->password));
        $this->patch(route('employees.deactivate', $employee->id))->assertRedirect();
        $this->assertSame('Inactive', $employee->fresh()->status);
        $this->assertFalse($employee->fresh()->is_active);
        $this->patch(route('employees.activate', $employee->id))->assertRedirect();
        $this->assertSame('Active', $employee->fresh()->status);
        $this->assertDatabaseCount('employees', 1);
        $this->get('/admin/employees?status=active&q=Teammate')->assertOk()->assertSee('New Teammate');
    }

    public function test_admin_can_add_and_edit_a_customer_without_accepting_role_changes(): void
    {
        $this->admin();
        $data = ['name' => 'New Customer', 'username' => 'new-customer', 'phone' => '09111222333', 'address' => '23 Fresh Street', 'role' => 'admin'];
        $this->post('/admin/customers', $data + ['password' => 'customer-pass', 'password_confirmation' => 'customer-pass'])
            ->assertRedirect()->assertSessionHasNoErrors();
        $customer = User::where('username', 'new-customer')->sole();
        $this->assertSame('customer', $customer->role);
        $this->put(route('admin.customers.update', $customer->id), array_merge($data, ['address' => 'Updated address']))
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('Updated address', $customer->fresh()->address);
        $this->assertSame('customer', $customer->fresh()->role);
    }

    public function test_employee_add_links_open_the_team_modal_including_the_legacy_url(): void
    {
        $this->admin();
        $this->get('/admin/employees/add')->assertRedirect(route('employees.list', ['add' => 1]));
        $response = $this->get('/admin/employees?add=1')->assertOk()
            ->assertSee('data-bs-target="#employee-create-modal"', false)
            ->assertSee('data-open-on-load="true"', false);
        $this->assertSame(1, substr_count($response->getContent(), 'id="employee-create-modal"'));
        $this->get('/admin/employees')->assertOk()->assertSee('data-open-on-load="false"', false);
    }

    public function test_invalid_employee_details_reopen_the_modal_and_preserve_non_secret_input(): void
    {
        $this->admin();
        $this->from(route('employees.list'))->post('/admin/employees', [
            'name' => 'Draft Teammate', 'contact' => '09111222333', 'position' => 'Staff',
            'email' => 'invalid-email', 'hire_date' => '2026-11-08', 'shift' => 'Morning',
            'password' => 'staff-password', 'password_confirmation' => 'does-not-match',
        ])->assertRedirect(route('employees.list'))->assertSessionHasErrors(['email', 'password'], null, 'employeeForm');
        $this->get('/admin/employees')->assertOk()
            ->assertSee('data-open-on-load="true"', false)
            ->assertSee('value="Draft Teammate"', false)
            ->assertSee('Please check the employee details.')
            ->assertDontSee('value="staff-password"', false);
        $this->assertDatabaseCount('employees', 0);
    }

    public function test_employee_filter_validation_does_not_open_the_creation_modal(): void
    {
        $this->admin();
        $this->from(route('employees.list'))->get('/admin/employees?status=unknown')
            ->assertRedirect(route('employees.list'))->assertSessionHasErrors('status');
        $this->get('/admin/employees')->assertOk()->assertSee('data-open-on-load="false"', false);
    }

    public function test_modal_forms_render_with_unique_ids_and_blank_customer_details(): void
    {
        $this->admin();
        $this->user(['name' => 'Existing Customer']);
        foreach (['/admin/orders', '/admin/customers', '/admin/employees'] as $path) {
            $html = $this->get($path)->assertOk()->getContent();
            preg_match_all('/\bid="([^"]+)"/', $html, $matches);
            $this->assertSame(count($matches[1]), count(array_unique($matches[1])), 'Duplicate IDs on '.$path);
            $this->assertSame('false', $this->element($html, 'admin-settings-modal')->getAttribute('data-open-on-load'));
        }
        $html = $this->get('/admin/customers')->getContent();
        $this->assertSame('', $this->element($html, 'customer-create-name')->getAttribute('value'));
        $this->assertSame('Mika Santos', $this->element($html, 'settings-name')->getAttribute('value'));
    }

    public function test_customer_modal_creation_returns_to_the_filtered_customer_list(): void
    {
        $this->admin();
        $target = '/admin/customers?q=New&page=2';
        $this->from($target)->post('/admin/customers', [
            '_admin_modal' => 'customer-create', '_return_to' => $target,
            'name' => 'New Customer', 'username' => 'new-customer', 'phone' => '09111222333',
            'address' => '23 Fresh Street', 'password' => 'customer-pass',
            'password_confirmation' => 'customer-pass', 'role' => 'admin',
        ])->assertRedirect($target)->assertSessionHasNoErrors()->assertSessionHas('new_customer_id');
        $customer = User::where('username', 'new-customer')->sole();
        $this->assertSame('customer', $customer->role);
        $this->assertTrue(Hash::check('customer-pass', $customer->password));
        $html = $this->get($target)->assertOk()->assertSee('View the new customer')->getContent();
        $this->assertSame('false', $this->element($html, 'customer-create-modal')->getAttribute('data-open-on-load'));
    }

    public function test_customer_modal_errors_preserve_customer_input_and_leave_settings_alone(): void
    {
        $this->admin();
        $this->user(['username' => 'taken-name']);
        $this->from('/admin/customers')->post('/admin/customers', [
            '_admin_modal' => 'customer-create', '_return_to' => '/admin/customers',
            'name' => 'Draft Customer', 'username' => 'taken-name', 'phone' => '09111222333',
            'address' => '23 Fresh Street', 'password' => 'customer-pass', 'password_confirmation' => 'mismatch',
        ])->assertRedirect('/admin/customers')->assertSessionHasErrors(['username', 'password'], null, 'customerForm');
        $html = $this->get('/admin/customers')->assertOk()->getContent();
        $this->assertSame('true', $this->element($html, 'customer-create-modal')->getAttribute('data-open-on-load'));
        $this->assertSame('Draft Customer', $this->element($html, 'customer-create-name')->getAttribute('value'));
        $this->assertSame('', $this->element($html, 'customer-create-password')->getAttribute('value'));
        $this->assertSame('Mika Santos', $this->element($html, 'settings-name')->getAttribute('value'));
        $this->assertSame('false', $this->element($html, 'admin-settings-modal')->getAttribute('data-open-on-load'));
        $this->assertDatabaseMissing('users', ['name' => 'Draft Customer']);
    }

    public function test_order_modal_creates_a_paid_order_and_returns_to_the_filtered_order_list(): void
    {
        $this->admin();
        $customer = $this->user();
        $target = '/admin/orders?status=active&payment=paid';
        $data = $this->details([
            'user_id' => $customer->id, 'payment_status' => 'paid',
            'payment_method' => 'GCash', 'payment_reference' => 'PAY-MODAL-123',
        ]);
        $this->from($target)->post('/admin/orders', $data + [
            '_admin_modal' => 'order-create', '_return_to' => $target,
        ])->assertRedirect($target)->assertSessionHasNoErrors()->assertSessionHas('new_order_id');
        $order = Order::sole();
        $this->assertSame(155.0, (float) $order->total_amount);
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('PAY-MODAL-123', $order->payment_reference);
        $this->assertSame('Pending', $order->status);
        $this->assertSame('admin', $order->statusHistories()->sole()->changed_by_type);
        $html = $this->get($target)->assertOk()->assertSee('View the new order')->getContent();
        $this->assertSame('false', $this->element($html, 'order-create-modal')->getAttribute('data-open-on-load'));
    }

    public function test_order_modal_payment_errors_preserve_the_booking_and_reopen_the_modal(): void
    {
        $this->admin();
        $customer = $this->user();
        $this->from('/admin/orders')->post('/admin/orders', $this->details([
            'user_id' => $customer->id, 'payment_status' => 'paid', 'payment_method' => 'GCash',
        ]) + ['_admin_modal' => 'order-create'])->assertRedirect('/admin/orders')
            ->assertSessionHasErrors('payment_reference', null, 'orderForm');
        $html = $this->get('/admin/orders')->assertOk()->getContent();
        $this->assertSame('true', $this->element($html, 'order-create-modal')->getAttribute('data-open-on-load'));
        $this->assertSame('2.5', $this->element($html, 'weight')->getAttribute('value'));
        $this->assertSame('2026-11-08', $this->element($html, 'date')->getAttribute('value'));
        $this->assertSame('false', $this->element($html, 'admin-settings-modal')->getAttribute('data-open-on-load'));
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_order_modal_handles_service_validation_errors_in_the_same_form(): void
    {
        $this->admin();
        $customer = $this->user();
        $this->from('/admin/orders')->post('/admin/orders', $this->details([
            'user_id' => $customer->id, 'date' => '2026-11-07', 'time' => '09:00',
        ]) + ['_admin_modal' => 'order-create'])->assertRedirect('/admin/orders')
            ->assertSessionHasErrors('time', null, 'orderForm');
        $html = $this->get('/admin/orders')->assertOk()->getContent();
        $this->assertSame('true', $this->element($html, 'order-create-modal')->getAttribute('data-open-on-load'));
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_settings_modal_save_returns_to_the_current_admin_page(): void
    {
        $admin = $this->admin();
        $target = '/admin/orders?status=Ready&payment=pending';
        $this->from($target)->put('/admin/settings/profile', [
            'name' => 'Updated Admin', 'username' => $admin->username, '_return_to' => $target,
        ])->assertRedirect($target)->assertSessionHasNoErrors();
        $this->assertSame('Updated Admin', $admin->fresh()->name);
        $html = $this->get($target)->assertOk()->getContent();
        $this->assertSame('false', $this->element($html, 'admin-settings-modal')->getAttribute('data-open-on-load'));
        $this->assertSame('Updated Admin', $this->element($html, 'settings-name')->getAttribute('value'));
    }

    public function test_settings_errors_reopen_settings_without_filling_the_employee_form(): void
    {
        $admin = $this->admin();
        $this->from('/admin/employees')->put('/admin/settings/profile', [
            'name' => 'Draft Admin', 'username' => $admin->username, '_return_to' => '/admin/employees',
            'current_password' => 'wrong-password', 'password' => 'new-admin-pass', 'password_confirmation' => 'new-admin-pass',
        ])->assertRedirect('/admin/employees')->assertSessionHasErrors('current_password', null, 'settingsForm');
        $html = $this->get('/admin/employees')->assertOk()->getContent();
        $this->assertSame('true', $this->element($html, 'admin-settings-modal')->getAttribute('data-open-on-load'));
        $this->assertSame('Draft Admin', $this->element($html, 'settings-name')->getAttribute('value'));
        $this->assertSame('', $this->element($html, 'settings-current-password')->getAttribute('value'));
        $this->assertSame('', $this->element($html, 'settings-password')->getAttribute('value'));
        $this->assertSame('', $this->element($html, 'employee-name')->getAttribute('value'));
        $this->assertSame('false', $this->element($html, 'employee-create-modal')->getAttribute('data-open-on-load'));
        $this->assertSame('Mika Santos', $admin->fresh()->name);
    }

    public function test_settings_modal_return_address_stays_inside_the_admin_workspace(): void
    {
        $admin = $this->admin();
        foreach (['https://example.test', '//example.test', '/admin/../../outside', '/admin/%2E%2E/outside', '/admin/\\example.test'] as $target) {
            $this->put('/admin/settings/profile', [
                'name' => $admin->name, 'username' => $admin->username, '_return_to' => $target,
            ])->assertRedirect(route('admin.dashboard'))->assertSessionHasNoErrors();
        }
    }

    public function test_order_filter_errors_do_not_open_the_order_modal(): void
    {
        $this->admin();
        $this->from('/admin/orders')->get('/admin/orders?status=unknown')
            ->assertRedirect('/admin/orders')->assertSessionHasErrors('status');
        $html = $this->get('/admin/orders')->assertOk()->getContent();
        $this->assertSame('false', $this->element($html, 'order-create-modal')->getAttribute('data-open-on-load'));
    }

    public function test_admin_settings_save_profile_and_require_the_current_password_for_a_change(): void
    {
        $admin = $this->admin();
        $data = ['name' => 'Updated Admin', 'username' => $admin->username];
        $this->put('/admin/settings/profile', $data)->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('Updated Admin', $admin->fresh()->name);
        $this->put('/admin/settings/profile', $data + ['password' => 'new-admin-pass', 'password_confirmation' => 'new-admin-pass'])
            ->assertSessionHasErrors('current_password', null, 'settingsForm');
        $this->put('/admin/settings/profile', $data + ['current_password' => 'laundry-test', 'password' => 'new-admin-pass', 'password_confirmation' => 'new-admin-pass'])
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('new-admin-pass', $admin->fresh()->password));
    }

    public function test_attendance_uses_recorded_monthly_checkins_and_positive_logged_hours(): void
    {
        $this->admin();
        $employee = $this->employee();
        Attendance::create(['employee_id' => $employee->id, 'date' => '2026-11-07', 'shift' => 'Morning', 'check_in' => '2026-11-07 08:00:00', 'check_out' => '2026-11-07 16:00:00', 'status' => 'Present']);
        Attendance::create(['employee_id' => $employee->id, 'date' => '2026-10-07', 'shift' => 'Morning', 'check_in' => '2026-10-07 08:30:00', 'status' => 'Late']);
        $response = $this->get('/admin/reports/attendance?month=2026-11')->assertOk()->assertSee('8.0 h');
        $this->assertSame(1, $response->viewData('attendanceRecords')->first()->days_present);
        $this->assertSame(0, $response->viewData('attendanceRecords')->first()->late_arrivals);
        $this->get('/admin/reports/attendance?month=2026-10')->assertOk()->assertViewHas('selectedMonth');
        $this->get('/admin/reports/attendance/filter?month=10&year=2026')->assertRedirect(route('reports.attendance', ['month' => '2026-10']));
    }

    public function test_service_insights_include_extras_and_keep_empty_services_visible(): void
    {
        $this->admin();
        $this->order();
        $response = $this->get('/admin/reports/service-stats?period=all')->assertOk()->assertSee('155.00')->assertSee('Dry Cleaning');
        $this->assertSame(1, $response->viewData('totalOrders'));
        $this->assertEquals(2.5, $response->viewData('totalWeight'));
    }
}
