<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Allow Search Engines
    |--------------------------------------------------------------------------
    |
    | Set SEO_INDEXABLE=false on a development or staging site that mirrors the
    | real company, so Google and Bing do not index its pages (or the sample
    | content on them) next to the production site. Every page then carries a
    | "noindex, nofollow" robots meta tag and X-Robots-Tag header. Production
    | leaves it unset.
    |
    */

    'indexable' => (bool) env('SEO_INDEXABLE', true),

];
