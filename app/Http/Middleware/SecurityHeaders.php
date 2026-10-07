<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Adds standard security response headers to every response.
     *
     * The Content-Security-Policy below is deliberately permissive on
     * 'unsafe-inline'/'unsafe-eval' for scripts and styles because Livewire
     * and Filament rely on inline <script> blocks and inline style attributes
     * at runtime. Tightening this to a strict nonce-based policy is possible
     * but would require auditing every Livewire/Filament view — not worth the
     * risk right now. This still blocks the most common attack classes:
     * framing/clickjacking, MIME sniffing, and loading remote script/object
     * sources you haven't explicitly allowed.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        // Clickjacking protection: don't allow this site to be framed by anyone.
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Stop browsers guessing content types (helps prevent some XSS vectors
        // via uploaded files being served as something they're not).
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Don't leak the full referring URL (which may contain tokens/IDs) to
        // third-party destinations; still send it for same-origin navigation.
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Disable browser features this app doesn't use, reducing attack surface
        // if a malicious script ever got injected.
        $response->headers->set(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=(), payment=(self)'
        );

        // Content-Security-Policy: restrict where scripts/styles/images/fonts/
        // frames/connections can load from. Adjust the stripe.com entries if you
        // add other payment providers later, and add your CDN domain here if you
        // move Filament/Livewire assets off this origin.
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://js.stripe.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "font-src 'self' https://fonts.gstatic.com data:",
            "img-src 'self' data: blob: https:",
            "connect-src 'self' https://api.stripe.com",
            "frame-src 'self' https://js.stripe.com https://hooks.stripe.com https://www.youtube.com https://player.vimeo.com",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ]);
        $response->headers->set('Content-Security-Policy', $csp);

        // HSTS: only sent over an already-secure connection, and only once you
        // have a real HTTPS certificate in production. Sending this over plain
        // HTTP (e.g. local XAMPP testing) is a no-op since the header is skipped.
        if ($request->isSecure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            );
        }

        // Legacy header, superseded by CSP's frame-src/object-src above, but
        // still respected by some older browsers.
        $response->headers->set('X-XSS-Protection', '0');

        return $response;
    }
}
