<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    public function showRegisterForm()
    {
        return view('credentials.register');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'password' => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            if ($request->headers->has('HX-Request')) {
                $errors = '<div class="alert alert-danger"><ul>';
                foreach ($validator->errors()->all() as $error) {
                    $errors .= "<li>$error</li>";
                }
                $errors .= '</ul></div>';

                return response($errors);
            }
            return back()->withErrors($validator)->withInput();
        }

        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'phone' => $request->phone,
            'address' => $request->address,
            'password' => Hash::make($request->password),
            'role' => 'customer',
        ]);

        if ($request->headers->has('HX-Request')) {
            return response('<div class="alert alert-success">Account created! Redirecting to login...</div>')
                ->header('HX-Redirect', route('login.form'));
        }

        return redirect()->route('login.form')->with('success', 'Account created successfully. Please log in.');
    }
}
