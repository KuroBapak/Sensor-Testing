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
    expect(Work::count())->toBe(7);
    expect(Work::where('is_featured', true)->count())->toBe(6);
    expect(CertificationCategory::count())->toBe(4);
    expect(CorporateAccreditation::count())->toBe(6);
    expect(ProductCertification::count())->toBe(3);
    expect(PortfolioCategory::count())->toBe(3);
    expect(PortfolioItem::count())->toBe(7);
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
    $productCert->update(['work_id' => $work->id]);
    expect($productCert->fresh()->work)->toBeInstanceOf(Work::class);

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
    $work->update(['quality_standards' => ['ISO 9001:2015'], 'tolerance_value' => 0.002, 'scale_value' => 10]);
    $work = $work->fresh();
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

    expect($about->org_chart)->toHaveKeys(['chart_revision_tag', 'nodes']);
    $orgNode = $about->org_chart['nodes'][0];
    expect($orgNode)->toHaveKeys(['id', 'name', 'position', 'parent_id', 'placement', 'sort_order']);

    $work = Work::first();
    expect($work->work_slug)->not->toBeNull();
    expect($work->top_badge)->not->toBeNull();
    expect($work->serial_number)->not->toBeNull();
    expect($work->client_sector)->not->toBeNull();

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

test('seeded records point to placeholder media that exists on the public disk', function () {
    $this->seed();

    $paths = [
        GlobalSetting::first()->navbar_logo,
        GlobalSetting::first()->footer_partner_image,
        AboutCompany::first()->hero_image,
        AboutCompany::first()->company_profile_pdf,
        Work::first()->work_image,
        PortfolioItem::first()->image,
        CorporateAccreditation::first()->certificate_image,
        ProductCertification::first()->certificate_pdf,
    ];

    foreach ($paths as $path) {
        expect($path)->not->toBeNull()
            ->and(Storage::disk('public')->exists($path))->toBeTrue();
    }

    expect(Work::first()->image_url)->toBe(asset('storage/'.Work::first()->work_image));
});

test('seeded map urls use the key-less embed format that google accepts', function () {
    $this->seed();

    $global = GlobalSetting::first();

    expect($global->maps_iframe_embed_url)->toStartWith('https://maps.google.com/maps?q=')
        ->toContain('output=embed')
        ->not->toContain('pb=')
        ->and($global->maps_button_url)->toStartWith('https://maps.google.com/?q=');
});

test('seeded placeholder images are real raster images that the filament upload preview can read', function () {
    $this->seed();

    foreach ([Work::first()->work_image, GlobalSetting::first()->navbar_logo, AboutCompany::first()->hero_image] as $path) {
        expect($path)->toMatch('/\.(png|jpe?g|webp)$/');

        $size = getimagesizefromstring(Storage::disk('public')->get($path));

        expect($size)->not->toBeFalse()
            ->and($size['mime'])->toBeIn(['image/png', 'image/jpeg', 'image/webp']);
    }
});

test('public disk urls are relative so uploaded files load from any host and port', function () {
    expect(Storage::disk('public')->url('seed/work-1.png'))->toBe('/storage/seed/work-1.png');
});
