<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Order;

class LandingController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $order = [];

        if ($user) {
            $order = Order::where('user_id', $user->id)->latest()->get();
        }

        return view('landing', compact('user', 'order'));
    }

    public function landingPage()
{
    $orders = Order::where('user_id', auth()->id())->latest()->get();
    return view('landing', compact('orders'));
}
}
