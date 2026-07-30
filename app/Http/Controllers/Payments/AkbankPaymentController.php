<?php

namespace App\Http\Controllers\Payments;

use App\Actions\Payments\StartAkbankPayment;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentAttempt;
use App\Models\User;
use App\Services\Akbank\AkbankConfig;
use App\Services\Akbank\AkbankPaymentFormBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class AkbankPaymentController extends Controller
{
    public function show(Order $order, Request $request, StartAkbankPayment $payment): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User && $order->user_id === $user->id, 404);

        try {
            $attempt = $payment->handle($order, $request, $user);
        } catch (RuntimeException $exception) {
            return back()->with('toast', [
                'type' => 'error',
                'message' => $exception->getMessage(),
            ]);
        }

        return redirect()->route('payments.akbank.attempt', ['paymentAttempt' => $attempt->idempotency_key]);
    }

    public function attempt(PaymentAttempt $paymentAttempt, Request $request, AkbankPaymentFormBuilder $formBuilder): Response
    {
        abort_unless($paymentAttempt->provider === 'akbank', 404);
        abort_unless($request->user() instanceof User && $paymentAttempt->user_id === $request->user()->id, 404);
        abort_if($paymentAttempt->status === 'paid', 409, 'Bu ödeme zaten tamamlandı.');

        $paymentAttempt->load('order.items:id,order_id,name,quantity,line_total');
        $order = $paymentAttempt->order;
        abort_unless($order instanceof Order, 404);

        $config = AkbankConfig::fromConfig();
        $config->assertReady();

        return Inertia::render('payments/AkbankPay', [
            'order' => [
                'order_number' => $order->order_number,
                'grand_total' => (string) $order->grand_total,
                'currency' => $order->currency,
                'items' => $order->items->map(fn ($item) => [
                    'name' => $item->name,
                    'quantity' => $item->quantity,
                    'line_total' => (string) $item->line_total,
                ]),
            ],
            'payment' => [
                'merchant_oid' => $paymentAttempt->merchant_oid,
                'amount_minor' => $paymentAttempt->payment_amount,
                'environment' => $config->environment,
                'payment_model' => $config->paymentModel,
            ],
            'akbankForm' => $formBuilder->form($paymentAttempt),
            'cardFields' => $config->cardFields,
        ]);
    }
}
