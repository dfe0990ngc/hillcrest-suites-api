<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>{{ config('app.name') }} - Password Reset</title>
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
                            
                            <h2 style="margin: 0 0 20px 0; font-size: 28px; color: #008ea2; font-weight: bold;">Reset your password!</h2>
                            
                            <p style="margin: 0 0 20px 0;">Hello {{ $user->name ?? 'there' }},</p>
                            
                            <p style="margin: 0 0 30px 0;">Seems you'd like to reset your password in our {{ config('app.name') }} platform. To continue, please click the button below.</p>
                            
                            <!-- Button -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse; margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
                                            <tr>
                                                <td align="center" style="border-radius: 6px; background-color: #008ea2;">
                                                    <a href="{{ $url }}" target="_blank" style="display: inline-block; padding: 16px 36px; font-family: Arial, sans-serif; font-size: 16px; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold;">
                                                        Reset Password
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Security Note -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse; margin: 25px 0; background-color: #f0fdff; border-left: 4px solid #008ea2;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <p style="margin: 0; font-size: 14px; color: #333333;">
                                            <strong>🔐 Security Note:</strong><br>
                                            This password reset link will expire in {{ $expireMinutes }} minutes for your security.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            
                            <h3 style="margin: 30px 0 15px 0; font-size: 20px; color: #008ea2;">Need Help?</h3>
                            
                            <p style="margin: 0 0 15px 0;">If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:</p>
                            
                            <p style="margin: 0 0 25px 0; word-break: break-all; background-color: #f8f9fa; padding: 10px; border-radius: 4px; font-size: 14px; color: #666;">
                                {{ $url }}
                            </p>
                            
                            <p style="margin: 0 0 25px 0;">If you did not requested this password reset, no further action is required.</p>
                            
                            <p style="margin: 0;">
                                Thanks,<br>
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