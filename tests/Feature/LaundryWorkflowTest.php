<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Order;
use App\Models\User;
use App\Services\LaundryOrderService;
use App\Support\LaundryWorkflow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class LaundryWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-07 08:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function customer(array $attributes = []): User
    {
        return User::create(array_merge([
            'name' => 'Aki Tanaka', 'username' => 'aki-'.Str::random(8),
            'phone' => '09123456789', 'address' => '12 Green Street',
            'password' => Hash::make('laundry-test'), 'role' => 'customer',
        ], $attributes));
    }

    private function employee(): Employee
    {
        return Employee::create([
            'name' => 'Mika Santos', 'email' => Str::random(8).'@example.test',
            'position' => 'Staff', 'contact' => '09123456780',
            'hire_date' => '2026-01-01', 'shift' => 'Morning',
            'status' => 'Active', 'password' => Hash::make('laundry-test'),
        ]);
    }

    private function details(array $overrides = []): array
    {
        return array_merge([
            'service' => 'Wash & Fold', 'laundry_style' => 'Regular Wash',
            'fabric_type' => 'Mixed', 'weight' => 2.5,
            'date' => '2026-11-08', 'time' => '09:00',
            'additional_services' => ['Fabric Softener'],
        ], $overrides);
    }

    private function order(User $customer, array $overrides = []): Order
    {
        return app(LaundryOrderService::class)->create($this->details($overrides), $customer->id, 'customer', $customer->id);
    }

    public function test_booking_requires_sign_in_and_login_returns_to_booking(): void
    {
        $this->get('/booking')->assertRedirect(route('login.form'));
        $user = $this->customer();
        $this->post('/login', ['username' => $user->username, 'password' => 'laundry-test'])
            ->assertRedirect(route('booking.create'));
    }

    public function test_customer_booking_uses_its_owner_and_calculates_the_full_total(): void
    {
        $owner = $this->customer();
        $other = $this->customer();
        $response = $this->actingAs($owner)->post('/booking', $this->details([
            'user_id' => $other->id, 'total_amount' => 1,
            'payment_status' => 'paid', 'payment_method' => 'Cash',
        ]));

        $order = Order::sole();
        $response->assertRedirect(route('order.tracking', $order->reference_no));
        $this->assertSame($owner->id, $order->user_id);
        $this->assertEquals(2.5, $order->weight);
        $this->assertSame('125.00', $order->total_price);
        $this->assertSame('30.00', $order->additional_charges);
        $this->assertEquals(155, $order->total_amount);
        $this->assertSame('pending', $order->payment_status);
        $this->assertNull($order->paid_at);
        $this->assertDatabaseHas('order_status_histories', ['order_id' => $order->id, 'status' => 'Pending', 'changed_by_type' => 'customer']);
        $this->get(route('order.tracking', $order->reference_no))->assertOk()->assertSee('155.00')->assertSee('Awaiting pickup');
    }

    public function test_invalid_service_dates_slots_and_duplicate_extras_do_not_create_orders(): void
    {
        $this->actingAs($this->customer())->post('/booking', $this->details([
            'service' => 'Unknown service', 'date' => '2026-11-06', 'time' => '03:00',
            'additional_services' => ['Fabric Softener', 'Fabric Softener'],
        ]))->assertSessionHasErrors(['service', 'date', 'time', 'additional_services.0']);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_booking_requires_a_pickup_address_and_phone(): void
    {
        $this->actingAs($this->customer(['address' => '', 'phone' => '']))
            ->post('/booking', $this->details())->assertSessionHasErrors('contact');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_booking_rejects_elapsed_pickup_times_and_redundant_extras(): void
    {
        $this->actingAs($this->customer());
        Carbon::setTestNow('2026-11-07 10:00:00');
        $this->post('/booking', $this->details(['date' => '2026-11-07', 'time' => '09:00']))->assertSessionHasErrors('time');
        $this->post('/booking', $this->details(['service' => 'Ironing', 'additional_services' => ['Ironing']]))->assertSessionHasErrors('additional_services');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_history_keeps_old_completed_orders_and_filters_by_owner(): void
    {
        $owner = $this->customer();
        $past = $this->order($owner);
        $past->update(['status' => 'Completed', 'updated_at' => now()->subDays(40)]);
        $foreign = $this->order($this->customer());
        $this->actingAs($owner)->get('/customer/orders/history?view=closed')
            ->assertOk()->assertSee($past->reference_no)->assertDontSee($foreign->reference_no);
        $this->get('/customer/orders/history')->assertOk()->assertDontSee($past->reference_no);
        $this->get('/customer/orders/history?view=all')->assertOk()->assertSee($past->reference_no);
    }

    public function test_customers_cannot_view_someone_elses_tracking_or_receipt(): void
    {
        $order = $this->order($this->customer());
        $this->actingAs($this->customer())->get(route('order.tracking', $order->reference_no))->assertNotFound();
        $this->get(route('receipt.show', $order->id))->assertNotFound();
    }

    public function test_receipt_shows_extras_in_the_total_and_the_payment_state(): void
    {
        $owner = $this->customer();
        $order = $this->order($owner);
        $this->actingAs($owner)->get(route('receipt.show', $order->id))
            ->assertOk()->assertSee('155.00')->assertSee('Fabric Softener')->assertSee('Payment due');
    }

    public function test_shared_staff_queue_includes_customer_and_admin_orders(): void
    {
        $staff = $this->employee();
        $order = $this->order($this->customer());
        $adminOrder = $this->order($this->customer());
        $adminOrder->update(['operator_type' => 'admin', 'operator_id' => 999]);
        $this->withSession(['staff_id' => $staff->id])->get('/staff/orders')
            ->assertOk()->assertSee($order->reference_no)->assertSee($adminOrder->reference_no)->assertSee('Confirm pickup');
    }

    public function test_staff_queue_search_and_status_filter_work_together(): void
    {
        $staff = $this->employee();
        $matching = $this->order($this->customer(['name' => 'Matching Customer']));
        $matching->update(['status' => 'Ready']);
        $other = $this->order($this->customer(['name' => 'Other Customer']));
        $this->withSession(['staff_id' => $staff->id])->get('/staff/orders?status=Ready&q=Matching')
            ->assertOk()->assertSee($matching->reference_no)->assertDontSee($other->reference_no);
    }

    public function test_staff_creation_uses_the_same_prices_as_customer_booking(): void
    {
        $staff = $this->employee();
        $customer = $this->customer();
        $this->withSession(['staff_id' => $staff->id])->post('/staff/orders', $this->details([
            'user_id' => $customer->id, 'payment_status' => 'pending',
        ]))->assertRedirect();
        $order = Order::sole();
        $this->assertEquals(155, $order->total_amount);
        $this->assertSame('staff', $order->operator_type);
        $this->get(route('staff.orders.show', $order->id))->assertOk()->assertSee('155.00')->assertSee('Confirm pickup');
    }

    public function test_status_advances_in_order_and_rejects_a_stale_second_click(): void
    {
        $staff = $this->employee();
        $order = $this->order($this->customer());
        $this->withSession(['staff_id' => $staff->id]);
        $this->post(route('staff.orders.status', $order->id), ['current_status' => 'Pending'])->assertRedirect();
        $this->assertSame('Picked Up', $order->fresh()->status);
        $this->postJson(route('staff.orders.status', $order->id), ['current_status' => 'Pending'])->assertUnprocessable();
        $this->assertSame('Picked Up', $order->fresh()->status);
        $this->assertDatabaseCount('order_status_histories', 2);
    }

    public function test_each_service_follows_its_own_cleaning_steps(): void
    {
        $staff = $this->employee();
        foreach (array_keys(LaundryWorkflow::SERVICES) as $service) {
            $order = $this->order($this->customer(), ['service' => $service]);
            foreach (array_slice(LaundryWorkflow::steps($service), 1) as $expected) {
                $order = app(LaundryOrderService::class)->advance($order, 'staff', $staff->id, $order->status);
                $this->assertSame($expected, $order->status);
            }
            $this->assertNotNull($order->completion_date);
            $this->assertSame('pending', $order->payment_status);
        }
    }

    public function test_terminal_orders_cannot_advance(): void
    {
        $staff = $this->employee();
        $this->withSession(['staff_id' => $staff->id]);
        foreach (['Completed', 'Refunded'] as $status) {
            $order = $this->order($this->customer());
            $order->update(['status' => $status]);
            $this->postJson(route('staff.orders.status', $order->id), ['current_status' => $status])->assertUnprocessable();
            $this->assertSame($status, $order->fresh()->status);
        }
    }

    public function test_payment_requires_a_reference_for_gcash_and_preserves_progress(): void
    {
        $staff = $this->employee();
        $order = $this->order($this->customer());
        $this->withSession(['staff_id' => $staff->id]);
        $this->post(route('staff.orders.pay', $order->id), ['payment_method' => 'GCash'])
            ->assertSessionHasErrors('payment_reference');
        $this->assertSame('pending', $order->fresh()->payment_status);
        $this->post(route('staff.orders.pay', $order->id), ['payment_method' => 'GCash', 'payment_reference' => 'GC-123'])
            ->assertRedirect();
        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertSame('Pending', $order->fresh()->status);
        $this->assertNotNull($order->fresh()->paid_at);
    }

    public function test_profile_changes_save_the_pickup_contact_details(): void
    {
        $customer = $this->customer();
        $this->actingAs($customer)->put('/settings', [
            'name' => $customer->name, 'username' => $customer->username,
            'phone' => '09998887777', 'address' => 'New pickup address',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('09998887777', $customer->fresh()->phone);
        $this->assertSame('New pickup address', $customer->fresh()->address);
    }

    public function test_main_customer_and_staff_pages_render(): void
    {
        foreach (['/', '/login', '/register', '/staff/login'] as $path) $this->get($path)->assertOk();
        $this->actingAs($this->customer());
        foreach (['/', '/booking', '/settings', '/customer/orders/history'] as $path) $this->get($path)->assertOk();
        $this->withSession(['staff_id' => $this->employee()->id]);
        foreach (['/staff/dashboard', '/staff/orders', '/staff/orders/create', '/staff/customers', '/staff/customers/create'] as $path) $this->get($path)->assertOk();
        $this->get('/staff/tasks')->assertRedirect(route('staff.orders.index'));
    }

    public function test_staff_can_add_a_customer_and_continue_to_their_order(): void
    {
        $this->withSession(['staff_id' => $this->employee()->id])->post('/staff/customers', [
            'name' => 'New Customer', 'username' => 'new-customer',
            'phone' => '09111222333', 'address' => '23 Fresh Street',
            'password' => 'customer-test', 'password_confirmation' => 'customer-test',
        ])->assertSessionHasNoErrors()->assertRedirect();
        $customer = User::where('username', 'new-customer')->sole();
        $this->get(route('staff.orders.create', ['customer' => $customer->id]))->assertOk()->assertSee($customer->name);
        $this->get(route('staff.customers.show', $customer->id))->assertOk();
        $this->get(route('staff.customers.edit', $customer->id))->assertOk();
        $this->put(route('staff.customers.update', $customer->id), [
            'name' => $customer->name, 'username' => $customer->username,
            'phone' => '09990001111', 'address' => 'Updated address',
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('Updated address', $customer->fresh()->address);
    }
}
