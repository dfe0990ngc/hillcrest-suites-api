<?php

namespace App\Models;

use App\Models\User;
use App\Models\Payment;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    protected $table ='bookings';
    protected $fillable = [
        'code',
        'user_id',
        'room_id',
        'check_in',
        'check_out',
        'guests',
        'total_amount',
        'status',
        'payment_status',
        'special_requests',
        'created_at',

        'payment_reference',
        'payment_date',
        'payment_method',
        'refund_date',

        'cancelled_reason',
        'cancelled_by_name',
        'payment_method_account',

        'tax_amount',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'payment_date' => 'datetime',
        'refund_date' => 'datetime',
    ];

    protected $appends = [
        'total_paid',
        'remaining_balance',
        'payment_completion_percentage'
    ];

    protected static function booted() {
        static::creating(function ($booking) {
            $uid = $booking->user_id;
            $booking->code = strtoupper(Str::random(6)) . Str::padLeft($uid,6,'0');
        });
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function room(){
        return $this->belongsTo(Room::class);
    }

    /**
     * Get all payments for this booking
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
     * Get total amount paid for this booking
     */
    public function getTotalPaidAttribute()
    {
        return $this->payments()->where('is_void',false)
            ->where('status', Payment::STATUS_COMPLETED)
            ->sum('amount') ?: 0;
    }

    /**
     * Get remaining balance for this booking
     */
    public function getRemainingBalanceAttribute()
    {
        return max(0, $this->total_amount - $this->total_paid);
    }

    /**
     * Get payment completion percentage
     */
    public function getPaymentCompletionPercentageAttribute()
    {
        if ($this->total_amount <= 0) {
            return 0;
        }
        
        return min(100, round(($this->total_paid / $this->total_amount) * 100, 1));
    }

    /**
     * Check if booking is fully paid
     */
    public function isFullyPaid(): bool
    {
        return $this->remaining_balance <= 0;
    }

    /**
     * Check if booking has any payments
     */
    public function hasPayments(): bool
    {
        return $this->payments()->where('is_void',false)->exists();
    }

    /**
     * Get the latest payment for this booking
     */
    public function latestPayment()
    {
        return $this->payments()->where('is_void',false)->latest('payment_date')->first();
    }
}