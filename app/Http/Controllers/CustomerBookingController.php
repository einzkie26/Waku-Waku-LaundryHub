<?php

namespace App\Http\Controllers;

use App\Services\LaundryOrderService;
use App\Support\LaundryWorkflow;
use Illuminate\Http\Request;

class CustomerBookingController extends Controller
{
    public function create()
    {
        return view('booking.index');
    }

    public function store(Request $request, LaundryOrderService $orders)
    {
        $data = $request->validate(LaundryWorkflow::rules());
        if (!$request->user()->phone || !$request->user()->address) {
            return back()->withInput()->withErrors(['contact' => 'Add your phone number and pickup address in Profile settings before booking.']);
        }

        $order = $orders->create($data, $request->user()->id, 'customer', $request->user()->id);

        return redirect()->route('order.tracking', $order->reference_no)
            ->with('success', 'Booking confirmed! Your next step is to have your laundry ready for pickup.');
    }
}
