<?php

namespace App\Http\Controllers;

use App\Models\CertificationCategory;
use App\Models\CorporateAccreditation;
use App\Models\GlobalSetting;
use App\Models\PageSetting;
use App\Models\ProductCertification;
use Illuminate\Contracts\View\View;

class LicensesController extends Controller
{
    /**
     * Show the licenses and certifications page with dynamic CMS data.
     */
    public function index(): View
    {
        $globalSettings = GlobalSetting::current();
        $pageSettings = PageSetting::forPage('licenses');
        $certificationCategories = CertificationCategory::orderBy('sort_order')->get();
        $corporateAccreditations = CorporateAccreditation::with('certificationCategory')->orderBy('sort_order')->get();
        $productCertifications = ProductCertification::with('work')->orderBy('sort_order')->get();

        return view('license', compact(
            'globalSettings',
            'pageSettings',
            'certificationCategories',
            'corporateAccreditations',
            'productCertifications'
        ));
    }
}
