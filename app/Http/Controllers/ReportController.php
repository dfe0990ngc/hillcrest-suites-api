<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Services\HotelAnalyticsService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    protected $analyticsService;

    public function __construct(HotelAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Get reports data (matches your React component's API call)
     */
    public function index(Request $request)
    {
        try {
            // Get time range from request (matches your UI dropdown)
            $timeRange = $request->input('time_range', '1year');
            
            // Get analytics data
            $analytics = $this->analyticsService->getAnalyticsByTimeRange($timeRange);

            $activities = Activity::orderBy('created_at','desc')->limit(4)->get();

            $analytics['recent_activities'] = $activities;

            return response()->json($analytics, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching reports: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get analytics for specific time range
     */
    public function getByTimeRange(Request $request)
    {
        $request->validate([
            'time_range' => 'in:1month,3months,6months,1year'
        ]);

        $timeRange = $request->input('time_range', '6months');
        $analytics = $this->analyticsService->getAnalyticsByTimeRange($timeRange);

        return response()->json($analytics);
    }

    /**
     * Get analytics for custom date range
     */
    public function getByDateRange(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $analytics = $this->analyticsService->getCustomDateRangeAnalytics($startDate, $endDate);

        return response()->json($analytics);
    }

    /**
     * Export report (placeholder for your download functionality)
     */
    public function export(Request $request)
    {
        $timeRange = $request->input('time_range', '6months');
        $analytics = $this->analyticsService->getAnalyticsByTimeRange($timeRange);

        // Here you would implement PDF/Excel export
        // For now, return the data
        return response()->json([
            'message' => 'Report exported successfully',
            'data' => $analytics
        ]);
    }
}