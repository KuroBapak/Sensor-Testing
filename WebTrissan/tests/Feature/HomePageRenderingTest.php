<?php

use App\Models\ContactSubmission;
use App\Models\GlobalSetting;
use App\Models\PageSetting;
use App\Models\Service;
use App\Models\Work;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('landing contact section renders phones address and emails from global settings', function () {
    GlobalSetting::current()->update([
        'phones' => [
            ['display_text' => '+62 21 7000-1111', 'tel_url' => 'tel:+622170001111', 'is_whatsapp' => false],
            ['display_text' => '+62 812-7000-2222', 'tel_url' => 'tel:+6281270002222', 'is_whatsapp' => true],
        ],
        'factory_address' => 'Jl. Kontak Uji No. 7, Cikarang',
        'primary_email' => 'halo@uji-kontak.test',
        'corporate_email' => 'korporat@uji-kontak.test',
        'facility_zone' => 'Zona Uji Industri',
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('href="tel:+622170001111"', false)
        ->assertSee('+62 812-7000-2222')
        ->assertSee('Jl. Kontak Uji No. 7, Cikarang')
        ->assertSee('href="mailto:halo@uji-kontak.test"', false)
        ->assertSee('korporat@uji-kontak.test')
        ->assertSee('Zona Uji Industri')
        ->assertDontSee('tel:+62215508899', false)
        ->assertDontSee('5508-8990');
});

test('landing social media cards come from the cms and only link to http urls', function () {
    GlobalSetting::current()->update(['social_media' => [
        ['platform_name' => 'Kanal Dua', 'platform_icon' => 'youtube', 'handle' => '@dua', 'description' => 'Deskripsi dua', 'profile_url' => 'https://youtube.com/@dua', 'action_text' => 'Tonton', 'sort_order' => 2],
        ['platform_name' => 'Kanal Satu', 'platform_icon' => 'linkedin', 'handle' => '@satu', 'description' => 'Deskripsi satu', 'profile_url' => 'javascript:alert(1)', 'action_text' => 'Ikuti', 'sort_order' => 1],
    ]]);

    $content = $this->get(route('home'))->assertOk()->getContent();

    expect(strpos($content, 'Kanal Satu'))->toBeLessThan(strpos($content, 'Kanal Dua'))
        ->and($content)->toContain('href="https://youtube.com/@dua"')
        ->not->toContain('javascript:alert(1)')
        ->not->toContain('apexindustrial.id');
});

test('landing map uses the cms embed and button urls when they are https', function () {
    GlobalSetting::current()->update([
        'maps_button_url' => 'https://maps.example.com/buka',
        'maps_iframe_embed_url' => 'https://maps.example.com/embed?x=1',
        'overlay_title' => 'Overlay Uji',
        'overlay_coordinate' => '1°2\'3"S',
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('href="https://maps.example.com/buka"', false)
        ->assertSee('src="https://maps.example.com/embed?x=1"', false)
        ->assertSee('Overlay Uji');
});

test('landing map rejects non-https cms urls and falls back to the default map', function () {
    GlobalSetting::current()->update([
        'maps_button_url' => 'javascript:alert(1)',
        'maps_iframe_embed_url' => 'http://insecure.example.com/embed',
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('javascript:alert(1)', false)
        ->assertDontSee('insecure.example.com', false)
        ->assertSee('output=embed', false);
});

test('landing about teaser stats come from the global company stats with landing labels', function () {
    GlobalSetting::current()->update(['company_stats' => [
        ['value' => '88+', 'label_landing' => 'Label Landing Uji', 'label_about' => 'Label About Uji'],
    ]]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('88+')
        ->assertSee('Label Landing Uji')
        ->assertDontSee('Label About Uji');
});

test('landing hero slides and featured works only use images that exist', function () {
    PageSetting::forPage('landing')->update(['hero_slides' => [
        ['image' => 'landing/hero-uji.jpg', 'alt_text' => 'Hero Uji'],
        ['image' => '/images/hero-tidak-ada.jpg', 'alt_text' => 'Hero Hilang'],
    ]]);
    Work::query()->update(['work_image' => '/images/work-tidak-ada.jpg']);
    Work::where('is_featured', true)->first()->update(['work_image' => 'works/unggulan.jpg']);

    $content = $this->get(route('home'))->assertOk()->getContent();

    expect($content)
        ->toContain('src="'.asset('storage/landing/hero-uji.jpg').'"')
        ->toContain('src="'.asset('storage/works/unggulan.jpg').'"')
        ->not->toContain('storage//')
        ->not->toContain('hero-tidak-ada.jpg')
        ->not->toContain('work-tidak-ada.jpg');
});

test('landing exposes a contact form modal that posts to the contact route', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('id="contact-modal"', false)
        ->assertSee('action="'.route('contact.store').'"', false)
        ->assertSee('name="_token"', false)
        ->assertSee('name="website"', false)
        ->assertSee('onclick="openContactModal()"', false)
        ->assertDontSee('href="#contact-modal"', false);
});

test('landing contact form lists services as optional interest', function () {
    $response = $this->get(route('home'))->assertOk();

    foreach (Service::all() as $service) {
        $response->assertSee('<option value="'.$service->id.'"', false);
    }
});

test('landing reopens the contact modal with errors and old input after a failed submission', function () {
    $this->from(route('home'))
        ->post(route('contact.store'), ['name' => 'Budi Uji', 'email' => 'bukan-email', 'message' => ''])
        ->assertRedirect(route('home'));

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('value="Budi Uji"', false)
        ->assertSee('The email field must be a valid email address.')
        ->assertSee('The message field is required.')
        ->assertSee('items-center justify-center p-4 bg-black/70 backdrop-blur-sm flex', false);
});

test('landing shows the success message after a contact submission', function () {
    $this->from(route('home'))
        ->post(route('contact.store'), ['name' => 'Siti Uji', 'email' => 'siti@uji.test', 'message' => 'Mohon penawaran.'])
        ->assertRedirect(route('home'))
        ->assertSessionHas('success');

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Thank you for contacting us!');

    expect(ContactSubmission::where('email', 'siti@uji.test')->value('status'))->toBe('new');
});

test('landing keeps the contact modal closed on a normal visit', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('items-center justify-center p-4 bg-black/70 backdrop-blur-sm hidden', false)
        ->assertDontSee('Thank you for contacting us!');
});
