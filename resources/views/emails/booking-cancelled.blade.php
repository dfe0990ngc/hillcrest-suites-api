<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>{{ config('app.name') }} - Booking Cancelled</title>
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
                            
                            <h2 style="margin: 0 0 20px 0; font-size: 28px; color: #dc3545; font-weight: bold;">Booking Cancelled</h2>
                            
                            <p style="margin: 0 0 20px 0;">Hello {{ $guest->name ?? 'there' }},</p>
                            
                            <p style="margin: 0 0 30px 0;">We have processed your cancellation request for your reservation at {{ config('app.name') }}. Your booking has been successfully cancelled.</p>

                            <!-- Cancellation Confirmation Box -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse; margin: 20px 0;">
                                <tr>
                                    <td style="background-color: #f8d7da; border: 2px solid #dc3545; border-radius: 8px; padding: 20px; text-align: center;">
                                        <h3 style="margin: 0 0 10px 0; color: #721c24; font-size: 20px;">Cancellation Confirmed</h3>
                                        <p style="margin: 0; color: #721c24; font-size: 16px; font-weight: bold;">
                                            Cancellation Date: {{ $booking->updated_at?->format('l, F j, Y \a\t g:i A') ?? now()->format('l, F j, Y \a\t g:i A') }}
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Cancellation Details -->
                            <h3 style="margin: 30px 0 15px 0; font-size: 20px; color: #008ea2;">Cancellation Details</h3>
                            <p style="margin: 0 0 7px 0;"><strong>Reason:</strong>&nbsp;{{ $booking->cancelled_reason ?? 'Customer request' }}</p>
                            <p style="margin: 0 0 7px 0;"><strong>Cancelled by:</strong>&nbsp;{{ $booking->cancelled_by_name ?? $guest->name ?? 'Guest' }}</p>
                            <p style="margin: 0 0 20px 0;"><strong>Status:</strong>&nbsp;<span style="color: #dc3545; font-weight: bold;">CANCELLED</span></p>

                            <!-- Original Reservation Details -->
                            <h3 style="margin: 30px 0 15px 0; font-size: 20px; color: #008ea2;">Original Reservation Details</h3>
                            <p style="margin: 0 0 7px 0;"><strong>Reservation Code:</strong>&nbsp;{{ $booking->code }}</p>
                            <p style="margin: 0 0 7px 0;"><strong>Original Check-in:</strong>&nbsp;{{ $booking->check_in->format('l, F j, Y') }}</p>
                            <p style="margin: 0 0 7px 0;"><strong>Original Check-out:</strong>&nbsp;{{ $booking->check_out->format('l, F j, Y') }}</p>
                            <p style="margin: 0 0 7px 0;"><strong>Room:</strong>&nbsp;{{ $booking->room->number }} - {{ $booking->room->type }}</p>
                            <p style="margin: 0 0 7px 0;"><strong>Guests:</strong>&nbsp;{{ $booking->guests }}</p>
                            <p style="margin: 0 0 20px 0;"><strong>Original Amount:</strong>&nbsp;{{ $setting->currency_symbol }} {{ number_format($booking->total_amount, 2) }}</p>

                            <!-- Refund Information -->
                            @if(!empty($booking->payment_reference) && $booking->payment_status == 'paid')
                                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse; margin: 20px 0; border: 1px solid #e9ecef; border-radius: 6px;">
                                    <tr>
                                        <td colspan="2" style="background-color: #f8f9fa; padding: 15px; border-bottom: 1px solid #e9ecef;">
                                            <h4 style="margin: 0; color: #008ea2; font-size: 16px;">Refund Information</h4>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 12px 15px; border-bottom: 1px solid #e9ecef;">Original Payment:</td>
                                        <td style="padding: 12px 15px; text-align: right; border-bottom: 1px solid #e9ecef;">{{ $setting->currency_symbol }} {{ number_format($booking->total_amount, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 15px; font-weight: bold; background-color: #f8f9fa;">Total Refund:</td>
                                        <td style="padding: 15px; text-align: right; font-weight: bold; background-color: #f8f9fa; color: #28a745;">
                                            {{ $setting->currency_symbol }} {{ number_format($booking->total_amount, 2) }}
                                        </td>
                                    </tr>
                                </table>
                            @endif

                            <!-- Refund Processing Information -->
                            @if(!empty($booking->payment_reference) && $booking->payment_status == 'paid')
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse; margin: 20px 0;">
                                <tr>
                                    <td style="background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 6px; padding: 20px;">
                                        <h4 style="margin: 0 0 10px 0; color: #155724; font-size: 16px;">💳 Refund Processing</h4>
                                        <ul style="margin: 0; padding-left: 20px; color: #155724;">
                                            <li style="margin-bottom: 8px;">Your refund of <strong>{{ $setting->currency_symbol }} {{ number_format($booking->total_amount, 2) }}</strong> is being processed</li>
                                            <li style="margin-bottom: 8px;">Refunds typically take 5-10 business days to appear on your statement</li>
                                            <li style="margin-bottom: 8px;">You will receive a separate email confirmation when the refund is completed</li>
                                            @if(isset($booking->payment_method))
                                            <li style="margin-bottom: 0;">Refund will be credited back to your original payment method: {{ $booking->payment_method }}</li>
                                            @else
                                            <li style="margin-bottom: 0;">Refund will be credited back to your original payment method</li>
                                            @endif
                                        </ul>
                                    </td>
                                </tr>
                            </table>
                            @endif

                            <!-- No Refund Notice -->
                            @if(empty($booking->payment_reference) || $booking->payment_status == 'refund')
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse; margin: 20px 0;">
                                <tr>
                                    <td style="background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 6px; padding: 20px;">
                                        <h4 style="margin: 0 0 10px 0; color: #721c24; font-size: 16px;">❌ No Refund</h4>
                                        @if($booking->payment_status == 'refund')
                                            <p style="margin: 0; color: #721c24;">
                                                We had already processed your refund on {{ $booking->refund_date->format('l, F j, Y \a\t g:i A') }}.
                                            </p>
                                        @else
                                            <p style="margin: 0; color: #721c24;">
                                                Based on our cancellation policy and the timing of your cancellation, no refund is applicable for this booking. 
                                                The full amount has been retained as per the terms and conditions agreed upon at the time of booking.
                                            </p>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                            @endif

                            <!-- Important Information Box -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse; margin: 20px 0;">
                                <tr>
                                    <td style="background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 6px; padding: 20px;">
                                        <h4 style="margin: 0 0 10px 0; color: #856404; font-size: 16px;">📋 Important Notes</h4>
                                        <ul style="margin: 0; padding-left: 20px; color: #856404;">
                                            <li style="margin-bottom: 8px;">This cancellation is final and cannot be reversed</li>
                                            <li style="margin-bottom: 8px;">Your room has been released and is now available for other guests</li>
                                            @if(!empty($booking->payment_reference))
                                            <li style="margin-bottom: 8px;">Keep this email as proof of your refund request</li>
                                            @endif
                                            <li style="margin-bottom: 0;">For any questions, please contact our customer service team</li>
                                        </ul>
                                    </td>
                                </tr>
                            </table>

                            <!-- Future Booking Incentive -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse; margin: 20px 0;">
                                <tr>
                                    <td style="background-color: #e7f3ff; border: 1px solid #b3d9ff; border-radius: 6px; padding: 20px; text-align: center;">
                                        <h4 style="margin: 0 0 10px 0; color: #0056b3; font-size: 18px;">We Hope to Welcome You Soon! 🏨</h4>
                                        <p style="margin: 0 0 15px 0; color: #0056b3;">
                                            We're sorry your plans changed, but we'd love to host you in the future.
                                        </p>
                                        <a href="{{ config('app.frontend_url') }}" target="_blank" style="display: inline-block; padding: 12px 24px; background-color: #008ea2; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 10px;">
                                            Explore Our Rooms
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 30px 0 15px 0;">If you have any questions about this cancellation or need assistance with a future booking, please don't hesitate to contact us at <a href="mailto:{{ config('mail.from.address') }}">{{ config('mail.from.address') }}</a>.</p>

                            <p style="margin: 0 0 30px 0;">Thank you for considering {{ config('app.name') }}, and we hope to serve you in the future.</p>
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
                                For assistance, contact us at <a href="mailto:{{ config('mail.from.address') }}" style="color: #008ea2;">{{ config('mail.from.address') }}</a>
                            </p>
                            
                        </td>
                    </tr>
                    
                </table>
                
            </td>
        </tr>
    </table>
    
</body>
</html>