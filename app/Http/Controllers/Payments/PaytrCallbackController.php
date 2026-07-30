<?php

namespace App\Http\Controllers\Payments;

use App\Actions\Payments\HandlePaytrCallback;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payments\PaytrCallbackRequest;
use Illuminate\Http\Response;

class PaytrCallbackController extends Controller
{
    public function __invoke(PaytrCallbackRequest $request, HandlePaytrCallback $handler): Response
    {
        $handler->handle($request->validated());

        return response('OK', 200)->header('Content-Type', 'text/plain');
    }
}
