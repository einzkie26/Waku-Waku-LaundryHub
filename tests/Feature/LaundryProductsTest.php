<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Order;
use App\Models\User;
use App\Services\AdminReportService;
use App\Services\LaundryOrderService;
use App\Support\LaundryProducts;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LaundryProductsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-10 08:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function customer(): User
    {
        return User::create([
            'name' => 'Aki Tanaka', 'username' => 'aki-customer', 'password' => 'customer-pass',
            'phone' => '09123456789', 'address' => '12 Green Street', 'role' => 'customer',
        ]);
    }

    private function details(array $overrides = []): array
    {
        return array_replace([
            'service' => 'Wash & Fold', 'weight' => 2.5, 'laundry_style' => 'Regular Wash', 'fabric_type' => 'Mixed',
            'date' => '2026-11-11', 'time' => '09:00', 'detergent_brand' => 'ariel', 'detergent_sachets' => 2,
            'softener_brand' => 'downy', 'softener_cups' => 10,
            'additional_services' => ['Scent Boost', 'Hang Dry', 'With Packaging'],
        ], $overrides);
    }

    public function test_branded_booking_controls_show_the_confirmed_units_and_only_the_new_extras(): void
    {
        $this->actingAs($this->customer())->get('/booking')->assertOk()
            ->assertSee('ariel.webp')->assertSee('breeze.png')->assertSee('surf.png')->assertSee('downy.webp')
            ->assertSee('1 cup = 1.5 mL')->assertSee('₱24 / sachet')->assertSee('₱27 / liter')
            ->assertSee('value="With Packaging"', false)->assertSee('value="Scent Boost"', false)->assertSee('value="Hang Dry"', false)
            ->assertDontSee('value="Stain Removal"', false)->assertDontSee('value="Fabric Softener"', false)
            ->assertDontSee('Choose your laundry service, schedule a pickup, and check your total.');
    }

    public function test_customer_booking_saves_sachet_and_cup_prices_with_product_snapshots(): void
    {
        $this->actingAs($this->customer())->post('/booking', $this->details())->assertRedirect()->assertSessionHasNoErrors();
        $order = Order::sole();
        $this->assertSame('125.00', $order->total_price);
        $this->assertSame('48.41', $order->product_charges);
        $this->assertSame('80.00', $order->additional_charges);
        $this->assertEqualsWithDelta(253.41, $order->total_amount, .00001);
        $this->assertSame('253.41', number_format($order->getRawOriginal('total_amount'), 2, '.', ''));
        $this->assertSame('Ariel', $order->laundry_products[0]['name']);
        $this->assertSame(2, $order->laundry_products[0]['quantity']);
        $this->assertSame(2400, $order->laundry_products[0]['rate_cents']);
        $this->assertSame('Downy', $order->laundry_products[1]['name']);
        $this->assertEquals(15, $order->laundry_products[1]['volume_ml']);
        $this->assertEquals(1.5, $order->laundry_products[1]['cup_ml']);
        $this->assertSame(41, $order->laundry_products[1]['amount_cents']);
        $this->assertSame('pending', $order->payment_status);
    }

    public function test_softener_rounding_uses_the_full_volume_before_rounding_to_centavos(): void
    {
        foreach ([['cups' => .5, 'cents' => 2, 'ml' => .75], ['cups' => 1, 'cents' => 4, 'ml' => 1.5], ['cups' => 10, 'cents' => 41, 'ml' => 15], ['cups' => 1000, 'cents' => 4050, 'ml' => 1500]] as $case) {
            $quote = LaundryProducts::quote(['service' => 'Wash & Fold', 'softener_brand' => 'downy', 'softener_cups' => $case['cups']]);
            $this->assertSame($case['cents'], $quote['amount_cents']);
            $this->assertEquals($case['ml'], $quote['items'][0]['volume_ml']);
        }
    }

    public function test_client_prices_and_product_snapshots_cannot_change_the_charge(): void
    {
        $this->actingAs($this->customer())->post('/booking', $this->details([
            'total_price' => .01, 'total_amount' => .01, 'product_charges' => .01, 'additional_charges' => 0,
            'laundry_products' => [['name' => 'Fake', 'amount_cents' => 1]],
            'detergent_rate' => 0, 'softener_rate' => 0,
        ]))->assertRedirect()->assertSessionHasNoErrors();
        $order = Order::sole();
        $this->assertEqualsWithDelta(253.41, $order->total_amount, .00001);
        $this->assertSame('Ariel', $order->laundry_products[0]['name']);
    }

    public function test_invalid_brands_quantities_and_removed_extras_do_not_create_orders(): void
    {
        $this->actingAs($this->customer());
        foreach ([
            ['detergent_brand' => 'unknown'], ['detergent_sachets' => 0], ['detergent_sachets' => 1.5], ['detergent_sachets' => 51],
            ['softener_brand' => 'unknown'], ['softener_cups' => 0], ['softener_cups' => .25], ['softener_cups' => 1000.5],
            ['detergent_brand' => 'own', 'detergent_sachets' => 2], ['softener_brand' => 'none', 'softener_cups' => 1],
            ['additional_services' => ['Stain Removal']], ['additional_services' => ['Fabric Softener']],
            ['additional_services' => ['Scent Boost', 'Scent Boost']],
        ] as $invalid) {
            $this->postJson('/booking', $this->details($invalid))->assertUnprocessable();
        }
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_invalid_product_array_input_can_render_the_form_again(): void
    {
        $this->actingAs($this->customer())->from('/booking')->post('/booking', $this->details([
            'detergent_brand' => ['ariel'], 'softener_brand' => ['downy'],
        ]))->assertRedirect('/booking')->assertSessionHasErrors(['detergent_brand', 'softener_brand']);
        $this->get('/booking')->assertOk();
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_ironing_and_dry_cleaning_cannot_charge_for_liquid_products(): void
    {
        $this->actingAs($this->customer());
        foreach (['Ironing', 'Dry Cleaning'] as $service) {
            $this->postJson('/booking', $this->details(['service' => $service]))->assertUnprocessable()->assertJsonValidationErrors('products');
            $this->post('/booking', $this->details([
                'service' => $service, 'detergent_brand' => 'own', 'detergent_sachets' => 0,
                'softener_brand' => 'none', 'softener_cups' => 0, 'additional_services' => [],
            ]))->assertRedirect()->assertSessionHasNoErrors();
        }
        $this->assertDatabaseCount('orders', 2);
        $this->assertEquals(0, Order::sum('product_charges'));
    }

    public function test_product_charges_appear_on_receipts_and_in_revenue(): void
    {
        $customer = $this->customer();
        $order = app(LaundryOrderService::class)->create($this->details(), $customer->id, 'customer', $customer->id);
        $order->update(['payment_status' => 'paid', 'payment_method' => 'Cash', 'paid_at' => now()]);
        $this->actingAs($customer)->get(route('receipt.show', $order->id))->assertOk()
            ->assertSee('Ariel detergent')->assertSee('Downy fabric softener')->assertSee('15 mL')->assertSee('253.41')->assertSee('0.41');
        $report = app(AdminReportService::class)->revenue('week');
        $this->assertEqualsWithDelta(253.41, $report['totalRevenue'], .00001);
        $this->assertEqualsWithDelta(253.41, $report['serviceRevenue']->first()->revenue, .00001);
    }

    public function test_older_orders_keep_their_original_extras_and_total(): void
    {
        $customer = $this->customer();
        $order = app(LaundryOrderService::class)->create($this->details([
            'detergent_brand' => 'own', 'detergent_sachets' => 0, 'softener_brand' => 'none', 'softener_cups' => 0,
            'additional_services' => [],
        ]), $customer->id, 'customer', $customer->id);
        $order->update(['laundry_products' => null, 'product_charges' => 0, 'additional_services' => ['Fabric Softener'], 'additional_charges' => 30, 'total_amount' => 155]);
        $this->assertEquals(155, $order->fresh()->total_amount);
        $this->actingAs($customer)->get(route('receipt.show', $order->id))->assertOk()->assertSee('155.00')->assertSee('Fabric Softener');
    }

    public function test_employee_shared_login_can_create_orders_with_the_same_product_prices(): void
    {
        $customer = $this->customer();
        $employee = Employee::create([
            'name' => 'Mika Staff', 'email' => 'staff@example.test', 'password' => Hash::make('staff-pass'), 'position' => 'Staff',
            'contact' => '09123456789', 'hire_date' => '2026-01-01', 'shift' => 'Morning', 'status' => 'Active',
        ]);
        $this->post('/login', ['username' => $employee->email, 'password' => 'staff-pass'])->assertRedirect(route('staff.dashboard'));
        $this->post('/staff/orders', $this->details(['user_id' => $customer->id, 'payment_status' => 'pending']))
            ->assertRedirect()->assertSessionHasNoErrors();
        $order = Order::sole();
        $this->assertSame('staff', $order->operator_type);
        $this->assertEquals($employee->id, $order->operator_id);
        $this->assertEqualsWithDelta(253.41, $order->total_amount, .00001);
        $this->get(route('staff.orders.show', $order->id))->assertOk()->assertSee('253.41');
        $this->get(route('staff.reports'))->assertOk()->assertViewHas('todayRevenue', fn ($amount) => abs((float) $amount - 253.41) < .00001);
    }

    public function test_admin_product_errors_reopen_the_order_modal(): void
    {
        $customer = $this->customer();
        $admin = User::create(['name' => 'Admin', 'username' => 'admin', 'password' => 'admin-pass', 'role' => 'admin', 'phone' => '', 'address' => '']);
        $this->actingAs($admin)->from('/admin/orders')->post('/admin/orders', $this->details([
            'user_id' => $customer->id, 'payment_status' => 'pending', 'detergent_sachets' => 0, '_admin_modal' => 'order-create',
        ]))->assertRedirect('/admin/orders')->assertSessionHasErrors('detergent_sachets', null, 'orderForm');
        $this->get('/admin/orders')->assertOk()->assertSee('data-open-on-load="true"', false)->assertSee('Choose between 1 and 50 sachets.');
        $this->assertDatabaseCount('orders', 0);
    }
}
