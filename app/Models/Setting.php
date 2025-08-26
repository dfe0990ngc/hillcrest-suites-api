<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';
    protected $fillable = [
        'hotel_name',
        'currency',
        'hotel_address',
        'phone',
        'email',
        'check_in',
        'check_out',
        'tax_rate',
        'notify_new_booking',
        'notify_booking_cancellation',
        'notify_booking_payment_confirmation',
        'enable_push_notification',
        'session_timeout',
        'password_policy',
        'smtp',
        'bcc_emails',
    ];

    protected $appends = ['currency_symbol'];

    protected $casts = [
        'smtp' => 'array',
    ];

    public function getCurrencySymbolAttribute(){
        $currency_symbols = [
            'USD' => '$',
            'PHP' => '₱',
            'JPY' => '¥',
        ];

        return $currency_symbols[$this->currency] ?? '₱';
    }
}
