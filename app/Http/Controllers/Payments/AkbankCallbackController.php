<?php

namespace App\Http\Controllers\Payments;

use App\Actions\Payments\HandleAkbankCallback;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payments\AkbankCallbackRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class AkbankCallbackController extends Controller
{
    public function __invoke(AkbankCallbackRequest $request, string $result, HandleAkbankCallback $handler): RedirectResponse
    {
        $attempt = $handler->handle($request->paymentPayload(), $result);
        $order = $attempt->order;

        if ($order !== null && Auth::id() === $order->user_id) {
            return redirect()->route('account.orders.show', ['public_id' => $order->public_id]);
        }

        return redirect()->route($attempt->status === 'paid' ? 'payments.success' : 'payments.fail');
    }
}
