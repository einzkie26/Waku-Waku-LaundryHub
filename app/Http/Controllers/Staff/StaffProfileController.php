<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StaffProfileController extends Controller
{
    public function show(Request $request)
    {
        return view('staff.profile', ['employee' => $request->attributes->get('staff')]);
    }

    public function photo(Request $request)
    {
        $path = $request->attributes->get('staff')->profile_photo_path;
        abort_unless($path && str_starts_with($path, 'staff-photos/') && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path, null, [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function update(Request $request)
    {
        $employee = $request->attributes->get('staff');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('employees')->ignore($employee->id)],
            'contact' => ['required', 'string', 'max:20'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072', 'dimensions:min_width=64,min_height=64,max_width=4096,max_height=4096'],
            'remove_photo' => ['sometimes', 'boolean'],
            'current_password' => ['nullable', 'required_with:new_password', 'string'],
            'new_password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if ($request->filled('new_password') && ! Hash::check($data['current_password'], $employee->password)) {
            throw ValidationException::withMessages(['current_password' => 'Your current password is incorrect.']);
        }

        $oldPhoto = $employee->profile_photo_path;
        $newPhoto = null;
        try {
            if ($request->hasFile('photo')) {
                $newPhoto = $request->file('photo')->store('staff-photos', 'local');
                if (! $newPhoto) {
                    throw new \RuntimeException('Staff photo could not be stored.');
                }
            }
            $employee->fill(collect($data)->only(['name', 'email', 'contact'])->all());
            if ($newPhoto || $request->boolean('remove_photo')) {
                $employee->profile_photo_path = $newPhoto;
            }
            if ($request->filled('new_password')) {
                $employee->password = Hash::make($data['new_password']);
            }
            $employee->save();
        } catch (\Throwable $exception) {
            if ($newPhoto) {
                Storage::disk('local')->delete($newPhoto);
            }
            report($exception);

            throw ValidationException::withMessages(['photo' => 'We could not save your profile. Please try again.']);
        }

        if ($oldPhoto && $oldPhoto !== $employee->profile_photo_path && str_starts_with($oldPhoto, 'staff-photos/')) {
            Storage::disk('local')->delete($oldPhoto);
        }

        return $request->expectsJson()
            ? response()->json(['success' => true, 'message' => 'Profile updated.', 'photo_url' => $employee->profile_photo_url])
            : redirect()->route('staff.profile')->with('success', 'Your profile has been updated.');
    }
}
