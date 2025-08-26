<?php 

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Room;
use App\Models\Booking;
use Illuminate\Http\Request;
use App\Services\RoomAvailabilityService;

class GuestBookingController extends Controller
{
    /**
     * Validate booking before creation
     */
    public function validateBooking(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'guests' => 'integer|min:1',
        ]);

        $validation = RoomAvailabilityService::validateBookingAvailability(
            $request->room_id,
            $request->check_in,
            $request->check_out
        );

        if (!$validation['valid']) {
            return response()->json([
                'valid' => false,
                'errors' => $validation['errors'],
                'availability_details' => $validation['availability_details'] ?? null,
            ], 422);
        }

        $room = Room::findOrFail($request->room_id);
        $checkIn = Carbon::parse($request->check_in);
        $checkOut = Carbon::parse($request->check_out);
        $nights = $checkIn->diffInDays($checkOut);

        // Calculate pricing
        $subtotal = $nights * $room->price_per_night;
        $taxRate = config('hotel.tax_rate', 0); // Get from config or hotel settings
        $taxAmount = $subtotal * ($taxRate / 100);
        $totalAmount = $subtotal + $taxAmount;

        return response()->json([
            'valid' => true,
            'room' => $room,
            'pricing' => [
                'nights' => $nights,
                'rate_per_night' => $room->price_per_night,
                'subtotal' => $subtotal,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
            ],
            'booking_details' => [
                'check_in' => $checkIn->toDateString(),
                'check_out' => $checkOut->toDateString(),
                'guests' => $request->guests,
            ]
        ]);
    }

    /**
     * Store a new booking
     */
    public function store(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'guests' => 'required|integer|min:1',
            'total_amount' => 'required|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'special_requests' => 'nullable|string|max:1000',
        ]);

        // Final availability check
        $validation = RoomAvailabilityService::validateBookingAvailability(
            $request->room_id,
            $request->check_in,
            $request->check_out
        );

        if (!$validation['valid']) {
            return response()->json([
                'message' => 'Room is no longer available for the selected dates.',
                'errors' => $validation['errors']
            ], 422);
        }

        $booking = Booking::create([
            'user_id' => auth()->id(),
            'room_id' => $request->room_id,
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
            'guests' => $request->guests,
            'total_amount' => $request->total_amount,
            'tax_amount' => $request->tax_amount,
            'status' => 'pending',
            'payment_status' => 'pending',
            'special_requests' => $request->special_requests,
        ]);

        $booking->load(['room', 'user']);

        return response()->json([
            'message' => 'Booking created successfully!',
            'booking' => $booking,
        ], 201);
    }
}