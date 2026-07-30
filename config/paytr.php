<?php

return [
    'merchant_id' => env('PAYTR_MERCHANT_ID', 'XXXXXX'),
    'merchant_key' => env('PAYTR_MERCHANT_KEY', 'XXXXXXXXXXXXXXXX'),
    'merchant_salt' => env('PAYTR_MERCHANT_SALT', 'XXXXXXXXXXXXXXXX'),
    'timeout_limit' => env('PAYTR_TIMEOUT_LIMIT', 30),
    'debug_on' => env('PAYTR_DEBUG_ON', 1),
    'test_mode' => env('PAYTR_TEST_MODE', 1),
    'max_installment' => env('PAYTR_MAX_INSTALLMENT', 0),
    'no_installment' => env('PAYTR_NO_INSTALLMENT', 0),
];
