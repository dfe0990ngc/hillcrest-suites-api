<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class PaymentAnalyticsService
{
    /**
     * Get comprehensive payment analytics data
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
        $currentTotalCollected = $this->getTotalCollected($startDate, $endDate);
        $currentPendingPayments = $this->getPendingPayments($startDate, $endDate);
        $currentSuccessRate = $this->getSuccessRate($startDate, $endDate);
        $currentAvgPaymentTime = $this->getAveragePaymentTime($startDate, $endDate);

        // Previous period metrics for trend calculation
        $previousTotalCollected = $this->getTotalCollected($previousStartDate, $previousEndDate);
        $previousPendingPayments = $this->getPendingPayments($previousStartDate, $previousEndDate);
        $previousSuccessRate = $this->getSuccessRate($previousStartDate, $previousEndDate);
        $previousAvgPaymentTime = $this->getAveragePaymentTime($previousStartDate, $previousEndDate);

        return [
            'total_collected' => $currentTotalCollected,
            'total_collected_trend' => $this->calculateTrend($currentTotalCollected, $previousTotalCollected),
            'pending_payments' => $currentPendingPayments,
            'pending_payments_trend' => $this->calculateTrend($currentPendingPayments, $previousPendingPayments, true), // reverse trend for pending
            'success_rate' => $currentSuccessRate,
            'success_rate_trend' => $this->calculateTrend($currentSuccessRate, $previousSuccessRate),
            'avg_payment_time' => $currentAvgPaymentTime,
            'avg_payment_time_trend' => $this->calculateTrend($currentAvgPaymentTime, $previousAvgPaymentTime, true), // reverse trend for time
            'today_collected' => $this->getTodayCollected(),
            'this_month_collected' => $this->getThisMonthCollected(),
            'daily_revenue' => $this->getDailyRevenue($startDate, $endDate),
            'payment_method_distribution' => $this->getPaymentMethodDistribution($startDate, $endDate),
            'payment_status_distribution' => $this->getPaymentStatusDistribution($startDate, $endDate),
            
            // Additional metrics
            'total_payment_count' => $this->getTotalPaymentCount($startDate, $endDate),
            'average_payment_amount' => $this->getAveragePaymentAmount($startDate, $endDate),
            'top_payment_methods' => $this->getTopPaymentMethods($startDate, $endDate),
            'monthly_payment_trends' => $this->getMonthlyPaymentTrends($startDate, $endDate),
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
    private function calculateTrend($current, $previous, $reversePositive = false)
    {
        if ($previous == 0) {
            return [
                'value' => $current > 0 ? 100 : 0,
                'isPositive' => $reversePositive ? ($current <= 0) : ($current >= 0)
            ];
        }

        $percentageChange = (($current - $previous) / $previous) * 100;
        $isPositive = $reversePositive ? $percentageChange <= 0 : $percentageChange >= 0;
        
        return [
            'value' => round(abs($percentageChange), 1),
            'isPositive' => $isPositive
        ];
    }

    /**
     * Get total amount collected (completed payments only)
     */
    private function getTotalCollected($startDate, $endDate)
    {
        return Payment::where('is_void',false)->whereBetween('payment_date', [$startDate, $endDate])
            ->where('status', Payment::STATUS_COMPLETED)
            ->sum('amount') ?: 0;
    }

    /**
     * Get total pending payments amount
     */
    private function getPendingPayments($startDate, $endDate)
    {
        $total = Booking::where(function($query) use ($startDate, $endDate) {
            $query->whereBetween('check_in', [$startDate, $endDate])
                  ->orWhereBetween('created_at', [$startDate, $endDate]);
        })
        ->where('payment_status', Payment::STATUS_PENDING)
        ->where('status', '!=', 'cancelled')
        ->selectRaw('COALESCE(SUM(total_amount - (
            SELECT COALESCE(SUM(amount), 0)
            FROM payments
            WHERE payments.booking_id = bookings.id
            AND is_void = 0
        )), 0) as balance')
        ->value('balance');

        return $total ?: 0;
    }

    /**
     * Calculate success rate as percentage
     */
    private function getSuccessRate($startDate, $endDate)
    {
        $totalPayments = Payment::where('is_void',false)->whereBetween('payment_date', [$startDate, $endDate])
            ->whereIn('status', [Payment::STATUS_COMPLETED, Payment::STATUS_FAILED])
            ->count();

        if ($totalPayments == 0) return 0;

        $completedPayments = Payment::where('is_void',false)->whereBetween('payment_date', [$startDate, $endDate])
            ->where('status', Payment::STATUS_COMPLETED)
            ->count();

        return round(($completedPayments / $totalPayments) * 100, 1);
    }

    /**
     * Calculate average payment processing time in days
     */
    private function getAveragePaymentTime($startDate, $endDate)
    {
        $payments = Payment::where('is_void',false)->join('bookings', 'payments.booking_id', '=', 'bookings.id')
            ->whereBetween('payments.payment_date', [$startDate, $endDate])
            ->where('payments.status', Payment::STATUS_COMPLETED)
            ->selectRaw('AVG(DATEDIFF(payments.payment_date, bookings.created_at)) as avg_days')
            ->first();

        return round($payments->avg_days ?? 0, 1);
    }

    /**
     * Get today's collected amount
     */
    private function getTodayCollected()
    {
        return Payment::where('is_void',false)->whereDate('payment_date', Carbon::today())
            ->where('status', Payment::STATUS_COMPLETED)
            ->sum('amount') ?: 0;
    }

    /**
     * Get this month's collected amount
     */
    private function getThisMonthCollected()
    {
        return Payment::where('is_void',false)->whereBetween('payment_date', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth()
            ])
            ->where('status', Payment::STATUS_COMPLETED)
            ->sum('amount') ?: 0;
    }

    /**
     * Get daily revenue data for charts (last 7 days)
     */
    private function getDailyRevenue($startDate, $endDate)
    {
        $days = [];
        $current = max($startDate, Carbon::now()->subDays(6))->copy();
        $end = min($endDate, Carbon::now());

        while ($current <= $end) {
            $dayStart = $current->copy()->startOfDay();
            $dayEnd = $current->copy()->endOfDay();

            $revenue = Payment::where('is_void',false)->whereBetween('payment_date', [$dayStart, $dayEnd])
                ->where('status', Payment::STATUS_COMPLETED)
                ->sum('amount');

            $days[] = [
                'date' => $current->format('M j'),
                'revenue' => (int) $revenue
            ];

            $current->addDay();
        }

        return $days;
    }

    /**
     * Get payment method distribution with counts and percentages
     */
    private function getPaymentMethodDistribution($startDate, $endDate)
    {
        $methods = Payment::where('is_void',false)->whereBetween('payment_date', [$startDate, $endDate])
            ->where('status', Payment::STATUS_COMPLETED)
            ->groupBy('payment_method')
            ->selectRaw('payment_method as method, COUNT(*) as count')
            ->get();

        $totalPayments = $methods->sum('count');
        
        $methodData = [];
        foreach ($methods as $method) {
            $percentage = $totalPayments > 0 ? round(($method->count / $totalPayments) * 100) : 0;
            
            $methodData[] = [
                'method' => ucwords(str_replace('_', ' ', $method->method)),
                'count' => $method->count,
                'percentage' => $percentage
            ];
        }

        // Sort by count descending
        usort($methodData, function($a, $b) {
            return $b['count'] <=> $a['count'];
        });

        return $methodData;
    }

    /**
     * Get payment status distribution
     */
    private function getPaymentStatusDistribution($startDate, $endDate)
    {
        $statuses = Payment::where('is_void',false)->whereBetween('payment_date', [$startDate, $endDate])
            ->groupBy('status')
            ->selectRaw('status, COUNT(*) as count')
            ->get();

        $statusData = [];
        foreach ($statuses as $status) {
            $statusData[] = [
                'status' => ucfirst($status->status),
                'count' => $status->count
            ];
        }

        return $statusData;
    }

    /**
     * Get total payment count
     */
    private function getTotalPaymentCount($startDate, $endDate)
    {
        return Payment::where('is_void',false)->whereBetween('payment_date', [$startDate, $endDate])
            ->count();
    }

    /**
     * Get average payment amount
     */
    private function getAveragePaymentAmount($startDate, $endDate)
    {
        return Payment::where('is_void',false)->whereBetween('payment_date', [$startDate, $endDate])
            ->where('status', Payment::STATUS_COMPLETED)
            ->avg('amount') ?: 0;
    }

    /**
     * Get top payment methods by revenue
     */
    private function getTopPaymentMethods($startDate, $endDate)
    {
        return Payment::where('is_void',false)->whereBetween('payment_date', [$startDate, $endDate])
            ->where('status', Payment::STATUS_COMPLETED)
            ->groupBy('payment_method')
            ->selectRaw('payment_method as method, SUM(amount) as total_amount, COUNT(*) as count')
            ->orderByDesc('total_amount')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'method' => ucwords(str_replace('_', ' ', $item->method)),
                    'total_amount' => (int) $item->total_amount,
                    'count' => $item->count
                ];
            });
    }

    /**
     * Get monthly payment trends
     */
    private function getMonthlyPaymentTrends($startDate, $endDate)
    {
        $months = [];
        $current = $startDate->copy()->startOfMonth();
        $end = $endDate->copy()->endOfMonth();

        while ($current <= $end) {
            $monthStart = $current->copy()->startOfMonth();
            $monthEnd = $current->copy()->endOfMonth();

            $collected = Payment::where('is_void',false)->whereBetween('payment_date', [$monthStart, $monthEnd])
                ->where('status', Payment::STATUS_COMPLETED)
                ->sum('amount');

            $pending = $this->getPendingPayments($monthStart, $monthEnd);

            $count = Payment::whereBetween('payment_date', [$monthStart, $monthEnd])
                ->count();

            $months[] = [
                'month' => $current->format('M'),
                'collected' => (int) $collected,
                'pending' => (int) $pending,
                'count' => $count
            ];

            $current->addMonth();
        }

        return $months;
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
            'total_collected' => $this->getTotalCollected($start, $end),
            'pending_payments' => $this->getPendingPayments($start, $end),
            'success_rate' => $this->getSuccessRate($start, $end),
            'avg_payment_time' => $this->getAveragePaymentTime($start, $end),
            'today_collected' => $this->getTodayCollected(),
            'this_month_collected' => $this->getThisMonthCollected(),
            'daily_revenue' => $this->getDailyRevenue($start, $end),
            'payment_method_distribution' => $this->getPaymentMethodDistribution($start, $end),
            'payment_status_distribution' => $this->getPaymentStatusDistribution($start, $end),
            'total_payment_count' => $this->getTotalPaymentCount($start, $end),
            'average_payment_amount' => $this->getAveragePaymentAmount($start, $end),
            'top_payment_methods' => $this->getTopPaymentMethods($start, $end),
            'monthly_payment_trends' => $this->getMonthlyPaymentTrends($start, $end),
        ];
    }
}