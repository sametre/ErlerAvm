<?php

use App\Http\Middleware\AuthenticateApiClient;
use App\Http\Middleware\CheckMaintenanceMode;
use App\Http\Middleware\EnsureAdminAccess;
use App\Http\Middleware\EnsureAdminPinVerified;
use App\Http\Middleware\EnsureApiPermission;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\TrackStorefrontVisitor;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn (Request $request): string => $request->getHost() === config('api.domain')
            ? (string) config('api.url')
            : route('login'));

        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);
        $middleware->validateCsrfTokens(except: [
            'paytr/callback',
            'akbank/callback/*',
        ]);

        $middleware->web(append: [
            CheckMaintenanceMode::class,
            HandleAppearance::class,
            HandleInertiaRequests::class,
            TrackStorefrontVisitor::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'admin.access' => EnsureAdminAccess::class,
            'admin.pin' => EnsureAdminPinVerified::class,
            'abilities' => CheckAbilities::class,
            'ability' => CheckForAnyAbility::class,
            'api.permission' => EnsureApiPermission::class,
            'api.client' => AuthenticateApiClient::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->respond(function (Response $response, Throwable $exception, Request $request): Response {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $response;
            }

            $status = $response->getStatusCode();
            $renderableStatuses = [400, 401, 403, 404, 405, 419, 422, 429, 500, 503];

            if (! in_array($status, $renderableStatuses, true)) {
                return $response;
            }

            if ($status >= 500 && config('app.debug')) {
                return $response;
            }

            return Inertia::render('Error', [
                'status' => $status,
                'requestId' => $request->headers->get('X-Request-Id'),
            ])->toResponse($request)->setStatusCode($status);
        });
    })->create();
