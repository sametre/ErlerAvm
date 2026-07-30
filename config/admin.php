<?php

$appUrl = (string) env('APP_URL', 'http://erleravm.test');
$appHost = parse_url($appUrl, PHP_URL_HOST) ?: 'erleravm.test';
$appScheme = parse_url($appUrl, PHP_URL_SCHEME) ?: 'http';
$adminDomain = (string) env('ADMIN_DOMAIN', 'dashboard.'.$appHost);

return [
    'domain' => $adminDomain,
    'url' => (string) env('ADMIN_URL', $appScheme.'://'.$adminDomain),
    'pin_session_key' => 'admin_pin_verified',
    'pin_max_attempts' => (int) env('ADMIN_PIN_MAX_ATTEMPTS', 5),
    'pin_decay_seconds' => (int) env('ADMIN_PIN_DECAY_SECONDS', 600),
];
