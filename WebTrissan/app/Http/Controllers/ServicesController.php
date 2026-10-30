<?php

namespace App\Http\Controllers;

use App\Models\GlobalSetting;
use App\Models\Service;
use Illuminate\Contracts\View\View;

class ServicesController extends Controller
{
    /**
     * Show the landing page of one service with its CMS content and related works.
     *
     * A service without enough body text, process steps and FAQ still renders for visitors who
     * follow a direct link, but is marked noindex so a thin page never reaches the search index.
     */
    public function show(Service $service): View
    {
        $globalSettings = GlobalSetting::current();
        $works = $service->works()->with('service')->get();
        $relatedServices = Service::query()
            ->where('service_cluster_id', $service->service_cluster_id)
            ->whereKeyNot($service->getKey())
            ->orderBy('sort_order')
            ->get();
        $isIndexable = $service->hasLandingContent();

        return view('service', compact(
            'globalSettings',
            'service',
            'works',
            'relatedServices',
            'isIndexable'
        ));
    }
}
