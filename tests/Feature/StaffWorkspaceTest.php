<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Notification;
use App\Models\Order;
use App\Models\User;
use App\Services\LaundryOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class StaffWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    private function employee(array $data = []): Employee
    {
        return Employee::create($data + [
            'name' => 'Mika Staff', 'email' => Str::random(10).'@example.test', 'password' => Hash::make('staff-pass'),
            'position' => 'Laundry attendant', 'contact' => '09123456789', 'hire_date' => '2026-01-01',
            'shift' => 'Morning', 'status' => 'Active', 'is_active' => true,
        ]);
    }

    private function details(Employee $employee, array $data = []): array
    {
        return $data + ['name' => $employee->name, 'email' => $employee->email, 'contact' => $employee->contact];
    }

    private function photo(string $name = 'portrait.png', int $width = 100, int $height = 100): UploadedFile
    {
        // A real PNG fixture without requiring GD on the development machine.
        $chunk = fn ($type, $data) => pack('N', strlen($data)).$type.$data.pack('N', crc32($type.$data));
        $pixels = str_repeat("\0".str_repeat("\x60\x9c\x82", $width), $height);
        $png = "\x89PNG\r\n\x1a\n".$chunk('IHDR', pack('NNCCCCC', $width, $height, 8, 2, 0, 0, 0))
            .$chunk('IDAT', gzcompress($pixels)).$chunk('IEND', '');

        return UploadedFile::fake()->createWithContent($name, $png);
    }

    private function notification(Employee $employee, array $data = []): Notification
    {
        return $employee->notifications()->create($data + [
            'title' => 'Queue update', 'message' => 'REF-TEST is ready.', 'type' => 'order',
            'action_url' => '/staff/orders/1',
        ]);
    }

    private function order(string $operatorType = 'customer', ?int $actor = null): Order
    {
        $customer = User::create([
            'name' => 'Aki Customer', 'username' => Str::random(12), 'password' => 'customer-pass',
            'phone' => '09123456789', 'address' => '12 Green Street', 'role' => 'customer',
        ]);

        return app(LaundryOrderService::class)->create([
            'service' => 'Wash & Fold', 'weight' => 2, 'laundry_style' => 'Regular Wash', 'fabric_type' => 'Mixed',
            'date' => today()->addDay()->toDateString(), 'time' => '09:00',
        ], $customer->id, $operatorType, $actor ?? $customer->id);
    }

    public function test_staff_layout_has_both_navigation_modes_and_the_real_notification_inbox(): void
    {
        $staff = $this->employee();
        foreach (['/staff/dashboard', '/staff/orders', '/staff/profile', '/staff/attendance', '/staff/history', '/staff/customers'] as $path) {
            $this->actingAs($staff, 'staff')->get($path)->assertOk()
                ->assertSee('data-staff-mode="sidebar"', false)->assertSee('data-staff-mode="radial"', false)
                ->assertSee('id="staff-notifications"', false)->assertSee('staff-navigation.js')
                ->assertDontSee('THE DAILY LOAD')->assertDontSee('One queue. Every load.');
        }
        $this->get('/staff/profile')->assertSee('multipart/form-data')->assertSee('Laundry attendant')->assertSee('09123456789');
    }

    public function test_profile_saves_contact_and_photo_and_serves_only_the_signed_in_employees_photo(): void
    {
        Storage::fake('local');
        $staff = $this->employee();
        $other = $this->employee();
        $this->actingAs($staff, 'staff')->post(route('staff.profile.update'), $this->details($staff, [
            'name' => 'Mika Updated', 'contact' => '09998887777', 'photo' => $this->photo('portrait.png', 180, 180),
            'position' => 'Admin', 'status' => 'Inactive', 'is_active' => false, 'profile_photo_path' => 'other.png',
        ]))->assertRedirect(route('staff.profile'))->assertSessionHasNoErrors();
        $staff->refresh();
        $this->assertSame('09998887777', $staff->contact);
        $this->assertSame('Mika Updated', $staff->name);
        $this->assertSame('Laundry attendant', $staff->position);
        $this->assertTrue($staff->is_active);
        Storage::disk('local')->assertExists($staff->profile_photo_path);
        $this->assertStringStartsWith('staff-photos/', $staff->profile_photo_path);
        $this->get(route('staff.profile.photo'))->assertOk()->assertHeader('Content-Type', 'image/png')->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->get('/staff/dashboard')->assertSee($staff->profile_photo_url, false);
        $this->actingAs($other, 'staff')->get(route('staff.profile.photo'))->assertNotFound();
    }

    public function test_replacing_and_removing_a_photo_cleans_up_the_previous_file(): void
    {
        Storage::fake('local');
        $staff = $this->employee();
        $this->actingAs($staff, 'staff');
        foreach (['first.png', 'second.png'] as $name) {
            $previous = $staff->profile_photo_path;
            $this->post(route('staff.profile.update'), $this->details($staff, ['photo' => $this->photo($name)]))
                ->assertSessionHasNoErrors();
            $staff->refresh();
            Storage::disk('local')->assertExists($staff->profile_photo_path);
            if ($previous) {
                Storage::disk('local')->assertMissing($previous);
            }
        }
        $last = $staff->profile_photo_path;
        $this->post(route('staff.profile.update'), $this->details($staff, ['remove_photo' => 1]))->assertSessionHasNoErrors();
        $this->assertNull($staff->fresh()->profile_photo_path);
        Storage::disk('local')->assertMissing($last);
        $this->get(route('staff.profile.photo'))->assertNotFound();
    }

    public function test_invalid_photos_and_duplicate_email_do_not_change_the_profile(): void
    {
        Storage::fake('local');
        $staff = $this->employee();
        $other = $this->employee();
        $this->actingAs($staff, 'staff');
        foreach ([
            UploadedFile::fake()->createWithContent('avatar.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'),
            $this->photo('small.png', 20, 20),
            $this->photo('large.png')->size(3073),
        ] as $photo) {
            $this->postJson(route('staff.profile.update'), $this->details($staff, ['photo' => $photo, 'name' => 'Changed']))
                ->assertUnprocessable()->assertJsonValidationErrors('photo');
        }
        $this->postJson(route('staff.profile.update'), $this->details($staff, ['email' => $other->email]))
            ->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->assertSame('Mika Staff', $staff->fresh()->name);
        $this->assertNull($staff->fresh()->profile_photo_path);
        $this->assertEmpty(Storage::disk('local')->allFiles('staff-photos'));
    }

    public function test_password_change_requires_the_current_password_and_never_flashes_passwords(): void
    {
        Storage::fake('local');
        $staff = $this->employee();
        $this->actingAs($staff, 'staff');
        $change = $this->details($staff, ['new_password' => 'new-staff-pass', 'new_password_confirmation' => 'new-staff-pass']);
        $this->postJson(route('staff.profile.update'), $change)->assertUnprocessable()->assertJsonValidationErrors('current_password');
        $this->from('/staff/profile')->post(route('staff.profile.update'), $change + ['current_password' => 'wrong-password', 'photo' => $this->photo()])
            ->assertRedirect('/staff/profile')->assertSessionHasErrors('current_password');
        $this->assertNotContains('new-staff-pass', session()->getOldInput());
        $this->assertNotContains('wrong-password', session()->getOldInput());
        $this->assertEmpty(Storage::disk('local')->allFiles('staff-photos'));
        $this->assertTrue(Hash::check('staff-pass', $staff->fresh()->password));
        $this->postJson(route('staff.profile.update'), $change + ['current_password' => 'staff-pass'])->assertOk()->assertJsonPath('success', true);
        $this->assertTrue(Hash::check('new-staff-pass', $staff->fresh()->password));
    }

    public function test_staff_endpoints_require_an_active_employee(): void
    {
        $this->getJson('/staff/notifications')->assertUnauthorized();
        $this->postJson('/staff/notifications/read-all')->assertUnauthorized();
        $this->get('/staff/profile/photo')->assertRedirect(route('login.form'));
        $staff = $this->employee(['is_active' => false]);
        $this->actingAs($staff, 'staff')->postJson('/staff/profile/update', $this->details($staff))->assertUnauthorized();
    }

    public function test_notification_feed_and_read_actions_are_scoped_by_employee_and_model_type(): void
    {
        $staff = $this->employee();
        $other = $this->employee();
        $own = $this->notification($staff);
        $foreign = $this->notification($other);
        $customer = Notification::create(['notifiable_type' => User::class, 'notifiable_id' => $staff->id, 'title' => 'Customer only', 'message' => 'Private']);
        $this->actingAs($staff, 'staff')->getJson('/staff/notifications')->assertOk()
            ->assertJsonCount(1, 'notifications')->assertJsonCount(0, 'events')->assertJsonPath('unread_count', 1)
            ->assertJsonPath('notifications.0.id', $own->id)->assertHeader('Cache-Control', 'no-store, private');
        $this->postJson('/staff/notifications/'.$foreign->id.'/read')->assertNotFound();
        $this->postJson('/staff/notifications/'.$customer->id.'/read')->assertNotFound();
        $this->postJson('/staff/notifications/'.$own->id.'/read')->assertOk();
        $this->assertNotNull($own->fresh()->read_at);
        $second = $this->notification($staff);
        $this->postJson('/staff/notifications/read-all')->assertOk();
        $this->assertNotNull($second->fresh()->read_at);
        $this->assertNull($foreign->fresh()->read_at);
        $this->assertNull($customer->fresh()->read_at);
    }

    public function test_polling_delivers_only_new_unread_events_and_handles_large_batches_without_skipping(): void
    {
        $staff = $this->employee();
        $first = $this->notification($staff);
        $this->notification($staff, ['read_at' => now()]);
        foreach (range(1, 102) as $number) {
            $this->notification($staff);
        }
        $this->actingAs($staff, 'staff');
        $response = $this->getJson('/staff/notifications?after='.$first->id)->assertOk()
            ->assertJsonCount(30, 'notifications')->assertJsonCount(100, 'events')->assertJsonPath('unread_count', 103);
        $cursor = $response->json('latest_id');
        $this->getJson('/staff/notifications?after='.$cursor)->assertOk()->assertJsonCount(2, 'events');
        $this->getJson('/staff/notifications?after=-1')->assertUnprocessable();
    }

    public function test_real_order_events_notify_active_employees_with_independent_read_state(): void
    {
        $one = $this->employee();
        $two = $this->employee();
        $this->employee(['status' => 'Inactive']);
        $this->employee(['is_active' => false]);
        $order = $this->order();
        $this->assertSame(1, $one->notifications()->count());
        $this->assertSame(1, $two->notifications()->count());
        $this->assertDatabaseCount('notifications', 3);
        $this->assertSame('Booking received', $order->user->laundryNotifications()->sole()->title);
        $this->assertSame(route('staff.orders.show', $order->id, false), $one->notifications()->first()->action_url);
        $one->notifications()->first()->markAsRead();
        $this->assertNull($two->notifications()->first()->read_at);
        $this->actingAs($one, 'staff');
        app(LaundryOrderService::class)->advance($order, 'staff', $one->id, 'Pending');
        $this->assertSame(1, $one->notifications()->count());
        $this->assertSame(2, $two->notifications()->count());
        $this->assertSame('Laundry status updated', $two->notifications()->latest('id')->first()->title);
        app(LaundryOrderService::class)->recordPayment($order, ['payment_method' => 'Cash']);
        $this->assertSame('Payment recorded', $two->notifications()->latest('id')->first()->title);
        app(LaundryOrderService::class)->recordPayment($order, ['payment_method' => 'Cash']);
        $this->assertSame(3, $two->notifications()->count());
    }

    public function test_staff_does_not_receive_a_popup_for_the_order_they_created(): void
    {
        $one = $this->employee();
        $two = $this->employee();
        $this->actingAs($one, 'staff');
        $this->order('staff', $one->id);
        $this->assertSame(0, $one->notifications()->count());
        $this->assertSame(1, $two->notifications()->count());
    }
}
