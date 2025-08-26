<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Notifications\PartialPayment;
use App\Notifications\PaymentConfirmed;
use App\Services\PaymentAnalyticsService;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StorePaymentRequest;

class PaymentController extends Controller
{
    protected $paymentAnalyticsService;

    public function __construct(PaymentAnalyticsService $paymentAnalyticsService)
    {
        $this->paymentAnalyticsService = $paymentAnalyticsService;
    }

    /**
     * Display a listing of payments with optional filters
     */
    public function index(Request $request)
    {
        $query = Payment::where('is_void',false)->with(['booking', 'user', 'processedBy']);

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->whereHas('booking', function ($subQ) use ($search) {
                    $subQ->where('code', 'like', "%{$search}%");
                })
                ->orWhereHas('user', function ($subQ) use ($search) {
                    $subQ->where('name', 'like', "%{$search}%");
                })
                ->orWhere('payment_reference', 'like', "%{$search}%");
            });
        }

        // Apply payment method filter
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->input('payment_method'));
        }

        // Apply time range filter
        if ($request->filled('time_range')) {
            $dateRange = $this->getDateRangeFromTimeRange($request->input('time_range', '6months'));
            $startDate = $dateRange['start'];
            $endDate = Carbon::parse($dateRange['end'])->endOfDay();
            
            $query->whereBetween('payment_date', [$startDate, $endDate]);
        }

        // Get pagination parameters
        $perPage = $request->input('per_page', 15);
        $perPage = in_array($perPage, [10, 15, 25, 50]) ? $perPage : 15; // Validate per_page values

        $payments = $query->orderBy('payment_date', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $payments->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'booking_code' => $payment->booking->code ?? 'N/A',
                    'guest_name' => $payment->user->name ?? ($payment->authorized_representative ?? 'Unknown'),
                    'amount' => $payment->amount,
                    'payment_method' => $payment->payment_method,
                    'payment_reference' => $payment->payment_reference,
                    'payment_date' => $payment->payment_date->format('Y-m-d'),
                    'status' => $payment->status,
                    'notes' => $payment->notes,
                    'receipt_url' => $payment->receipt_url,
                    'processed_by' => $payment->processedBy ? $payment->processedBy->name : null,
                    'created_at' => $payment->created_at->format('Y-m-d H:i:s'),
                ];
            }),
            'pagination' => [
                'current_page' => $payments->currentPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
                'last_page' => $payments->lastPage(),
                'from' => $payments->firstItem(),
                'to' => $payments->lastItem(),
            ],
            'filters' => [
                'search' => $request->input('search', ''),
                'payment_method' => $request->input('payment_method', ''),
                'time_range' => $request->input('time_range', '6months'),
            ]
        ]);
    }

    /**
     * Store a newly created payment
     */
    public function store(StorePaymentRequest $request)
    {
        $validated = $request->validated();
        try {
            // Find booking
            $booking = Booking::where('code', $validated['booking_code'])->first();

            // Create payment record
            $payment = Payment::create([
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'payment_reference' => $validated['payment_reference'],
                'payment_date' => Carbon::parse($validated['payment_date']),
                'status' => Payment::STATUS_COMPLETED, // Default to completed for manual entries
                'notes' => $validated['notes'],
                'receipt_url' => $validated['receipt_url'],
                'processed_by' => Auth::id(),
                'authorized_representative' => $validated['guest_name'],
            ]);

            // Update booking payment status if fully paid
            $totalPayments = Payment::where('booking_id', $booking->id)
                ->where('status', Payment::STATUS_COMPLETED)
                ->sum('amount');

            if ($totalPayments >= ($booking->total_amount)) {
                $booking->update(['payment_status' => 'paid']);
                try{
                    $payment->user->notify(new PaymentConfirmed($payment));
                }catch(\Exception $ei){
                    Log::debug('Sending PaymentConfirmation Error',[$ei->getMessage()]);
                }
            }else{
                $payment->total_amount_paid = $totalPayments;
                try{
                    $payment->user->notify(new PartialPayment($payment));
                }catch(\Exception $ei){
                    Log::debug('Sending Partial Payment Confirmation Error',[$ei->getMessage()]);
                }
            }

            if($booking->status == 'pending'){
                $booking->update(['status' => 'confirmed']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment recorded successfully',
                'data' => [
                    'id' => $payment->id,
                    'booking_code' => $booking->code,
                    'guest_name' => $booking->user->name,
                    'authorized_representative' => $payment->authorized_representative,
                    'amount' => $payment->amount,
                    'payment_method' => $payment->payment_method,
                    'payment_reference' => $payment->payment_reference,
                    'payment_date' => $payment->payment_date->format('Y-m-d'),
                    'status' => $payment->status,
                    'notes' => $payment->notes,
                    'receipt_url' => $payment->receipt_url,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment record',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper method to get date range from time range
     */
    private function getDateRangeFromTimeRange($timeRange)
    {
        $endDate = Carbon::now();

        switch ($timeRange) {
            case '7days':
                $startDate = Carbon::now()->subDays(7);
                break;
            case '30days':
                $startDate = Carbon::now()->subDays(30);
                break;
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
                $startDate = Carbon::now()->subMonths(6); // fallback
        }

        return [
            'start' => $startDate->startOfDay(),
            'end'   => $endDate->endOfDay(),
        ];
    }


    /**
     * Calculate success rate for given date range
     */
    private function calculateSuccessRate($startDate, $endDate)
    {
        $totalPayments = Payment::whereBetween('payment_date', [$startDate, $endDate])
            ->whereIn('status', [Payment::STATUS_COMPLETED, Payment::STATUS_FAILED])
            ->count();

        if ($totalPayments == 0) return 0;

        $completedPayments = Payment::whereBetween('payment_date', [$startDate, $endDate])
            ->where('status', Payment::STATUS_COMPLETED)
            ->count();

        return round(($completedPayments / $totalPayments) * 100, 1);
    }

    /**
     * Calculate average payment processing time
     */
    private function calculateAvgPaymentTime($startDate, $endDate)
    {
        $payments = Payment::join('bookings', 'payments.booking_id', '=', 'bookings.id')
            ->whereBetween('payments.payment_date', [$startDate, $endDate])
            ->where('payments.status', Payment::STATUS_COMPLETED)
            ->selectRaw('AVG(DATEDIFF(payments.payment_date, bookings.created_at)) as avg_days')
            ->first();

        $avgDays = round($payments->avg_days ?? 0, 1);
        return $avgDays . ' days';
    }
    
    /**
     * Update the specified payment
     */
    public function update(Request $request, Payment $payment)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'sometimes|numeric|min:0',
            'payment_method' => 'sometimes|string|in:cash,credit_card,debit_card,bank_transfer,mobile_payment,check',
            'payment_reference' => 'nullable|string|max:255',
            'payment_date' => 'sometimes|date',
            'status' => 'sometimes|string|in:completed,pending,failed',
            'notes' => 'nullable|string|max:1000',
            'receipt_url' => 'nullable|string|max:1024',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $payment->update($request->only([
                'amount', 'payment_method', 'payment_reference', 
                'payment_date', 'status', 'notes','receipt_url',
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Payment updated successfully',
                'data' => $payment->fresh()
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified payment
     */
    public function destroy(Request $request, Payment $payment)
    {
        try {
            // Delete receipt file if exists
            // if ($payment->receipt_url) {
            //     $path = str_replace('/storage/', '', $payment->receipt_url);
            //     Storage::disk('public')->delete($path);
            // }

            if($request->user()->id != $payment->processed_by){
                return response()->json(['message' => 'You cannot void this record. Only the creator of this record is authorized!']);
            }

            $booking = $payment->booking()->first();

            $payment->update(['is_void' => true]);

            // Update booking status when needed
            $amount = $booking->payments()->where('is_void',false)->sum('amount') ?? 0;
            if($amount == 0){
                $booking->payment_status = 'pending';
                $booking->save();
            }

            // $payment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Payment has been void successfully',
                'data' => $payment->fresh(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment analytics
     */
    public function analytics(Request $request)
    {
        $timeRange = $request->input('time_range', '6months');
        
        try {
            $analytics = $this->paymentAnalyticsService->getAnalyticsByTimeRange($timeRange);
            
            return response()->json([
                'success' => true,
                'data' => $analytics
            ]);

        } catch (Exception $e) {
             return response()->json([
                'success' => false,
                'message' => 'Failed to fetch analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}