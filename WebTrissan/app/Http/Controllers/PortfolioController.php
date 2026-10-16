<?php

namespace App\Http\Controllers;

use App\Models\GlobalSetting;
use App\Models\PageSetting;
use App\Models\PortfolioCategory;
use App\Models\PortfolioItem;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PortfolioController extends Controller
{
    /**
     * Show the portfolio catalog with dynamic CMS data.
     */
    public function index(Request $request): View
    {
        $globalSettings = GlobalSetting::current();
        $pageSettings = PageSetting::forPage('portfolio');
        $portfolioCategories = PortfolioCategory::orderBy('sort_order')->get();

        $query = PortfolioItem::with('portfolioCategory')->orderBy('sort_order');

        if ($request->filled('category')) {
            $query->where('portfolio_category_id', $request->query('category'));
        }

        $portfolioItems = $query->paginate(12)->withQueryString();

        return view('portofolio', compact(
            'globalSettings',
            'pageSettings',
            'portfolioCategories',
            'portfolioItems'
        ));
    }
}
