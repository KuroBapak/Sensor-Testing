<?php

namespace App\Http\Controllers;

use App\Models\GlobalSetting;
use App\Models\PageSetting;
use App\Models\Service;
use App\Models\ServiceCluster;
use App\Models\Work;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    /**
     * Show the landing page with dynamic CMS data.
     */
    public function index(): View
    {
        $globalSettings = GlobalSetting::current();
        $pageSettings = PageSetting::forPage('landing');
        $serviceClusters = ServiceCluster::with(['services' => function ($query) {
            $query->orderBy('sort_order');
        }])->orderBy('sort_order')->get();
        $services = Service::with('serviceCluster')->orderBy('sort_order')->get();
        $featuredWorks = Work::with('service')->where('is_featured', true)->orderBy('featured_sort_order')->orderBy('sort_order')->take(6)->get();

        return view('home', compact(
            'globalSettings',
            'pageSettings',
            'serviceClusters',
            'services',
            'featuredWorks'
        ));
    }
}
