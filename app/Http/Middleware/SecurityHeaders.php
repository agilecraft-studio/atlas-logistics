<?php

namespace App\Http\Middleware;

use Closure;

/**
 * Adds standard browser-hardening headers to every web response.
 * (A strict Content-Security-Policy needs the legacy inline scripts refactored first; not enabled here.)
 */
class SecurityHeaders
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if (!method_exists($response, 'header') && !isset($response->headers)) {
            return $response;
        }

        $h = $response->headers;
        $h->set('X-Frame-Options', 'SAMEORIGIN');                       // blocks clickjacking
        $h->set('X-Content-Type-Options', 'nosniff');                   // blocks MIME sniffing of uploads
        $h->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $h->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(self), payment=()');
        $h->set('Cross-Origin-Opener-Policy', 'same-origin-allow-popups');
        if ($request->isSecure()) {
            $h->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Do not let the browser keep logged-in HTML pages in its back/forward cache after logout.
        $type = (string) $h->get('Content-Type');
        if ($request->user() && stripos($type, 'text/html') !== false) {
            $h->set('Cache-Control', 'no-store, private');
        }

        header_remove('X-Powered-By');

        return $response;
    }
}
