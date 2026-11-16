<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HideFromSearchEngines
{
    /**
     * Ask search engines not to index any response while SEO_INDEXABLE is false.
     *
     * The header covers everything the page markup cannot: images, PDFs and the XML sitemap. Crawling stays allowed
     * in robots.txt on purpose, because a crawler that may not fetch a page never sees its noindex instruction.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! config('seo.indexable')) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        }

        return $response;
    }
}
