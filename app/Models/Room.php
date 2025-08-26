<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class Room extends Model
{
    protected $table = 'rooms';
    protected $fillable = [
        'number',
        'type',
        'price_per_night',
        'capacity',
        'amenities',
        'description',
        'images',
        'status',
        'floor',
    ];

    protected $casts = [
        'amenities' => 'array',
        'images' => 'array',
        'price_per_night' => 'decimal:2',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Check if room is available for given date range
     * 
     * @param string|Carbon $checkIn
     * @param string|Carbon $checkOut
     * @param int|null $excludeBookingId Exclude specific booking (useful for updates)
     * @return bool
     */
    public function isAvailable($checkIn, $checkOut, $excludeBookingId = null): bool
    {
        $checkIn = Carbon::parse($checkIn);
        $checkOut = Carbon::parse($checkOut);

        // Room must be available status
        if ($this->status !== 'Available') {
            return false;
        }

        // Check for overlapping bookings
        $query = $this->bookings()
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($checkIn, $checkOut) {
                $query->where(function ($q) use ($checkIn, $checkOut) {
                    // Booking starts before our checkout and ends after our checkin
                    $q->where('check_in', '<', $checkOut)
                      ->where('check_out', '>', $checkIn);
                });
            });

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return !$query->exists();
    }

    /**
     * Get availability status for a date range
     * 
     * @param string|Carbon $checkIn
     * @param string|Carbon $checkOut
     * @param int|null $excludeBookingId
     * @return array
     */
    public function getAvailabilityStatus($checkIn, $checkOut, $excludeBookingId = null): array
    {
        $checkIn = Carbon::parse($checkIn);
        $checkOut = Carbon::parse($checkOut);

        $isAvailable = $this->isAvailable($checkIn, $checkOut, $excludeBookingId);
        
        $conflictingBookings = [];
        if (!$isAvailable) {
            $conflictingBookings = $this->bookings()
                ->where('status', '!=', 'cancelled')
                ->where(function ($query) use ($checkIn, $checkOut) {
                    $query->where('check_in', '<', $checkOut)
                          ->where('check_out', '>', $checkIn);
                })
                ->when($excludeBookingId, function ($query) use ($excludeBookingId) {
                    return $query->where('id', '!=', $excludeBookingId);
                })
                ->get(['id', 'code', 'check_in', 'check_out', 'status']);
        }

        return [
            'available' => $isAvailable,
            'room_status' => $this->status,
            'conflicting_bookings' => $conflictingBookings,
            'check_in' => $checkIn->toDateString(),
            'check_out' => $checkOut->toDateString(),
        ];
    }

    /**
     * Scope to filter available rooms for date range
     */
    public function scopeAvailableForDates(Builder $query, $checkIn, $checkOut, $excludeBookingId = null)
    {
        $checkIn = Carbon::parse($checkIn);
        $checkOut = Carbon::parse($checkOut);

        return $query->where('status', 'Available')
            ->whereDoesntHave('bookings', function ($bookingQuery) use ($checkIn, $checkOut, $excludeBookingId) {
                $bookingQuery->where('status', '!=', 'cancelled')
                    ->where(function ($q) use ($checkIn, $checkOut) {
                        $q->where('check_in', '<', $checkOut)
                          ->where('check_out', '>', $checkIn);
                    })
                    ->when($excludeBookingId, function ($query) use ($excludeBookingId) {
                        return $query->where('id', '!=', $excludeBookingId);
                    });
            });
    }

    /**
     * Scope to filter by room type and capacity
     */
    public function scopeFilterByTypeAndCapacity(Builder $query, $type = null, $minCapacity = null)
    {
        return $query->when($type, function ($query) use ($type) {
                return $query->where('type', $type);
            })
            ->when($minCapacity, function ($query) use ($minCapacity) {
                return $query->where('capacity', '>=', $minCapacity);
            });
    }

    /**
     * Static method to find available rooms
     * 
     * @param string|Carbon $checkIn
     * @param string|Carbon $checkOut
     * @param string|null $type
     * @param int|null $minCapacity
     * @param int|null $excludeBookingId
     * @return Collection
     */
    public static function findAvailable($checkIn, $checkOut, $type = null, $minCapacity = null, $excludeBookingId = null): Collection
    {
        return static::availableForDates($checkIn, $checkOut, $excludeBookingId)
            ->filterByTypeAndCapacity($type, $minCapacity)
            ->orderBy('price_per_night')
            ->get();
    }

    /**
     * Static method to check availability for multiple rooms
     * 
     * @param array $roomIds
     * @param string|Carbon $checkIn
     * @param string|Carbon $checkOut
     * @param int|null $excludeBookingId
     * @return array
     */
    public static function checkMultipleAvailability(array $roomIds, $checkIn, $checkOut, $excludeBookingId = null): array
    {
        $rooms = static::whereIn('id', $roomIds)->get();
        $availability = [];

        foreach ($rooms as $room) {
            $availability[$room->id] = [
                'room' => $room,
                'availability' => $room->getAvailabilityStatus($checkIn, $checkOut, $excludeBookingId)
            ];
        }

        return $availability;
    }

    /**
     * Get room availability calendar for a month
     * 
     * @param int $month
     * @param int $year
     * @return array
     */
    public function getMonthlyAvailabilityCalendar(int $month, int $year): array
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        
        $bookings = $this->bookings()
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where('check_in', '<=', $endDate)
                      ->where('check_out', '>=', $startDate);
            })
            ->get();

        $calendar = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $isBooked = $bookings->some(function ($booking) use ($currentDate) {
                return $currentDate->between(
                    Carbon::parse($booking->check_in), 
                    Carbon::parse($booking->check_out)->subDay()
                );
            });

            $calendar[$currentDate->toDateString()] = [
                'date' => $currentDate->toDateString(),
                'available' => !$isBooked && $this->status === 'Available',
                'room_status' => $this->status,
            ];

            $currentDate->addDay();
        }

        return $calendar;
    }

    /**
     * Get next available date after given date
     * 
     * @param string|Carbon $fromDate
     * @param int $nights
     * @return Carbon|null
     */
    public function getNextAvailableDate($fromDate, int $nights = 1): ?Carbon
    {
        $fromDate = Carbon::parse($fromDate);
        $maxSearchDays = 365; // Search up to 1 year ahead
        
        for ($i = 0; $i < $maxSearchDays; $i++) {
            $checkIn = $fromDate->copy()->addDays($i);
            $checkOut = $checkIn->copy()->addDays($nights);
            
            if ($this->isAvailable($checkIn, $checkOut)) {
                return $checkIn;
            }
        }
        
        return null;
    }
}