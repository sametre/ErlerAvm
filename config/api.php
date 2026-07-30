<?php

$appUrl = (string) env('APP_URL', 'http://erleravm.test');
$appHost = parse_url($appUrl, PHP_URL_HOST) ?: 'erleravm.test';
$appScheme = parse_url($appUrl, PHP_URL_SCHEME) ?: 'http';
$apiDomain = (string) env('API_DOMAIN', 'api.'.$appHost);

return [
    'domain' => $apiDomain,
    'url' => (string) env('API_URL', $appScheme.'://'.$apiDomain),

    'abilities' => [
        'catalog:read' => 'Ürün, kategori, marka ve kampanya verilerini okuma',
    ],
];
