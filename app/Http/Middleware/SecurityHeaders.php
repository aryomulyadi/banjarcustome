<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $nonce = bin2hex(random_bytes(16));
        $request->attributes->set('csp_nonce', $nonce);

        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        $production = app()->environment('production');

        // Chrome menolak sumber IPv6 bracket (http://[::1]:5199) dan port wildcard
        // ("*") — keduanya dibuang sebagai malformed — jadi cukup localhost/127.0.0.1
        // dengan port pasti (vite di-pin 127.0.0.1:5173).
        $loopback = $production ? '' : ' http://localhost:5173 http://127.0.0.1:5173';
        $connectDev = $production ? '' : ' ws://localhost:5173 ws://127.0.0.1:5173 http://localhost:5173 http://127.0.0.1:5173';

        $directives = [
            "default-src 'self'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
            "object-src 'none'",
            "script-src 'self' 'unsafe-eval' 'nonce-{$nonce}'{$loopback}",
            "style-src 'self' 'unsafe-inline'{$loopback}",
            "img-src 'self' data: https:",
            "font-src 'self'",
            "connect-src 'self'{$connectDev}",
            "frame-src 'self' https://www.google.com https://maps.google.com",
        ];

        if ($production) {
            $directives[] = 'upgrade-insecure-requests';
        }

        $response->headers->set('Content-Security-Policy', implode('; ', $directives));

        if ($production) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
