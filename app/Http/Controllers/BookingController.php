<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Booking;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Notifications\BookingCancelled;
use App\Notifications\BookingConfirmed;
use App\Notifications\PaymentConfirmed;
use App\Models\Activity;

use Illuminate\Support\Carbon;

class BookingController extends Controller
{
    public function index(){
        $bookings = Booking::with(['user','room'])->get();
        return response()->json($bookings);
    }

	public function store(Request $request){
	
		$user = auth()->user();
		
		$validated = $request->validate([
			'check_in' => 'required|date',
			'check_out' => 'required|date',
			'guests' => 'required|numeric|max:20',
			'room_id' => 'required|numeric|exists:rooms,id',
			'total_amount' => 'required|numeric|max:9999',
			'special_requests' => 'required|string|max:255',
		]);
		
		$setting = Setting::first();
		$inTime = $setting->check_in;
		$outTime = $setting->check_out;
		
		$validated['check_in'] = Carbon::parse($request->check_in)->format('Y-m-d').' '.$inTime;
		$validated['check_out'] = Carbon::parse($request->check_out)->format('Y-m-d').' '.$outTime;
        $validated['tax_amount'] = $validated['total_amount'] * $setting->tax_rate / 100;
		
		$booking = $user->bookings()->create($validated);
		
		// ============== Log Activity =============
		$room = $booking->room()->first();
		$days = $booking->check_in->startOfDay()->diffInDays($booking->check_out->startOfDay());

		$type = $booking->status;
		$title = 'New Room Reservation';
		$details = 'Room '.$room->number.' - '.$user->name.' • '.ucFirst($room->type).' Room';
		$subDetails = $days.' nights';

		Activity::create([
			'type' => $type,
			'title' => $title,
			'details' => $details,
			'sub_details' => $subDetails,
		]);
		
		return response()->json([
			'message' => 'Room reservation successful!',
			'booking' => $booking->fresh(),
		],201);
	}

    public function update(Request $request, $id)
    {
        $booking = Booking::with(['user', 'room'])->find($id);

        if (!$booking) {
            return response()->json(['message' => 'Booking record not found!'], 404);
        }

        $validated = $request->validate([
            'payment_status' => 'nullable|in:paid,pending,refund',
            'status' => 'nullable|in:pending,confirmed,checked_in,checked_out,cancelled',
            'payment_reference' => 'nullable|string|max:255',
            'payment_method' => 'nullable|string|max:255',
            'payment_method_account' => 'nullable|string|max:255',
            'payment_date' => 'nullable|date',
            'refund_date' => 'nullable|date',
            'cancellation_reason' => 'nullable|string|max:500', // Added for cancellation
        ]);

        // Filter out null values to avoid overwriting with nulls
        $updateData = array_filter($validated, function($value) {
            return $value !== null && $value !== '';
        });

        if (empty($updateData)) {
            return response()->json(['message' => 'No data provided for update'], 400);
        }

        // Store original values before update for comparison
        $originalStatus = $booking->status;

        // $origPaymentRef = $booking->payment_reference;

        // Update the booking
        $booking->update($updateData);

        // if($origPaymentRef != $booking->payment_reference){
            
        //     // Validate required payment fields
        //     if (!empty($booking->payment_reference) && !empty($booking->payment_date) && !empty($booking->payment_method)) {
        //         // Send payment confirmation notification
        //         try {
        //             $settings = Setting::first();
        //             if ($settings && $settings->notify_booking_payment_confirmation) {
        //                 $booking->user->notify(new PaymentConfirmed($booking));
        //             }
        //         } catch (Exception $e) {
        //             // Log the error but don't fail the update
        //             Log::error('Failed to send payment confirmation email: ' . $e->getMessage());
        //         }
        //     }
        // }

        // Handle status changes
        if ($originalStatus != $booking->status) {
            if ($booking->status == 'cancelled') {
                // Add cancellation metadata
                $booking->update([
                    'cancelled_by_name' => auth()->user()->name,
                    // 'cancelled_at' => now(),
                ]);

                // Send cancellation notification
                try {
                    $settings = Setting::first();
                    if ($settings && $settings->notify_booking_cancellation) {
                        $booking->user->notify(new BookingCancelled($booking));
                    }
                } catch (Exception $e) {
                    // Log the error but don't fail the update
                    Log::error('Failed to send cancellation email: ' . $e->getMessage());
                }
				
				
				// ============== Log Activity =============
                $this->logCancelled($booking);

                $booking->room->update(['status' => 'Available']);

            } elseif ($booking->status == 'confirmed' && $originalStatus == 'pending') {
				
				if($booking->status == 'confirmed'){

                    $setting = Setting::first();
									
					// ============== Log Activity =============
					$room = $booking->room;
					$type = $booking->status;
					$title = 'New Booking Confirmed';
					$details = 'Room '.$room->number.' - '.$booking->user->name.' • '.ucFirst($room->type).' Room';
					$subDetails = $setting->currency_symbol.number_format($booking->total_amount,2).' Revenue';

					Activity::create([
						'type' => $type,
						'title' => $title,
						'details' => $details,
						'sub_details' => $subDetails,
					]);
					
				}

                // Send booking confirmation if moving from pending to confirmed
                try {
                    $booking->user->notify(new BookingConfirmed($booking));
                } catch (Exception $e) {
                    Log::error('Failed to send booking confirmation email: ' . $e->getMessage());
                }
            } else if($booking->status == 'checked_in'){
                $booking->room->update(['status' => 'Occupied']);
            } else if($booking->status == 'checked_out'){
                $booking->room->update(['status' => 'Available']);
            }
        }

        // Refresh the booking to get updated data
        $booking->refresh();

        return response()->json([
            'message' => 'Booking record has been updated successfully!',
            'booking' => $booking->load(['user', 'room']),
        ]);
    }

    // ================== GUEST SECTION ==================
    public function gIndex(Request $request){
        $bookings = $request->user()->bookings()->with(['user','room'])->get();
        return response()->json($bookings);
    }

    public function gCancelBooking(Request $request, $id){
        $booking = $request->user()->bookings()->find($id);

        if(!$booking){
            return response()->json(['message' => 'Booking record not found!'],404);
        }

        $booking->status = 'cancelled';
        $booking->cancelled_by_name = 'Guest ('.auth()->user()->name.')';
        $booking->save();

        // Send cancellation notification
        try {
            $settings = Setting::first();
            if ($settings && $settings->notify_booking_cancellation) {
                $booking->user->notify(new BookingCancelled($booking));
            }
        } catch (Exception $e) {
            // Log the error but don't fail the update
            Log::error('Failed to send cancellation email: ' . $e->getMessage());
        }
        
        // ============== Log Activity =============
        $this->logCancelled($booking);

        return response()->json(['message' => 'Room reservation has been cancelled!']);
    }

    private function logCancelled($booking){
        $room = $booking->room;
        $type = $booking->status;
        $title = 'Cancelled Room Reservation';
        $details = 'Room '.$room->number.' - '.$booking->user->name.' • '.ucFirst($room->type).' Room';
        $subDetails = 'Cancelled by '.$booking->cancelled_by_name;

        Activity::create([
            'type' => $type,
            'title' => $title,
            'details' => $details,
            'sub_details' => $subDetails,
        ]);
    }
}
