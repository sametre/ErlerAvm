<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class PaymentReturnController extends Controller
{
    public function success(): Response
    {
        return Inertia::render('payments/Return', [
            'status' => 'pending-confirmation',
            'title' => 'Ödeme sonucu kontrol ediliyor',
            'message' => 'Banka ödeme sonucunu doğruladıktan sonra sipariş durumunuz kesinleşir. Sipariş detayından son durumu kontrol edebilirsiniz.',
        ]);
    }

    public function fail(): Response
    {
        return Inertia::render('payments/Return', [
            'status' => 'failed',
            'title' => 'Ödeme tamamlanamadı',
            'message' => 'Ödeme işlemi tamamlanmadı. Kartınızdan tahsilat yapıldıysa sipariş durumu banka bildirimi ile tekrar kontrol edilir.',
        ]);
    }
}
