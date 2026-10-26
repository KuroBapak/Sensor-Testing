<?php

namespace App\Http\Controllers;

use App\Models\GlobalSetting;
use App\Models\PageSetting;
use App\Models\Personnel;
use App\Models\PortfolioCategory;
use App\Models\PortfolioItem;
use App\Models\SpecialtyCluster;
use Illuminate\Contracts\View\View;

class PortfolioController extends Controller
{
    /**
     * Show the portfolio catalog and personnel directory with dynamic CMS data.
     *
     * Category filtering, search, sorting, and pagination run in the browser,
     * so every item and active personnel record is sent to the view.
     */
    public function index(): View
    {
        $globalSettings = GlobalSetting::current();
        $pageSettings = PageSetting::forPage('portfolio');
        $portfolioCategories = PortfolioCategory::withCount('portfolioItems')->orderBy('sort_order')->get();
        $portfolioItems = PortfolioItem::with('portfolioCategory')->orderBy('sort_order')->get();
        $specialtyClusters = SpecialtyCluster::orderBy('sort_order')->get();
        $personnel = Personnel::with(['specialtyCluster', 'skills'])->where('status', true)->orderBy('sort_order')->get();

        return view('portofolio', compact(
            'globalSettings',
            'pageSettings',
            'portfolioCategories',
            'portfolioItems',
            'specialtyClusters',
            'personnel'
        ));
    }
}
