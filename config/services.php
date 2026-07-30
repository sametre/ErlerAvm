<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

    'paytr' => [
        'merchant_id' => env('PAYTR_MERCHANT_ID'),
        'merchant_key' => env('PAYTR_MERCHANT_KEY'),
        'merchant_salt' => env('PAYTR_MERCHANT_SALT'),
        'iframe_token_url' => env('PAYTR_IFRAME_TOKEN_URL', 'https://www.paytr.com/odeme/api/get-token'),
        'iframe_checkout_url' => env('PAYTR_IFRAME_CHECKOUT_URL', 'https://www.paytr.com/odeme/guvenli'),
        'ok_url' => env('PAYTR_OK_URL'),
        'fail_url' => env('PAYTR_FAIL_URL'),
        'test_mode' => (bool) env('PAYTR_TEST_MODE', true),
        'debug_on' => (bool) env('PAYTR_DEBUG_ON', false),
        'currency' => env('PAYTR_CURRENCY', 'TL'),
        'lang' => env('PAYTR_LANG', 'tr'),
        'timeout_limit' => (int) env('PAYTR_TIMEOUT_LIMIT', 30),
        'no_installment' => (int) env('PAYTR_NO_INSTALLMENT', 0),
        'max_installment' => (int) env('PAYTR_MAX_INSTALLMENT', 0),
    ],

    'akbank' => [
        'enabled' => (bool) env('AKBANK_ENABLED', false),
        'environment' => env('AKBANK_ENVIRONMENT', 'test'),
        'merchant_id' => env('AKBANK_MERCHANT_ID'),
        'terminal_id' => env('AKBANK_TERMINAL_ID'),
        'username' => env('AKBANK_USERNAME'),
        'password' => env('AKBANK_PASSWORD'),
        'secret_key' => env('AKBANK_SECRET_KEY'),
        'payment_model' => env('AKBANK_PAYMENT_MODEL', '3D_PAY'),
        'transaction_type' => env('AKBANK_TRANSACTION_TYPE', 'Auth'),
        'gateway_url' => env('AKBANK_GATEWAY_URL') ?: env('AKBANK_TEST_URL'),
        'production_url' => env('AKBANK_PRODUCTION_URL'),
        'success_url' => env('AKBANK_SUCCESS_URL'),
        'fail_url' => env('AKBANK_FAIL_URL'),
        'currency' => env('AKBANK_CURRENCY', 'TRY'),
        'currency_code' => env('AKBANK_CURRENCY_CODE', '949'),
        'language' => env('AKBANK_LANGUAGE', 'tr'),
        'timeout_seconds' => (int) env('AKBANK_TIMEOUT_SECONDS', 20),
        'accepted_3d_statuses' => array_filter(explode(',', (string) env('AKBANK_ACCEPTED_3D_STATUSES', '1'))),
        'request_hash_fields' => ['clientid', 'oid', 'amount', 'okUrl', 'failUrl', 'islemtipi', 'taksit', 'rnd'],
        'card_fields' => [
            'holder_name' => 'cardHolderName',
            'number' => 'pan',
            'expiry_month' => 'Ecom_Payment_Card_ExpDate_Month',
            'expiry_year' => 'Ecom_Payment_Card_ExpDate_Year',
            'cvv' => 'cv2',
            'card_type' => 'cardType',
        ],
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-1.5-flash'),
        'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
    ],

];
