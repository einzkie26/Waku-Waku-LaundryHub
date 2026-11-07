<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Order;

class LandingController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $activeOrders = collect();

        if ($user) {
            $activeOrders = Order::where('user_id', $user->id)
                ->whereNotIn('status', ['Completed', 'Refunded'])->latest()->take(3)->get();
        }

        return view('landing', compact('user', 'activeOrders'));
    }

    public function landingPage()
{
    $orders = Order::where('user_id', auth()->id())->latest()->get();
    return view('landing', compact('orders'));
}
}
