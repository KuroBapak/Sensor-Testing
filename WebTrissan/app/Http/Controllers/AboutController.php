<?php

namespace App\Http\Controllers;

use App\Models\AboutCompany;
use App\Models\GlobalSetting;
use App\Models\PageSetting;
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

        return view('abtus', compact(
            'globalSettings',
            'pageSettings',
            'aboutCompany'
        ));
    }
}
