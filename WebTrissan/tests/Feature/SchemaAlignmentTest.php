<?php

use App\Models\AboutCompany;
use App\Models\CertificationCategory;
use App\Models\ContactSubmission;
use App\Models\CorporateAccreditation;
use App\Models\GlobalSetting;
use App\Models\PageSetting;
use App\Models\Personnel;
use App\Models\PortfolioCategory;
use App\Models\PortfolioItem;
use App\Models\ProductCertification;
use App\Models\Service;
use App\Models\ServiceCluster;
use App\Models\Skill;
use App\Models\SpecialtyCluster;
use App\Models\Work;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('all models can be instantiated and queried', function () {
    expect(GlobalSetting::class)->toBeClass();
    expect(AboutCompany::class)->toBeClass();
    expect(PageSetting::class)->toBeClass();
    expect(ServiceCluster::class)->toBeClass();
    expect(Service::class)->toBeClass();
    expect(Work::class)->toBeClass();
    expect(CertificationCategory::class)->toBeClass();
    expect(CorporateAccreditation::class)->toBeClass();
    expect(ProductCertification::class)->toBeClass();
    expect(PortfolioCategory::class)->toBeClass();
    expect(PortfolioItem::class)->toBeClass();
    expect(SpecialtyCluster::class)->toBeClass();
    expect(Skill::class)->toBeClass();
    expect(Personnel::class)->toBeClass();
    expect(ContactSubmission::class)->toBeClass();
});

test('seeders populate database correctly', function () {
    $this->seed();

    expect(GlobalSetting::count())->toBe(1);
    expect(AboutCompany::count())->toBe(1);
    expect(PageSetting::count())->toBe(5);
    expect(ServiceCluster::count())->toBe(4);
    expect(Service::count())->toBe(12);
    expect(Work::count())->toBe(9);
    expect(Work::where('is_featured', true)->count())->toBe(6);
    expect(CertificationCategory::count())->toBe(4);
    expect(CorporateAccreditation::count())->toBe(6);
    expect(ProductCertification::count())->toBe(3);
    expect(PortfolioCategory::count())->toBe(4);
    expect(PortfolioItem::count())->toBe(8);
    expect(SpecialtyCluster::count())->toBe(4);
    expect(Skill::count())->toBe(8);
    expect(Personnel::count())->toBe(5);
});
test('model relationships work correctly', function () {
    $this->seed();

    $service = Service::first();
    expect($service->serviceCluster)->toBeInstanceOf(ServiceCluster::class);

    $cluster = ServiceCluster::first();
    expect($cluster->services->count())->toBeGreaterThan(0);

    $work = Work::first();
    expect($work->service)->toBeInstanceOf(Service::class);
    expect($service->works)->toBeInstanceOf(Collection::class);

    $accreditation = CorporateAccreditation::first();
    expect($accreditation->certificationCategory)->toBeInstanceOf(CertificationCategory::class);

    $productCert = ProductCertification::first();
    expect($productCert->work)->toBeInstanceOf(Work::class);

    $portfolioItem = PortfolioItem::first();
    expect($portfolioItem->portfolioCategory)->toBeInstanceOf(PortfolioCategory::class);

    $personnel = Personnel::first();
    expect($personnel->specialtyCluster)->toBeInstanceOf(SpecialtyCluster::class);
    expect($personnel->skills->count())->toBeGreaterThan(0);

    $submission = ContactSubmission::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '+62123456789',
        'message' => 'Test message',
        'service_id' => $service->id,
        'status' => 'new',
    ]);
    expect($submission->service)->toBeInstanceOf(Service::class);
});

