<?php

namespace App\Http\Controllers\Storefront;

use App\Actions\Payments\StartAkbankPayment;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\CheckoutRequest;
use App\Models\User;
use App\Services\Storefront\CartService;
use App\Services\Storefront\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class CheckoutController extends Controller
{
    public function show(CartService $cart): Response
    {
        return Inertia::render('Storefront/Checkout', [
            'cart' => $cart->summary(),
        ]);
    }

    public function store(CheckoutRequest $request, CheckoutService $checkout, StartAkbankPayment $payment): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        try {
            $order = $checkout->createOrder($user, $request->validated());
        } catch (RuntimeException $exception) {
            return back()->with('toast', [
                'type' => 'error',
                'message' => $exception->getMessage(),
            ]);
        }

        try {
            $attempt = $payment->handle($order, $request, $user);

            return redirect()
                ->route('payments.akbank.attempt', ['paymentAttempt' => $attempt->idempotency_key])
                ->with('toast', [
                    'type' => 'success',
                    'message' => 'Siparişiniz oluşturuldu. Akbank 3D Secure ödeme adımını tamamlayabilirsiniz.',
                ]);
        } catch (RuntimeException $exception) {
            logger()->warning('Akbank payment could not be started after checkout.', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'reason' => $exception->getMessage(),
            ]);
        }

        return redirect()
            ->route('account.orders.show', ['public_id' => $order->public_id])
            ->with('toast', [
                'type' => 'warning',
                'message' => 'Siparişiniz oluşturuldu ancak Akbank ödeme adımı başlatılamadı. Lütfen sipariş detayından tekrar deneyin.',
            ]);
    }
}
