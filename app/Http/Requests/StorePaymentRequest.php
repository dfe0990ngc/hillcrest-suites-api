<?php

namespace App\Http\Requests;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Adjust based on your authorization logic
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'booking_code' => [
                'required',
                'string',
                'exists:bookings,code'
            ],
            'guest_name' => [
                'required',
                'string',
                'max:255'
            ],
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:999999.99',
                function ($attribute, $value, $fail) {
                    if ($this->booking_code) {
                        $booking = Booking::where('code', $this->booking_code)->first();

                        if ($booking) {
                            $totalPaid = Payment::where('is_void', false)
                                ->where('booking_id', $booking->id)
                                ->sum('amount');

                            $remaining = $booking->total_amount - $totalPaid;
                            if($remaining < 0){
                                $remaining = 0;
                            }

                            if($booking->total_amount <= $totalPaid && $value > $remaining){
                                $fail("Payment amount exceeds remaining balance of " . number_format($remaining, 2));
                            }
                        }
                    }
                },
            ],
            'payment_method' => [
                'required',
                'string',
                'in:' . implode(',', [
                    Payment::METHOD_CASH,
                    Payment::METHOD_CREDIT_CARD,
                    Payment::METHOD_DEBIT_CARD,
                    Payment::METHOD_BANK_TRANSFER,
                    Payment::METHOD_MOBILE_PAYMENT,
                    Payment::METHOD_CHECK
                ])
            ],
            'payment_reference' => [
                'nullable',
                'string',
                'max:255'
            ],
            'payment_date' => [
                'required',
                'date',
                'before_or_equal:today'
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000'
            ],
            'receipt_url' => [
                'nullable',
                'string',
                'max:1024',
            ]
            // 'receipt_image' => [
            //     'nullable',
            //     'image',
            //     'mimes:jpeg,png,jpg,gif,webp',
            //     'max:2048' // 2MB max
            // ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'booking_code.exists' => 'The selected booking code does not exist.',
            'amount.min' => 'The payment amount must be at least 0.01.',
            'amount.max' => 'The payment amount cannot exceed 999,999.99.',
            'payment_method.in' => 'Please select a valid payment method.',
            'payment_date.before_or_equal' => 'Payment date cannot be in the future.',
            // 'receipt_url.image' => 'Receipt must be an image file.',
            // 'receipt_image.mimes' => 'Receipt must be a JPEG, PNG, JPG, GIF, or WebP file.',
            // 'receipt_image.max' => 'Receipt image size cannot exceed 2MB.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'booking_code' => 'booking code',
            'guest_name' => 'guest name',
            'payment_method' => 'payment method',
            'payment_reference' => 'payment reference',
            'payment_date' => 'payment date',
            // 'receipt_url' => 'receipt url',
        ];
    }
}