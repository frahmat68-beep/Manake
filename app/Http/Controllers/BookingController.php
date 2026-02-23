<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'equipment_id' => 'required|exists:equipments,id',
            'start_date'   => 'required|date',
            'end_date'     => 'required|date|after_or_equal:start_date',
        ]);

        Booking::create([
            'user_id'      => Auth::id(),
            'equipment_id' => $request->equipment_id,
            'start_date'   => $request->start_date,
            'end_date'     => $request->end_date,
            'status'       => 'pending',
            'reference'    => strtoupper(Str::random(10)),
        ]);

        return redirect()->route('dashboard')
            ->with('success', __('Booking created. Please proceed to payment.'));
    }
}
