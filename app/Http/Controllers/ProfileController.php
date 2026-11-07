<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\UserLog;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'password' => 'nullable|string|min:8|confirmed',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user->name = $request->input('name');
        $user->username = $request->input('username');
        $user->phone = $request->input('phone');
        $user->address = $request->input('address');
        $loggedChanges = [];

        if ($request->filled('password')) {
            $user->password = Hash::make($request->input('password'));
            $loggedChanges[] = 'Changed password';
        }

        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('profile_photos', 'public');
            $user->photo = $photoPath;
            $loggedChanges[] = 'Updated profile photo';
            session()->flash('success', 'You changed your profile photo ' . Auth::user()->photo);
        }

        $user->save();

        foreach ($loggedChanges as $action) {
            UserLog::create([
                'user_id' => $user->id,
                'action' => $action,
            ]);
        }

        UserLog::create([
            'user_id' => $user->id,
            'action' => 'Updated profile details',
        ]);

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }
}
