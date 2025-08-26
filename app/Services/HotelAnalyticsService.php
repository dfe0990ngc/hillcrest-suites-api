<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HotelAnalyticsService
{
    /**
     * Get comprehensive hotel analytics data
     */
    public function getAnalytics($timeRange = '6months')
    {
        $dateRange = $this->getDateRangeFromTimeRange($timeRange);
        $startDate = $dateRange['start'];
        $endDate = $dateRange['end'];

        // Get previous period for trend calculation
        $previousDateRange = $this->getPreviousPeriodDateRange($timeRange);
        $previousStartDate = $previousDateRange['start'];
        $previousEndDate = $previousDateRange['end'];

        // Current period metrics
        $currentRevenue = $this->getTotalRevenue($startDate, $endDate);
        $currentBookings = $this->getTotalBookings($startDate, $endDate);
        $currentOccupancy = $this->getOccupancyRate($startDate, $endDate);
        $currentADR = $this->getAverageDailyRate($startDate, $endDate);

        // Previous period metrics for trend calculation
        $previousRevenue = $this->getTotalRevenue($previousStartDate, $previousEndDate);
        $previousBookings = $this->getTotalBookings($previousStartDate, $previousEndDate);
        $previousOccupancy = $this->getOccupancyRate($previousStartDate, $previousEndDate);
        $previousADR = $this->getAverageDailyRate($previousStartDate, $previousEndDate);

        return [
            'total_bookings' => $currentBookings,
            'total_bookings_trend' => $this->calculateTrend($currentBookings, $previousBookings),
            'total_revenue' => $currentRevenue,
            'total_revenue_trend' => $this->calculateTrend($currentRevenue, $previousRevenue),
            'occupancy_rate' => $currentOccupancy,
            'occupancy_rate_trend' => $this->calculateTrend($currentOccupancy, $previousOccupancy),
            'average_daily_rate' => $currentADR,
            'average_daily_rate_trend' => $this->calculateTrend($currentADR, $previousADR),
            'monthly_revenue' => $this->getMonthlyRevenue($startDate, $endDate),
            'room_type_bookings' => $this->getRoomTypeBookings($startDate, $endDate),
            'booking_status_distribution' => $this->getBookingStatusDistribution($startDate, $endDate),
            
            // Additional metrics for the UI
            'revenue_per_available_room' => $this->getRevPAR($startDate, $endDate),
            'average_length_of_stay' => $this->getAverageLengthOfStay($startDate, $endDate),
            'customer_satisfaction' => 4.6, // Mock data - integrate with your review system
            'repeat_guest_rate' => $this->getRepeatGuestRate($startDate, $endDate),
        ];
    }

    /**
     * Convert time range string to date range
     */
    private function getDateRangeFromTimeRange($timeRange)
    {
        $endDate = Carbon::now();
        
        switch ($timeRange) {
            case '1month':
                $startDate = Carbon::now()->subMonth();
                break;
            case '3months':
                $startDate = Carbon::now()->subMonths(3);
                break;
            case '6months':
                $startDate = Carbon::now()->subMonths(6);
                break;
            case '1year':
                $startDate = Carbon::now()->subYear();
                break;
            default:
                $startDate = Carbon::now()->subMonths(6);
        }

        return [
            'start' => $startDate->startOfDay(),
            'end' => $endDate->endOfDay()
        ];
    }

    /**
     * Get previous period date range for trend calculation
     */
    private function getPreviousPeriodDateRange($timeRange)
    {
        $endDate = Carbon::now();
        
        switch ($timeRange) {
            case '1month':
                $startDate = Carbon::now()->subMonths(2);
                $endDate = Carbon::now()->subMonth();
                break;
            case '3months':
                $startDate = Carbon::now()->subMonths(6);
                $endDate = Carbon::now()->subMonths(3);
                break;
            case '6months':
                $startDate = Carbon::now()->subYear();
                $endDate = Carbon::now()->subMonths(6);
                break;
            case '1year':
                $startDate = Carbon::now()->subYears(2);
                $endDate = Carbon::now()->subYear();
                break;
            default:
                $startDate = Carbon::now()->subYear();
                $endDate = Carbon::now()->subMonths(6);
        }

        return [
            'start' => $startDate->startOfDay(),
            'end' => $endDate->endOfDay()
        ];
    }

    /**
     * Calculate trend percentage and direction
     */
    private function calculateTrend($current, $previous)
    {
        if ($previous == 0) {
            return [
                'value' => $current > 0 ? 100 : 0,
                'isPositive' => $current >= 0
            ];
        }

        $percentageChange = (($current - $previous) / $previous) * 100;
        
        return [
            'value' => round(abs($percentageChange), 1),
            'isPositive' => $percentageChange >= 0
        ];
    }

    /**
     * Get total bookings count (excluding cancelled)
     */
    private function getTotalBookings($startDate, $endDate)
    {
        return Booking::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotIn('status', ['cancelled'])
            ->count();
    }

    /**
     * Get total revenue from paid bookings
     */
    private function getTotalRevenue($startDate, $endDate)
    {

        return Payment::where('is_void',false)->whereHas('booking', function($q) use($startDate, $endDate){
            $q->whereBetween('created_at', [$startDate, $endDate])->whereNotIn('status', ['cancelled','pending']);
        })->sum('amount') ?: 0;

    }

    /**
     * Calculate occupancy rate as percentage
     */
    private function getOccupancyRate($startDate, $endDate)
    {
        $totalRooms = Room::where('status', 'Available')->count();
        
        if ($totalRooms == 0) return 0;

        $totalDays = $startDate->diffInDays($endDate) + 1;
        $totalPossibleRoomNights = $totalRooms * $totalDays;

        $occupiedRoomNights = Booking::whereBetween('check_in', [$startDate, $endDate])
            ->whereNotIn('status', ['cancelled'])
            ->get()
            ->sum(function ($booking) {
                return max(1, $booking->check_in->diffInDays($booking->check_out));
            });

        return $totalPossibleRoomNights > 0 
            ? round(($occupiedRoomNights / $totalPossibleRoomNights) * 100, 1)
            : 0;
    }

    /**
     * Calculate average daily rate (ADR)
     */
    private function getAverageDailyRate($startDate, $endDate)
    {

        $bookings = Booking::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotIn('status', ['cancelled'])
            ->whereIn('payment_status', ['paid', 'confirmed'])
            ->get();

        if ($bookings->isEmpty()) return 0;

        $totalRevenue = $this->getTotalRevenue($startDate, $endDate);

        $totalRoomNights = $bookings->sum(function ($booking) {
            return max(1, $booking->check_in->diffInDays($booking->check_out));
        });

        return $totalRoomNights > 0 ? round($totalRevenue / $totalRoomNights, 0) : 0;
    }

    /**
     * Get monthly revenue data for charts
     */
    private function getMonthlyRevenue($startDate, $endDate)
    {
        $months = [];
        $current = $startDate->copy()->startOfMonth();
        $end = $endDate->copy()->endOfMonth();

        while ($current <= $end) {
            $monthStart = $current->copy()->startOfMonth();
            $monthEnd = $current->copy()->endOfMonth();

            // Revenue for the month
            $revenue = $this->getTotalRevenue($monthStart, $monthEnd);

            // Bookings count for the month
            $bookings = Booking::whereBetween('created_at', [$monthStart, $monthEnd])
                ->whereNotIn('status', ['cancelled'])
                ->count();

            // Monthly occupancy calculation
            $totalRooms = Room::count();
            $daysInMonth = $monthEnd->day;
            $totalPossibleRoomNights = $totalRooms * $daysInMonth;
            
            $occupiedRoomNights = Booking::whereBetween('check_in', [$monthStart, $monthEnd])
                ->whereNotIn('status', ['cancelled'])
                ->get()
                ->sum(function ($booking) use ($monthStart, $monthEnd) {
                    $checkIn = max($booking->check_in, $monthStart);
                    $checkOut = min($booking->check_out, $monthEnd->addDay());
                    return max(0, $checkIn->diffInDays($checkOut));
                });

            $occupancy = $totalPossibleRoomNights > 0 
                ? round(($occupiedRoomNights / $totalPossibleRoomNights) * 100, 1)
                : 0;

            $months[] = [
                'month' => $current->format('M'),
                'revenue' => (int) $revenue,
                'bookings' => $bookings,
                'occupancy' => $occupancy
            ];

            $current->addMonth();
        }

        return $months;
    }

    /**
     * Get room type booking statistics with percentages
     */
    private function getRoomTypeBookings($startDate, $endDate)
    {
        $roomTypes = Room::distinct('type')->pluck('type');
        $totalRevenue = $this->getTotalRevenue($startDate, $endDate);
        
        $roomTypeData = [];

        foreach ($roomTypes as $type) {
            $bookings = Booking::join('rooms', 'bookings.room_id', '=', 'rooms.id')
                ->where('rooms.type', $type)
                ->whereBetween('bookings.created_at', [$startDate, $endDate])
                ->whereNotIn('bookings.status', ['cancelled'])
                ->select('bookings.*');

            $count = $bookings->count();
            $revenue = $this->getTotalRevenue($startDate,$endDate);

            $percentage = $totalRevenue > 0 ? round(($revenue / $totalRevenue) * 100, 1) : 0;

            $roomTypeData[] = [
                'type' => $type,
                'count' => $count,
                'revenue' => (int) $revenue,
                'percentage' => $percentage
            ];
        }

        // Sort by revenue descending
        usort($roomTypeData, function($a, $b) {
            return $b['revenue'] <=> $a['revenue'];
        });

        return $roomTypeData;
    }

    /**
     * Get booking status distribution with proper counts
     */
    private function getBookingStatusDistribution($startDate, $endDate)
    {
        $statuses = ['Confirmed', 'Checked In', 'Checked Out', 'Pending', 'Cancelled'];
        
        $statusData = [];

        foreach ($statuses as $status) {
            $count = Booking::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', strtolower(str_replace(' ','_',$status)))
                ->count();

            // Only include statuses that have bookings
            if ($count > 0) {
                $statusData[] = [
                    'status' => $status,
                    'count' => $count
                ];
            }
        }

        return $statusData;
    }

    /**
     * Calculate Revenue per Available Room (RevPAR)
     */
    private function getRevPAR($startDate, $endDate)
    {
        $occupancyRate = $this->getOccupancyRate($startDate, $endDate);
        $averageDailyRate = $this->getAverageDailyRate($startDate, $endDate);
        
        return round(($averageDailyRate * $occupancyRate) / 100, 0);
    }

    /**
     * Calculate average length of stay
     */
    private function getAverageLengthOfStay($startDate, $endDate)
    {
        $bookings = Booking::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotIn('status', ['cancelled'])
            ->get();

        if ($bookings->isEmpty()) return 0;

        $totalNights = $bookings->sum(function ($booking) {
            return max(1, $booking->check_in->diffInDays($booking->check_out));
        });

        return round($totalNights / $bookings->count(), 1);
    }

    /**
     * Calculate repeat guest rate
     */
    private function getRepeatGuestRate($startDate, $endDate)
    {
        $totalGuests = Booking::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotIn('status', ['cancelled'])
            ->distinct('user_id')
            ->count('user_id');

        if ($totalGuests == 0) return 0;

        $repeatGuests = DB::table('bookings')
            ->select('user_id')
            ->whereNotIn('status', ['cancelled'])
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->count();

        return round(($repeatGuests / $totalGuests) * 100, 0);
    }

    /**
     * Get analytics with time range filter (for API endpoint)
     */
    public function getAnalyticsByTimeRange($timeRange = '6months')
    {
        return $this->getAnalytics($timeRange);
    }

    /**
     * Get current year analytics (convenience method)
     */
    public function getCurrentYearAnalytics()
    {
        return $this->getAnalytics('1year');
    }

    /**
     * Get analytics for custom date range
     */
    public function getCustomDateRangeAnalytics($startDate, $endDate)
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        return [
            'total_bookings' => $this->getTotalBookings($start, $end),
            'total_revenue' => $this->getTotalRevenue($start, $end),
            'occupancy_rate' => $this->getOccupancyRate($start, $end),
            'average_daily_rate' => $this->getAverageDailyRate($start, $end),
            'monthly_revenue' => $this->getMonthlyRevenue($start, $end),
            'room_type_bookings' => $this->getRoomTypeBookings($start, $end),
            'booking_status_distribution' => $this->getBookingStatusDistribution($start, $end),
            'revenue_per_available_room' => $this->getRevPAR($start, $end),
            'average_length_of_stay' => $this->getAverageLengthOfStay($start, $end),
            'customer_satisfaction' => 4.6,
            'repeat_guest_rate' => $this->getRepeatGuestRate($start, $end),
        ];
    }
}