test('model casts work correctly', function () {
    $this->seed();

    $global = GlobalSetting::first();
    expect($global->footer_legal_links)->toBeArray();
    expect($global->company_stats)->toBeArray();
    expect($global->phones)->toBeArray();
    expect($global->social_media)->toBeArray();

    $landingPage = PageSetting::where('page', 'landing')->first();
    expect($landingPage->hero_slides)->toBeArray();

    $worksPage = PageSetting::where('page', 'works')->first();
    expect($worksPage->stats)->toBeArray();

    $about = AboutCompany::first();
    expect($about->image_overlay)->toBeArray();
    expect($about->vision_mission_values)->toBeArray();
    expect($about->facilities)->toBeArray();
    expect($about->milestones)->toBeArray();
    expect($about->org_chart)->toBeArray();

    $work = Work::first();
    expect($work->is_featured)->toBeBool();
    expect($work->gallery_images)->toBeArray();
    expect($work->highlight_tags)->toBeArray();
    expect($work->quality_standards)->toBeArray();
    expect($work->tolerance_value)->toBeFloat();
    expect($work->scale_value)->toBeInt();
    expect($work->production_date)->toBeInstanceOf(Carbon::class);

    $personnel = Personnel::first();
    expect($personnel->status)->toBeBool();
    expect($personnel->qualification_rank)->toBeInt();

    $productCert = ProductCertification::first();
    expect($productCert->is_featured)->toBeBool();
    expect($productCert->spec_rows)->toBeArray();
    expect($productCert->validity_date)->toBeInstanceOf(Carbon::class);

    $corpAccred = CorporateAccreditation::first();
    expect($corpAccred->validity_date)->toBeInstanceOf(Carbon::class);
});

test('PRD seed data structure matches expectations', function () {
    $this->seed();

    $global = GlobalSetting::first();
    $phone = $global->phones[0];
    expect($phone)->toHaveKeys(['display_text', 'tel_url', 'is_whatsapp']);

    $social = $global->social_media[0];
    expect($social)->toHaveKeys(['platform_name', 'platform_icon', 'handle', 'description', 'profile_url', 'action_text', 'sort_order']);

    $stat = $global->company_stats[0];
    expect($stat)->toHaveKeys(['value', 'label_landing', 'label_about']);

    $landing = PageSetting::where('page', 'landing')->first();
    expect($landing->hero_slides)->toBeArray();
    expect($landing->hero_slides[0])->toHaveKeys(['image', 'alt_text']);
    expect($landing->trust_card)->toHaveKeys(['label', 'rating', 'description', 'clients', 'footer_left', 'footer_right']);

    $about = AboutCompany::first();
    $vmv = $about->vision_mission_values[0];
    expect($vmv)->toHaveKey('card_type');
    expect($vmv['card_type'])->toBe('vision');

    $facility = $about->facilities[0];
    expect($facility)->toHaveKeys(['facility_image', 'facility_title', 'facility_tag', 'spec_tag_1', 'spec_tag_2']);

    $milestone = $about->milestones[0];
    expect($milestone)->toHaveKeys(['year', 'title', 'description', 'highlights', 'is_current']);

    expect($about->org_chart)->toHaveKeys(['chart_revision_tag', 'ceo', 'general_manager', 'division_heads']);
    $divHead = $about->org_chart['division_heads'][0];
    expect($divHead)->toHaveKeys(['division_tag', 'head_name', 'head_position', 'teams']);

    $work = Work::first();
    expect($work->work_slug)->not->toBeNull();
    expect($work->top_badge)->not->toBeNull();
    expect($work->serial_number)->not->toBeNull();
    expect($work->client_sector)->not->toBeNull();
    expect($work->tolerance_value)->toBeFloat();
    expect($work->scale_value)->toBeInt();

    $cert = ProductCertification::first();
    $specRow = $cert->spec_rows[0];
    expect($specRow)->toHaveKeys(['label', 'value']);

    $portfolioItem = PortfolioItem::first();
    expect($portfolioItem->industry_tag)->not->toBeNull();

    $personnel = Personnel::first();
    expect($personnel->registration_id)->not->toBeNull();
    expect($personnel->status)->toBeBool();
    expect($personnel->technical_focus)->not->toBeNull();
    expect($personnel->qualification_rank)->toBeInt();
});
