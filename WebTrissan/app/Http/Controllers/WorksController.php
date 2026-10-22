<?php

namespace App\Http\Controllers;

use App\Models\GlobalSetting;
use App\Models\PageSetting;
use App\Models\Service;
use App\Models\Work;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class WorksController extends Controller
{
    /**
     * Show the works/projects catalog with dynamic CMS data.
     */
    public function index(Request $request): View
    {
        $globalSettings = GlobalSetting::current();
        $pageSettings = PageSetting::forPage('works');
        $services = Service::orderBy('sort_order')->get();

        $query = Work::with('service')->orderBy('sort_order');

        if ($request->filled('service')) {
            $query->where('service_id', $request->query('service'));
        }

        $works = $query->paginate(12)->withQueryString();
        $featuredWorks = Work::with('service')->where('is_featured', true)->orderBy('sort_order')->get();

        return view('works', compact(
            'globalSettings',
            'pageSettings',
            'services',
            'works',
            'featuredWorks'
        ));
    }
}
