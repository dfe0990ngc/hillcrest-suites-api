<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Booking;
use App\Models\Payment;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'reset_password_key',
        'reset_password_valid_until',
        'profile_url',
        // 'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'reset_password_key',
        'reset_password_valid_until',
    ];

    protected $appends = [
        'total_bookings',
        'total_spent',
        'total_nights',
        'total_payments_made',
        'payment_methods_used',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'reset_password_key' => 'hashed',
            'reset_password_valid_until' => 'datetime',
        ];
    }
    
    // Single query to get all stats
    public function getBookingStatsAttribute()
    {
        static $stats;
        
        if (!isset($stats)) {
            $stats = $this->bookings()
                ->leftJoin('payments', 'payments.booking_id', '=', 'bookings.id')
                ->selectRaw('
                    COUNT(DISTINCT bookings.id) as total_bookings,
                    COALESCE(SUM(payments.amount), 0) as total_spent,
                    SUM(CASE 
                            WHEN bookings.status IN ("confirmed","checked_in","checked_out") 
                            THEN DATEDIFF(bookings.check_out, bookings.check_in) 
                            ELSE 0 
                        END) as total_nights
                ')
                ->first();
        }
        
        return $stats;
    }

    public function getTotalBookingsAttribute(){
        return intval($this->booking_stats->total_bookings ?? 0);
    }

    public function getTotalSpentAttribute(){
        return floatval($this->booking_stats->total_spent ?? 0);
    }

    public function getTotalNightsAttribute(){
        return intVal($this->booking_stats->total_nights ?? 0);
    }

    /**
     * Get total number of payments made by this user
     */
    public function getTotalPaymentsMadeAttribute()
    {
        return $this->payments()->where('is_void',false)->count();
    }

    /**
     * Get unique payment methods used by this user
     */
    public function getPaymentMethodsUsedAttribute()
    {
        return $this->payments()->where('is_void',false)
            ->distinct('payment_method')
            ->pluck('payment_method')
            ->map(function ($method) {
                return ucwords(str_replace('_', ' ', $method));
            })
            ->toArray();
    }
	
	public function bookings(){
		return $this->hasMany(Booking::class);
	}

    /**
     * Get all payments made by this user
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get completed payments only
     */
    public function completedPayments(): HasMany
    {
        return $this->hasMany(Payment::class)->where('status', Payment::STATUS_COMPLETED);
    }

    /**
     * Get pending payments only
     */
    public function pendingPayments(): HasMany
    {
        return $this->hasMany(Payment::class)->whereHas('booking', function($q){
            $q->where('payment_status',Payment::STATUS_PENDING);
        });
    }

    /**
     * Get payments processed by this user (if they are staff)
     */
    public function processedPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'processed_by');
    }

    /**
     * Get the latest payment made by this user
     */
    public function latestPayment()
    {
        return $this->payments()->where('is_void',false)->latest('payment_date')->first();
    }

    /**
     * Get payment statistics for this user
     */
    public function getPaymentStats()
    {
        $stats = $this->payments()
            ->where('is_void', false)
            ->selectRaw('
                COUNT(*) as total_payments,
                SUM(CASE WHEN status = "completed" THEN amount ELSE 0 END) as total_paid,
                AVG(CASE WHEN status = "completed" THEN amount END) as avg_payment_amount
            ')
            ->first();

        // If you want pending from bookings, do it separately
        $totalBookingsPending = $this->bookings()
            ->where('payment_status', 'pending')
            ->sum('total_amount');

        $totalPending = $totalBookingsPending - ($stats->total_paid ?? 0);

        return [
            'total_payments' => (int) ($stats->total_payments ?? 0),
            'total_paid' => (float) ($stats->total_paid ?? 0),
            'total_pending' => (float) max($totalPending, 0), // prevent negative
            'avg_payment_amount' => (float) ($stats->avg_payment_amount ?? 0),
        ];
    }

    /**
     * Check if user has any pending payments
     */
    public function hasPendingPayments(): bool
    {
        return $this->pendingPayments()->exists();
    }

    /**
     * Get the most used payment method by this user
     */
    public function getMostUsedPaymentMethod()
    {
        return $this->payments()->where('is_void',false)
            ->groupBy('payment_method')
            ->selectRaw('payment_method, COUNT(*) as count')
            ->orderByDesc('count')
            ->first();
    }
}