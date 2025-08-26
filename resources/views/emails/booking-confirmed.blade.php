<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>{{ config('app.name') }} - Booking Confirmed</title>
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
                            
                            <h2 style="margin: 0 0 20px 0; font-size: 28px; color: #008ea2; font-weight: bold;">Booking Confirmed.</h2>
                            
                            <p style="margin: 0 0 20px 0;">Hello {{ $guest->name ?? 'there' }},</p>
                            
                            <p style="margin: 0 0 30px 0;">Your reservation at {{ config('app.name') }} has been confirmed!</p>

                            <p style="margin: 0 0 7px 0;"><strong>Reservation Code:</strong>&nbsp;{{ $booking->code }}</p>
                            <p style="margin: 0 0 7px 0;"><strong>Check-in:</strong>&nbsp;{{ $booking->check_in->format('l, F j, Y') }}</p>
                            <p style="margin: 0 0 7px 0;"><strong>Check-out:</strong>&nbsp;{{ $booking->check_out->format('l, F j, Y') }}</p>
                            <p style="margin: 0 0 7px 0;"><strong>Room:</strong>&nbsp;{{ $booking->room->number }} - {{ $booking->room->type }}</p>
                            <p style="margin: 0 0 7px 0;"><strong>Guests:</strong>&nbsp;{{ $booking->guests }}</p>
                            <p style="margin: 0 0 20px 0;"><strong>Total Amount:</strong>&nbsp;₱&nbsp;{{ number_format($booking->total_amount,2) }}</p>
                            
                            <!-- Amenities -->
                            @if(!empty($booking?->room?->amenities))
                                <h3 style="margin: 30px 0 15px 0; font-size: 20px; color: #008ea2;">Hotel Amenities</h3>
                                @foreach ($booking?->room?->amenities as $amenity)
                                    <p style="margin: 0 0 7px 0;">- {{ $amenity }}</p>
                                @endforeach
                            @endif

                            <!-- Important Information Box -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse; margin: 20px 0;">
                                <tr>
                                    <td style="background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 6px; padding: 20px;">
                                        <h4 style="margin: 0 0 10px 0; color: #856404; font-size: 16px;">📋 Important Information</h4>
                                        <ul style="margin: 0; padding-left: 20px; color: #856404;">
                                            <li style="margin-bottom: 7px;">Please present a valid ID upon check-in</li>
                                            <li style="margin-bottom: 7px;">Check-in time: {{ $booking->check_in->format('g:i A') }}</li>
                                            <li style="margin-bottom: 7px;">Check-out time: {{ $booking->check_out->format('g:i A') }}</li>
                                        </ul>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 0 0 30px 0;">If you have any questions or need to modify your reservation, please contact us at <a href="mailto:{{ config('mail.from.address') }}">{{ config('mail.from.address') }}</a>.</p>

                            <p style="margin: 0 0 30px 0;">Thank you for choosing {{ config('app.name') }}!</p>
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
                            
                        </td>
                    </tr>
                    
                </table>
                
            </td>
        </tr>
    </table>
    
</body>
</html>