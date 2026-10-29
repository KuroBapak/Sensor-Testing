<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectToCanonicalUrl
{
    /**
     * Permanently redirect duplicate URL spellings of a page to the one canonical form.
     *
     * "/index.php" and "/index.php/about" both serve the site, and "/about/" serves the same page as "/about";
     * search engines would index each spelling separately.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            return $next($request);
        }

        // Not parse_url(): it reads a request line like "//host/" as a host with an empty path.
        $uri = $request->getRequestUri();
        $path = strstr($uri, '?', true) ?: $uri;

        if (preg_match('#^/index\.php(/.*)?$#', $path, $matches) === 1) {
            $target = $matches[1] ?? '/';
        } elseif ($path !== '/' && str_ends_with($path, '/')) {
            $target = rtrim($path, '/');
        } else {
            return $next($request);
        }

        // Collapse repeated slashes so a path like "/index.php//host" can never become a protocol-relative redirect.
        $target = '/'.ltrim((string) preg_replace('#/{2,}#', '/', $target), '/');

        $query = $request->getQueryString();

        // Built from the host, not url(): a request for "/index.php" has "/index.php" as its base url, so url('/') would
        // point right back at "/index.php" and loop.
        return redirect()->away($request->getSchemeAndHttpHost().($query === null ? $target : $target.'?'.$query), 301);
    }
}
