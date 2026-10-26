<?php

namespace App\Http\Controllers;

use App\Models\GlobalSetting;
use App\Models\PageSetting;
use App\Models\Service;
use App\Models\Work;
use Illuminate\Contracts\View\View;

class WorksController extends Controller
{
    /**
     * Show the works/projects catalog with dynamic CMS data.
     *
     * Filtering (`?category={service slug}`), sorting, search, and pagination run in the browser,
     * so every work is sent to the view.
     */
    public function index(): View
    {
        $globalSettings = GlobalSetting::current();
        $pageSettings = PageSetting::forPage('works');
        $services = Service::orderBy('sort_order')->get();
        $works = Work::with('service')->orderBy('sort_order')->get();

        return view('works', compact(
            'globalSettings',
            'pageSettings',
            'services',
            'works'
        ));
    }
}
