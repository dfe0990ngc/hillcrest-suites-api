<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>{{ config('app.name') }} - Payment Confirmed</title>
    <style>
        @media screen and (max-width: 600px) {
            .content, .header-content{
                padding:0px;
                width:100%;
                height:100%;
            }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f4f4; font-family: Arial, sans-serif;">
    
    <!-- Outer Table -->
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;">
        <tr>
            <td class="content" align="center" style="padding: 20px 0; background-color: #008ea2;">
                
                <!-- Main Container -->
                <table border="0" cellpadding="0" cellspacing="0" width="600" style="border-collapse: collapse; max-width: 600px; width: 100%; background-color: #ffffff; border-radius: 8px;">
                    
                    <!-- Header -->
                    <tr>
                        <td class="header-content" align="left" style="padding: 15px 20px; background-color: #ffffff; border-bottom: 1px solid #008ea2;">
                            
                            <!-- Logo and Title Container -->
                            <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt;">
                                <tr>
                                    <!-- Logo -->
                                    <td align="center" valign="middle" style="padding-right: 7px;">
                                        <table border="0" cellpadding="0" cellspacing="0" width="50" height="50" style="border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 50px; height: 50px; background-color: rgba(255, 255, 255, 0.2); border-radius: 25px;">
                                            <tr>
                                                <td align="center" valign="middle">
                                                    <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" width="128" height="128" style="display: block; border: 0; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; max-width: 35px; height: auto;" />
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    
                                    <!-- Title -->
                                    <td align="left" valign="middle">
                                        <h1 style="margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 24px; font-weight: bold; color:#008ea2;; line-height: 1.2;">
                                            {{ config('app.name') }}
                                        </h1>
                                    </td>
                                </tr>
                            </table>
                            
                        </td>
                    </tr>
                    
                    <!-- Body -->
                    <tr>
                        <td style="padding: 40px 30px; font-family: Arial, sans-serif; font-size: 16px; line-height: 1.6; color: #333333;">
                            
                            <h2 style="margin: 0 0 20px 0; font-size: 28px; color: #008ea2; font-weight: bold;">Payment Confirmed ✓</h2>
                            
                            <p style="margin: 0 0 20px 0;">Hello {{ $guest->name ?? 'there' }},</p>
                            
                            <p style="margin: 0 0 30px 0;">Excellent! We've successfully received your full payment for the reservation at {{ config('app.name') }}. Your booking is now confirmed and paid in full.</p>

                            <!-- Payment Confirmation Box -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse; margin: 20px 0;">
                                <tr>
                                    <td style="background-color: #e8f5e8; border: 2px solid #4CAF50; border-radius: 8px; padding: 20px; text-align: center;">
                                        <h3 style="margin: 0 0 10px 0; color: #2E7D32; font-size: 20px;">🎉 Payment Confirmed</h3>
                                        <p style="margin: 0; color: #388E3C; font-size: 16px; font-weight: bold;">
                                            Amount Paid: {{ $setting->currency_symbol }} {{ number_format($payment->amount ?? 0, 2) }}
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Payment Details -->
                            <h3 style="margin: 30px 0 15px 0; font-size: 20px; color: #008ea2;">Payment Details</h3>
                            <p style="margin: 0 0 7px 0;"><strong>Transaction ID:</strong>&nbsp;{{ $payment->payment_reference }}</p>
                            <p style="margin: 0 0 7px 0;"><strong>Payment Method:</strong>&nbsp;{{ $payment->payment_method ?? 'Cashier' }}</p>
                            <p style="margin: 0 0 7px 0;"><strong>Payment Date:</strong>&nbsp;{{ $payment->payment_date?->format('l, F j, Y') }}</p>
                            <p style="margin: 0 0 20px 0;"><strong>Status:</strong>&nbsp;<span style="color: #4CAF50; font-weight: bold;">PAID IN FULL</span></p>

                            <!-- Reservation Details -->
                            <h3 style="margin: 30px 0 15px 0; font-size: 20px; color: #008ea2;">Reservation Details</h3>
                            <p style="margin: 0 0 7px 0;"><strong>Reservation Code:</strong>&nbsp;{{ $payment->booking->code }}</p>
                            <p style="margin: 0 0 7px 0;"><strong>Check-in:</strong>&nbsp;{{ $payment->booking->check_in->format('l, F j, Y') }}</p>
                            <p style="margin: 0 0 7px 0;"><strong>Check-out:</strong>&nbsp;{{ $payment->booking->check_out->format('l, F j, Y') }}</p>
                            <p style="margin: 0 0 7px 0;"><strong>Room:</strong>&nbsp;{{ $payment->booking->room->number }} - {{ $payment->booking->room->type }}</p>
                            <p style="margin: 0 0 20px 0;"><strong>Guests:</strong>&nbsp;{{ $payment->booking->guests }}</p>

                            <!-- Payment Breakdown -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse; margin: 20px 0; border: 1px solid #e9ecef; border-radius: 6px;">
                                <tr>
                                    <td colspan="2" style="background-color: #f8f9fa; padding: 15px; border-bottom: 1px solid #e9ecef;">
                                        <h4 style="margin: 0; color: #008ea2; font-size: 16px;">Payment Summary</h4>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 15px; border-bottom: 1px solid #e9ecef;">Room Rate ({{ $payment->booking->check_in->startOfDay()->diffInDays($payment->booking->check_out->startOfDay()) }} {{ $payment->booking->check_in->startOfDay()->diffInDays($payment->booking->check_out->startOfDay()) == 1 ? 'night' : 'nights' }}):</td>
                                    <td style="padding: 12px 15px; text-align: right; border-bottom: 1px solid #e9ecef;">₱ {{ number_format($payment->booking->room->price_per_night * $payment->booking->check_in->startOfDay()->diffInDays($payment->booking->check_out->startOfDay()), 2) }}</td>
                                </tr>
                                @if(isset($payment->booking->tax_amount) && $payment->booking->tax_amount > 0)
                                <tr>
                                    <td style="padding: 12px 15px; border-bottom: 1px solid #e9ecef;">Taxes & Fees:</td>
                                    <td style="padding: 12px 15px; text-align: right; border-bottom: 1px solid #e9ecef;">{{ $setting->currency_symbol }} {{ number_format($payment->booking->tax_amount, 2) }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td style="padding: 15px; font-weight: bold; background-color: #e8f5e8; color: #2E7D32;">Total Amount Paid:</td>
                                    <td style="padding: 15px; text-align: right; font-weight: bold; background-color: #e8f5e8; color: #2E7D32; font-size: 18px;">{{ $setting->currency_symbol }} {{ number_format(($payment->booking->total_amount), 2) }}</td>
                                </tr>
                            </table>
                            
                            <!-- Amenities -->
                            @if(!empty($payment->booking?->room?->amenities))
                                <h3 style="margin: 30px 0 15px 0; font-size: 20px; color: #008ea2;">Your Room Amenities</h3>
                                @foreach ($payment->booking?->room?->amenities as $amenity)
                                    <p style="margin: 0 0 10px 0;">✓ {{ $amenity }}</p>
                                @endforeach
                            @endif

                            <p style="margin: 30px 0 15px 0;">If you have any questions about your confirmed reservation or need to make changes, please contact us at <a href="mailto:{{ $setting->email }}">{{ $setting->email }}</a>.</p>

                            <p style="margin: 0 0 30px 0;">Thank you for choosing {{ config('app.name') }}. We're excited to welcome you and ensure you have a wonderful stay!</p>
                            <p style="margin: 0;">
                                Best regards,<br>
                                <strong>The {{ config('app.name') }} Team</strong>
                            </p>
                            
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="padding: 30px; background-color: #f8f9fa; border-top: 1px solid #e9ecef; text-align: center; font-family: Arial, sans-serif;">
                            
                            <!-- Copyright -->
                            <p style="margin: 0 0 20px 0; font-size: 14px; color: #6c757d;">
                                © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                            </p>
                            
                            <!-- Footer Links -->
                            <p style="margin: 0; font-size: 12px; color: #6c757d;">
                                This is an automated email. Please do not reply to this message.<br>
                                For assistance, contact us at <a href="mailto:{{ $setting->email }}" style="color: #008ea2;">{{ $setting->email }}</a>
                            </p>
                            
                        </td>
                    </tr>
                    
                </table>
                
            </td>
        </tr>
    </table>
    
</body>
</html>