<?php

namespace App\Http\Controllers;

use App\Models\AboutCompany;
use App\Models\GlobalSetting;
use App\Models\PageSetting;
use App\Models\Personnel;
use App\Models\SpecialtyCluster;
use Illuminate\Contracts\View\View;

class AboutController extends Controller
{
    /**
     * Show the about company page with dynamic CMS data.
     */
    public function index(): View
    {
        $globalSettings = GlobalSetting::current();
        $pageSettings = PageSetting::forPage('about');
        $aboutCompany = AboutCompany::current();
        $specialtyClusters = SpecialtyCluster::with(['personnel' => function ($query) {
            $query->where('status', true)->with('skills')->orderBy('sort_order');
        }])->orderBy('sort_order')->get();
        $personnel = Personnel::with(['specialtyCluster', 'skills'])->where('status', true)->orderBy('sort_order')->get();

        return view('abtus', compact(
            'globalSettings',
            'pageSettings',
            'aboutCompany',
            'specialtyClusters',
            'personnel'
        ));
    }
}
