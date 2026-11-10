<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Notification;
use App\Models\Order;
use App\Models\User;
use App\Services\LaundryOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserNotificationsTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role = 'customer'): User
    {
        return User::create([
            'name' => ucfirst($role).' User', 'username' => Str::random(12), 'password' => 'laundry-test',
            'phone' => '09123456789', 'address' => '12 Green Street', 'role' => $role,
        ]);
    }

    private function staff(): Employee
    {
        return Employee::create([
            'name' => 'Mika Staff', 'email' => Str::random(12).'@example.test', 'password' => Hash::make('staff-pass'),
            'position' => 'Staff', 'contact' => '09123456789', 'hire_date' => '2026-01-01',
            'shift' => 'Morning', 'status' => 'Active', 'is_active' => true,
        ]);
    }

    private function order(User $owner, string $actorType = 'customer', ?int $actorId = null): Order
    {
        return app(LaundryOrderService::class)->create([
            'service' => 'Wash & Fold', 'weight' => 2, 'laundry_style' => 'Regular Wash', 'fabric_type' => 'Mixed',
            'date' => today()->addDay()->toDateString(), 'time' => '09:00',
        ], $owner->id, $actorType, $actorId ?? $owner->id);
    }

    private function notification(User $owner, string $title = 'Laundry update'): Notification
    {
        return $owner->laundryNotifications()->create([
            'title' => $title, 'message' => 'A real update.', 'type' => 'order',
            'action_url' => $owner->role === 'admin' ? '/admin/orders/1' : '/order-tracking/REF-TEST',
        ]);
    }

    public function test_admin_and_customer_layouts_have_the_shared_inbox_and_guests_do_not(): void
    {
        $this->get('/')->assertOk()->assertDontSee('data-notification-center', false);
        $customer = $this->user();
        $this->actingAs($customer, 'web');
        foreach (['/', '/booking', '/customer/orders/history', '/settings'] as $path) {
            $this->get($path)->assertOk()->assertSee('id="customer-notifications"', false)
                ->assertSee('data-open-notifications', false)->assertSee('laundry-notifications.js')
                ->assertSee('data-recipient-key="customer:'.$customer->id.'"', false)
                ->assertDontSee('STAFF WORKSPACE');
        }
        $this->actingAs($this->user('admin'), 'web');
        foreach (['/admin/dashboard', '/admin/orders', '/admin/customers', '/admin/employees'] as $path) {
            $this->get($path)->assertOk()->assertSee('id="admin-notifications"', false)->assertSee('ADMIN WORKSPACE')
                ->assertSee('data-open-notifications', false)->assertSee('laundry-notifications.css');
        }
        $this->get('/')->assertOk()->assertSee('id="admin-notifications"', false)->assertDontSee('id="customer-notifications"', false);
    }

    public function test_new_booking_notifies_admins_staff_and_only_its_customer_with_the_correct_order_links(): void
    {
        $owner = $this->user();
        $otherCustomer = $this->user();
        $admin = $this->user('admin');
        $staff = $this->staff();
        $this->actingAs($owner, 'web');
        $order = $this->order($owner);
        $this->assertSame('Booking received', $owner->laundryNotifications()->sole()->title);
        $this->assertSame(route('order.tracking', $order->reference_no, false), $owner->laundryNotifications()->sole()->action_url);
        $this->assertSame(0, $otherCustomer->laundryNotifications()->count());
        $this->assertSame('New order in the queue', $admin->laundryNotifications()->sole()->title);
        $this->assertSame(route('orders.show', $order->id, false), $admin->laundryNotifications()->sole()->action_url);
        $this->assertSame(route('staff.orders.show', $order->id, false), $staff->notifications()->sole()->action_url);
    }

    public function test_admins_do_not_get_popups_for_their_own_actions_and_customers_receive_progress_and_payment_updates(): void
    {
        $owner = $this->user();
        $admin = $this->user('admin');
        $otherAdmin = $this->user('admin');
        $this->actingAs($owner, 'web');
        $order = $this->order($owner);
        $this->actingAs($admin, 'web');
        $this->patchJson(route('orders.updateStatus', $order), ['current_status' => 'Pending'])->assertOk();
        $this->assertSame(1, $admin->laundryNotifications()->count());
        $this->assertSame(2, $otherAdmin->laundryNotifications()->count());
        $this->assertSame('Your laundry was picked up', $owner->laundryNotifications()->latest('id')->first()->title);
        $this->post(route('orders.pay', $order), ['payment_method' => 'Cash'])->assertRedirect();
        $this->assertSame('Payment received', $owner->laundryNotifications()->latest('id')->first()->title);
        $this->assertSame('Payment recorded', $otherAdmin->laundryNotifications()->latest('id')->first()->title);
        $before = Notification::count();
        $this->post(route('orders.pay', $order), ['payment_method' => 'Cash'])->assertRedirect();
        $this->assertSame($before, Notification::count());
    }

    public function test_admin_created_orders_notify_other_admins_and_the_customer(): void
    {
        $actor = $this->user('admin');
        $otherAdmin = $this->user('admin');
        $owner = $this->user();
        $this->actingAs($actor, 'web');
        $this->order($owner, 'admin', $actor->id);
        $this->assertSame(0, $actor->laundryNotifications()->count());
        $this->assertSame(1, $otherAdmin->laundryNotifications()->count());
        $this->assertSame('Booking received', $owner->laundryNotifications()->sole()->title);
    }

    public function test_staff_progress_updates_reach_admins_and_the_order_owner(): void
    {
        $owner = $this->user();
        $admin = $this->user('admin');
        $staff = $this->staff();
        $order = $this->order($owner);
        Auth::guard('web')->logout();
        $this->actingAs($staff, 'staff')->postJson(route('staff.orders.status', $order), ['current_status' => 'Pending'])->assertOk();
        $this->assertSame('Laundry status updated', $admin->laundryNotifications()->latest('id')->first()->title);
        $this->assertSame('Your laundry was picked up', $owner->laundryNotifications()->latest('id')->first()->title);
        $this->assertSame(1, $staff->notifications()->count());
    }

    public function test_rescheduling_and_refunds_notify_the_customer_without_using_admin_activity_logs(): void
    {
        $owner = $this->user();
        $admin = $this->user('admin');
        $order = $this->order($owner);
        $this->actingAs($admin, 'web')->post(route('orders.reschedule', $order), [
            'new_delivery_date' => today()->addDays(2)->toDateString(), 'new_pickup_time' => '14:00',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $notice = $owner->laundryNotifications()->latest('id')->first();
        $this->assertSame('Pickup rescheduled', $notice->title);
        $this->assertStringContainsString('2:00 PM', $notice->message);
        $this->post(route('orders.pay', $order), ['payment_method' => 'Cash']);
        $this->post(route('orders.refund', $order), ['refund_amount' => $order->total_amount, 'refund_reason' => 'Approved refund'])
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('Refund recorded', $owner->laundryNotifications()->latest('id')->first()->title);
    }

    public function test_every_laundry_stage_has_a_customer_alert_and_reading_one_never_affects_other_users(): void
    {
        $owner = $this->user();
        $other = $this->user();
        $admin = $this->user('admin');
        $order = $this->order($owner);
        $this->actingAs($admin, 'web');
        foreach (['Pending', 'Picked Up', 'Washing', 'Drying', 'Ready'] as $status) {
            $this->patchJson(route('orders.updateStatus', $order), ['current_status' => $status])->assertOk();
        }
        $this->assertSame('Your laundry has been delivered', $owner->laundryNotifications()->latest('id')->first()->title);
        $this->assertSame(6, $owner->laundryNotifications()->count());
        $this->assertSame(0, $other->laundryNotifications()->count());
        $this->actingAs($owner, 'web')->getJson(route('customer.notifications.index'))->assertOk()->assertJsonPath('unread_count', 6);
    }

    public function test_inbox_read_and_read_all_are_scoped_to_the_signed_in_user_for_both_web_roles(): void
    {
        foreach (['admin', 'customer'] as $role) {
            $one = $this->user($role);
            $two = $this->user($role);
            $own = $this->notification($one);
            $foreign = $this->notification($two, 'Private other account');
            $staffNotice = Notification::create(['notifiable_type' => Employee::class, 'notifiable_id' => $one->id, 'title' => 'Staff only', 'message' => 'Private']);
            $base = $role === 'admin' ? '/admin/notifications' : '/customer/notifications';
            $this->actingAs($one, 'web')->getJson($base)->assertOk()->assertJsonCount(1, 'notifications')
                ->assertJsonPath('recipient_key', $role.':'.$one->id)->assertJsonPath('unread_count', 1)
                ->assertJsonCount(0, 'events')->assertDontSee('Private other account')->assertDontSee('Staff only');
            $this->postJson($base.'/'.$foreign->id.'/read')->assertNotFound();
            $this->postJson($base.'/'.$staffNotice->id.'/read')->assertNotFound();
            $this->postJson($base.'/'.$own->id.'/read')->assertOk();
            $this->assertNotNull($own->fresh()->read_at);
            $second = $this->notification($one);
            $this->postJson($base.'/read-all')->assertOk();
            $this->assertNotNull($second->fresh()->read_at);
            $this->assertNull($foreign->fresh()->read_at);
            $this->assertNull($staffNotice->fresh()->read_at);
        }
    }

    public function test_guests_staff_and_the_wrong_web_role_cannot_use_another_roles_inbox(): void
    {
        foreach (['/admin/notifications', '/customer/notifications'] as $path) {
            $this->getJson($path)->assertUnauthorized();
        }
        $this->actingAs($this->user(), 'web')->getJson('/admin/notifications')->assertForbidden();
        $this->actingAs($this->user('admin'), 'web')->getJson('/customer/notifications')->assertForbidden();
        Auth::guard('web')->logout();
        $this->actingAs($this->staff(), 'staff')->getJson('/customer/notifications')->assertUnauthorized();
    }

    public function test_stale_account_tabs_cannot_fetch_or_mark_the_new_accounts_notifications(): void
    {
        $old = $this->user();
        $current = $this->user();
        $notice = $this->notification($current);
        $this->actingAs($current, 'web')->withHeader('X-Notification-Recipient', 'customer:'.$old->id);
        $this->getJson('/customer/notifications')->assertUnauthorized();
        $this->postJson('/customer/notifications/read-all')->assertUnauthorized();
        $this->postJson('/customer/notifications/'.$notice->id.'/read')->assertUnauthorized();
        $this->assertNull($notice->fresh()->read_at);
    }

    public function test_incremental_web_feeds_report_only_new_unread_updates(): void
    {
        foreach (['admin', 'customer'] as $role) {
            $user = $this->user($role);
            $first = $this->notification($user);
            $new = $this->notification($user);
            $read = $this->notification($user);
            $read->markAsRead();
            $base = $role === 'admin' ? '/admin/notifications' : '/customer/notifications';
            $this->actingAs($user, 'web')->getJson($base.'?after='.$first->id)->assertOk()
                ->assertJsonCount(1, 'events')->assertJsonPath('events.0.id', $new->id)->assertJsonPath('latest_id', $read->id)
                ->assertJsonPath('unread_count', 2)->assertHeader('Cache-Control', 'no-store, private');
        }
    }
}
