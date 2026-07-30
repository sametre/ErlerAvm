<?php

namespace App\Http\Middleware;

use App\Models\VisitorSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

class TrackStorefrontVisitor
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (
            $request->isMethod('GET')
            && ! $request->expectsJson()
            && ! $request->is('admin*')
            && ! $request->is('yonetim*')
            && $request->getHost() !== config('admin.domain')
            && Schema::hasTable('visitor_sessions')
        ) {
            $this->track($request, $response);
        }

        return $response;
    }

    private function track(Request $request, Response $response): void
    {
        $sessionId = $request->session()->getId();

        if ($sessionId === '') {
            return;
        }

        $key = (string) config('app.key');
        $visitorUid = (string) $request->cookies->get('erler_uid', '');

        if ($visitorUid === '' || ! Str::isUuid($visitorUid)) {
            $visitorUid = (string) Str::uuid();

            $response->headers->setCookie(
                Cookie::create('erler_uid')
                    ->withValue($visitorUid)
                    ->withExpires(now()->addDays(400))
                    ->withPath('/')
                    ->withSecure($request->isSecure())
                    ->withHttpOnly(true)
                    ->withSameSite('lax')
            );
        }

        VisitorSession::query()->updateOrCreate(
            ['session_hash' => hash_hmac('sha256', $sessionId, $key)],
            [
                'visitor_uid_hash' => hash_hmac('sha256', $visitorUid, $key),
                'user_id' => $request->user()?->id,
                'ip_hash' => hash_hmac('sha256', (string) $request->ip(), $key),
                'user_agent_hash' => hash_hmac('sha256', (string) $request->userAgent(), $key),
                'last_path' => '/'.ltrim($request->path(), '/'),
                'last_seen_at' => now(),
            ],
        );
    }
}
