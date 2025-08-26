<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'security' => [
        'session_timeouts' => [
            '15' => 15,
            '30' => 30,
            '60' => 60,
            '120' => 120,
        ],

        'password_policies' => [
            // Basic: minimum 8 chars
            'basic' => 'required|string|min:8',

            // Strong: minimum 10 chars, at least one uppercase, one lowercase, one number
            'strong' => [
                'required',
                'string',
                'min:10',
                'regex:/[A-Z]/',    // at least one uppercase
                'regex:/[a-z]/',    // at least one lowercase
                'regex:/[0-9]/',    // at least one digit
            ],

            // Very Strong: minimum 12 chars, at least one uppercase, one lowercase, one number, one special char
            'very_strong' => [
                'required',
                'string',
                'min:12',
                'regex:/[A-Z]/',        // at least one uppercase
                'regex:/[a-z]/',        // at least one lowercase
                'regex:/[0-9]/',        // at least one digit
                'regex:/[~#@$!%*?&]/',    // at least one special character
            ],
        ]
    ],

    'currencies' => [
        'PHP' => 'PHP - Philippine Peso',
        'USD' => 'USD - US Dollar',
        'AUD' => 'AUD - Australian Dollar',
        'JPY' => 'JPY - Japanese Yen'
    ],

];
