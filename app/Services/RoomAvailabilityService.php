<?php

namespace App\Services;

use App\Models\Room;
use Illuminate\Support\Carbon;

class RoomAvailabilityService
{
    /**
     * Find best available room for requirements
     * 
     * @param string|Carbon $checkIn
     * @param string|Carbon $checkOut
     * @param int $guests
     * @param string|null $preferredType
     * @param array $requiredAmenities
     * @return Room|null
     */
    public static function findBestAvailableRoom($checkIn, $checkOut, int $guests, $preferredType = null, array $requiredAmenities = []): ?Room
    {
        $query = Room::availableForDates($checkIn, $checkOut)
            ->where('capacity', '>=', $guests);

        // Filter by required amenities
        if (!empty($requiredAmenities)) {
            foreach ($requiredAmenities as $amenity) {
                $query->whereJsonContains('amenities', $amenity);
            }
        }

        // Prefer specific type if requested
        if ($preferredType) {
            $preferredRoom = $query->clone()->where('type', $preferredType)->first();
            if ($preferredRoom) {
                return $preferredRoom;
            }
        }

        // Otherwise return cheapest available room that meets requirements
        return $query->orderBy('price_per_night')->first();
    }

    /**
     * Get availability summary for date range
     * 
     * @param string|Carbon $checkIn
     * @param string|Carbon $checkOut
     * @return array
     */
    public static function getAvailabilitySummary($checkIn, $checkOut): array
    {
        $checkIn = Carbon::parse($checkIn);
        $checkOut = Carbon::parse($checkOut);

        $totalRooms = Room::count();
        $availableRooms = Room::availableForDates($checkIn, $checkOut)->count();
        $occupiedRooms = $totalRooms - $availableRooms;
        $maintenanceRooms = Room::where('status', 'Maintenance')->count();

        return [
            'date_range' => [
                'check_in' => $checkIn->toDateString(),
                'check_out' => $checkOut->toDateString(),
                'nights' => $checkIn->diffInDays($checkOut)
            ],
            'total_rooms' => $totalRooms,
            'available_rooms' => $availableRooms,
            'occupied_rooms' => $occupiedRooms,
            'maintenance_rooms' => $maintenanceRooms,
            'occupancy_rate' => $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100, 1) : 0,
            'availability_by_type' => Room::availableForDates($checkIn, $checkOut)
                ->selectRaw('type, count(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray()
        ];
    }

    /**
     * Validate booking dates and room availability
     * 
     * @param int $roomId
     * @param string|Carbon $checkIn
     * @param string|Carbon $checkOut
     * @param int|null $excludeBookingId
     * @return array
     */
    public static function validateBookingAvailability(int $roomId, $checkIn, $checkOut, $excludeBookingId = null): array
    {
        $checkIn = Carbon::parse($checkIn);
        $checkOut = Carbon::parse($checkOut);
        $errors = [];

        // Basic date validation
        if ($checkIn->isPast()) {
            $errors[] = 'Check-in date cannot be in the past';
        }

        if ($checkOut->lte($checkIn)) {
            $errors[] = 'Check-out date must be after check-in date';
        }

        if ($checkIn->diffInDays($checkOut) > 365) {
            $errors[] = 'Booking period cannot exceed 365 days';
        }

        // Room existence and availability
        $room = Room::find($roomId);
        if (!$room) {
            $errors[] = 'Room not found';
            return ['valid' => false, 'errors' => $errors];
        }

        if (!$room->isAvailable($checkIn, $checkOut, $excludeBookingId)) {
            $availabilityStatus = $room->getAvailabilityStatus($checkIn, $checkOut, $excludeBookingId);
            $errors[] = 'Room is not available for the selected dates';
            
            return [
                'valid' => false, 
                'errors' => $errors,
                'availability_details' => $availabilityStatus
            ];
        }

        return ['valid' => true, 'errors' => []];
    }
}