<?php 

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Room;
use Illuminate\Http\Request;
use App\Services\RoomAvailabilityService;
use Illuminate\Support\Facades\Log;

class GuestRoomController extends Controller
{
    /**
     * Get available rooms for specific criteria
     */
    public function getAvailableRooms(Request $request)
    {
        $request->validate([
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'guests' => 'integer|min:1',
            'room_type' => 'nullable|string',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
        ]);

        $checkIn = Carbon::parse($request->check_in);
        $checkOut = Carbon::parse($request->check_out);
        $guests = $request->guests ?? 1;
        $roomType = $request->room_type;

        // Get available rooms
        $query = Room::availableForDates($checkIn, $checkOut)
            ->where('capacity', '>=', $guests);

        if ($request->filled('room_type')) {
            $query->where('type', $roomType);
        }

        if ($request->min_price) {
            $query->where('price_per_night', '>=', $request->min_price);
        }

        if ($request->max_price) {
            $query->where('price_per_night', '<=', $request->max_price);
        }

        $availableRooms = $query->orderBy('price_per_night')->get();

        // Get availability summary
        $summary = RoomAvailabilityService::getAvailabilitySummary($checkIn, $checkOut);

        return response()->json([
            'available_rooms' => $availableRooms,
            'summary' => $summary,
            'search_criteria' => [
                'check_in' => $checkIn->toDateString(),
                'check_out' => $checkOut->toDateString(),
                'guests' => $guests,
                'nights' => $checkIn->diffInDays($checkOut),
            ]
        ]);
    }

    /**
     * Check availability for a specific room
     */
    public function checkAvailability(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
        ]);

        $room = Room::findOrFail($request->room_id);
        $checkIn = Carbon::parse($request->check_in);
        $checkOut = Carbon::parse($request->check_out);

        $availabilityStatus = $room->getAvailabilityStatus($checkIn, $checkOut);

        return response()->json($availabilityStatus);
    }

    /**
     * Get availability calendar for a room
     */
    public function getAvailabilityCalendar(Request $request, Room $room)
    {
        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2024',
        ]);

        $calendar = $room->getMonthlyAvailabilityCalendar($request->month, $request->year);

        return response()->json([
            'room_id' => $room->id,
            'room_number' => $room->number,
            'month' => $request->month,
            'year' => $request->year,
            'calendar' => $calendar,
        ]);
    }

    /**
     * Get availability summary
     */
    public function getAvailabilitySummary(Request $request)
    {
        $request->validate([
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
        ]);

        $checkIn = Carbon::parse($request->check_in);
        $checkOut = Carbon::parse($request->check_out);

        $summary = RoomAvailabilityService::getAvailabilitySummary($checkIn, $checkOut);

        return response()->json($summary);
    }

    /**
     * Get similar available rooms
     */
    public function getSimilarRooms(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'guests' => 'integer|min:1',
        ]);

        $originalRoom = Room::findOrFail($request->room_id);
        $checkIn = Carbon::parse($request->check_in);
        $checkOut = Carbon::parse($request->check_out);
        $guests = $request->guests ?? 1;

        // Find similar rooms with same type first
        $similarRooms = Room::availableForDates($checkIn, $checkOut)
            ->where('id', '!=', $originalRoom->id)
            ->where('capacity', '>=', $guests)
            ->where(function($query) use ($originalRoom) {
                $query->where('type', $originalRoom->type)
                      ->orWhere('capacity', $originalRoom->capacity);
            })
            ->orderByRaw("CASE WHEN type = ? THEN 0 ELSE 1 END", [$originalRoom->type])
            ->orderBy('price_per_night')
            ->limit(5)
            ->get();

        return response()->json([
            'original_room' => $originalRoom,
            'similar_rooms' => $similarRooms,
            'search_criteria' => [
                'check_in' => $checkIn->toDateString(),
                'check_out' => $checkOut->toDateString(),
                'guests' => $guests,
            ]
        ]);
    }
}