<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UnifiedLoginTest extends TestCase
{
    use RefreshDatabase;

    private function employee(array $attributes = []): Employee
    {
        return Employee::create($attributes + [
            'name' => 'Mika Staff', 'email' => 'mika@example.test', 'password' => Hash::make('staff-pass'),
            'position' => 'Staff', 'contact' => '09123456789', 'hire_date' => '2026-01-01',
            'shift' => 'Morning', 'status' => 'Active', 'is_active' => true,
        ]);
    }

    private function user(string $role = 'customer', array $attributes = []): User
    {
        return User::create($attributes + [
            'name' => 'Aki User', 'username' => 'aki-'.$role, 'password' => 'customer-pass',
            'phone' => '09123456789', 'address' => '12 Green Street', 'role' => $role,
        ]);
    }

    public function test_employee_uses_the_shared_login_and_reaches_the_staff_workspace(): void
    {
        $employee = $this->employee();
        $this->get('/staff/login')->assertRedirect(route('login.form'));
        $this->get('/check-employees')->assertNotFound();
        $this->get('/login')->assertOk()->assertSee('Username or staff email')->assertDontSee('Staff sign in');
        $this->post('/login', ['username' => $employee->email, 'password' => 'staff-pass'])
            ->assertRedirect(route('staff.dashboard'))->assertSessionHas('staff_id', $employee->id);
        $this->assertAuthenticatedAs($employee, 'staff');
        $this->assertGuest('web');
        $this->get('/staff/dashboard')->assertOk()->assertSee('Mika Staff');
        $this->get('/login')->assertRedirect(route('staff.dashboard'));
    }

    public function test_customer_and_admin_keep_their_own_shared_login_destinations(): void
    {
        foreach (['customer' => 'landing', 'admin' => 'admin.dashboard'] as $role => $route) {
            $user = $this->user($role);
            $this->post('/login', ['username' => $user->username, 'password' => 'customer-pass'])
                ->assertRedirect(route($route))->assertSessionMissing('staff_id');
            $this->assertAuthenticatedAs($user, 'web');
            $this->assertGuest('staff');
            $this->post('/logout')->assertRedirect(route('login.form'));
        }
    }

    public function test_failed_legacy_staff_sign_in_shows_errors_on_the_shared_form(): void
    {
        $employee = $this->employee();
        $this->from('/staff/login')->post('/staff/login', ['email' => $employee->email, 'password' => 'wrong-password'])
            ->assertRedirect(route('login.form'))->assertSessionHasErrors('username');
        $this->get('/login')->assertOk()->assertSee('The provided credentials do not match our records.')
            ->assertSee('value="mika@example.test"', false)->assertDontSee('value="wrong-password"', false);
    }

    public function test_invalid_or_inactive_employee_credentials_never_create_a_staff_session(): void
    {
        $employee = $this->employee(['status' => 'Inactive']);
        $this->from('/login')->post('/login', ['username' => $employee->email, 'password' => 'staff-pass'])
            ->assertRedirect('/login')->assertSessionHasErrors('username')->assertSessionMissing('staff_id');
        $this->assertGuest('staff');
        $employee->update(['status' => 'Active', 'is_active' => false]);
        $this->from('/login')->post('/login', ['username' => $employee->email, 'password' => 'staff-pass'])
            ->assertSessionHasErrors('username')->assertSessionMissing('staff_id');
        $employee->update(['is_active' => true]);
        $this->from('/login')->post('/login', ['username' => $employee->email, 'password' => 'wrong-password'])
            ->assertSessionHasErrors('username')->assertSessionMissing('staff_id');
        $this->assertGuest('staff');
        $this->assertNotContains('wrong-password', session()->getOldInput());
    }

    public function test_employee_remember_me_and_both_logout_addresses_use_the_same_identity(): void
    {
        $employee = $this->employee();
        $response = $this->post('/login', ['username' => $employee->email, 'password' => 'staff-pass', 'remember' => 1]);
        $response->assertCookie(Auth::guard('staff')->getRecallerName());
        $this->assertNotEmpty($employee->fresh()->remember_token);
        $this->withSession(['staff_id' => null])->get('/staff/dashboard')->assertOk()->assertSessionHas('staff_id', $employee->id);
        $this->post('/logout')->assertRedirect(route('login.form'))->assertSessionMissing('staff_id');
        $this->assertGuest('web');
        $this->assertGuest('staff');
        $this->post('/staff/login', ['email' => $employee->email, 'password' => 'staff-pass'])->assertRedirect(route('staff.dashboard'));
        $this->post('/staff/logout')->assertRedirect(route('login.form'))->assertSessionMissing('staff_id');
        $this->assertGuest('staff');
    }

    public function test_deactivation_revokes_an_existing_employee_session(): void
    {
        $employee = $this->employee();
        $this->post('/login', ['username' => $employee->email, 'password' => 'staff-pass']);
        $employee->update(['is_active' => false]);
        $this->get('/staff/dashboard')->assertRedirect(route('login.form'))->assertSessionMissing('staff_id');
        $this->assertGuest('staff');
    }

    public function test_staff_cannot_enter_customer_or_admin_routes_as_a_web_user(): void
    {
        $employee = $this->employee();
        $this->post('/login', ['username' => $employee->email, 'password' => 'staff-pass']);
        $this->get('/admin/dashboard')->assertRedirect(route('login.form'));
        $this->get('/booking')->assertRedirect(route('login.form'));
        $this->assertGuest('web');
    }

    public function test_customer_cannot_reuse_a_staff_session_id(): void
    {
        $employee = $this->employee();
        $this->actingAs($this->user())->withSession(['staff_id' => $employee->id]);
        $this->get('/staff/dashboard')->assertRedirect(route('login.form'))->assertSessionMissing('staff_id');
        $this->assertGuest('staff');
    }

    public function test_ambiguous_credentials_do_not_choose_the_wrong_account(): void
    {
        $employee = $this->employee();
        $this->user('customer', ['username' => $employee->email, 'password' => 'staff-pass']);
        $this->from('/login')->post('/login', ['username' => $employee->email, 'password' => 'staff-pass'])
            ->assertSessionHasErrors('username')->assertSessionMissing('staff_id');
        $this->assertGuest('web');
        $this->assertGuest('staff');
    }

    public function test_receipts_do_not_allow_mixed_or_inactive_staff_sessions(): void
    {
        $employee = $this->employee();
        $owner = $this->user('customer', ['username' => 'owner']);
        $viewer = $this->user('customer', ['username' => 'viewer']);
        $order = app(\App\Services\LaundryOrderService::class)->create([
            'service' => 'Wash & Fold', 'weight' => 1, 'laundry_style' => 'Regular Wash', 'fabric_type' => 'Mixed',
            'date' => today()->addDay()->toDateString(), 'time' => '09:00',
        ], $owner->id, 'customer', $owner->id);
        $this->actingAs($viewer)->withSession(['staff_id' => $employee->id])->get(route('receipt.show', $order->id))->assertNotFound();
        $this->post('/logout');
        $this->post('/login', ['username' => $employee->email, 'password' => 'staff-pass']);
        $employee->update(['is_active' => false]);
        $this->get(route('receipt.show', $order->id))->assertForbidden();
    }
}
