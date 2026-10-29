<?php

use App\Models\GlobalSetting;
use App\Models\Service;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('home page returns successful response with expected data', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertViewIs('home');
    $response->assertViewHas([
        'globalSettings',
        'pageSettings',
        'serviceClusters',
        'services',
        'featuredWorks',
    ]);
});

test('works page returns successful response with expected data', function () {
    $response = $this->get(route('works'));

    $response->assertOk();
    $response->assertViewIs('works');
    $response->assertViewHas([
        'globalSettings',
        'pageSettings',
        'services',
        'works',
    ]);
});

test('portfolio page returns successful response with expected data', function () {
    $response = $this->get(route('portfolio'));

    $response->assertOk();
    $response->assertViewIs('portofolio');
    $response->assertViewHas([
        'globalSettings',
        'pageSettings',
        'portfolioCategories',
        'portfolioItems',
        'specialtyClusters',
        'personnel',
    ]);
});

test('licenses page returns successful response with expected data', function () {
    $response = $this->get(route('licenses'));

    $response->assertOk();
    $response->assertViewIs('license');
    $response->assertViewHas([
        'globalSettings',
        'pageSettings',
        'certificationCategories',
        'corporateAccreditations',
        'productCertifications',
    ]);
});

test('about page returns successful response with expected data', function () {
    $response = $this->get(route('about'));

    $response->assertOk();
    $response->assertViewIs('abtus');
    $response->assertViewHas([
        'globalSettings',
        'pageSettings',
        'aboutCompany',
    ]);
});

test('contact form stores submission successfully', function () {
    $payload = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '+628123456789',
        'message' => 'We are interested in your precision engineering services.',
    ];

    $response = $this->post(route('contact.store'), $payload);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('contact_submissions', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '+628123456789',
        'status' => 'new',
    ]);
});

test('contact form redirects to whatsapp with the message prefilled when a whatsapp number is set', function () {
    GlobalSetting::current()->update(['whatsapp_number' => '0812-3456-7890']);
    $service = Service::first();

    $response = $this->post(route('contact.store'), [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '+628123456789',
        'message' => 'Mohon penawaran & jadwal survei.',
        'service_id' => $service->id,
    ]);

    $expectedText = "Halo, saya John Doe.\nLayanan yang diminati: {$service->service_title}\nEmail: john@example.com\nTelepon: +628123456789\n\nMohon penawaran & jadwal survei.";

    $response->assertRedirect('https://wa.me/6281234567890?text='.rawurlencode($expectedText));
    $this->assertDatabaseHas('contact_submissions', ['email' => 'john@example.com', 'service_id' => $service->id]);
});

test('contact form falls back to the phone marked as whatsapp when no dedicated number is set', function () {
    GlobalSetting::current()->update(['whatsapp_number' => null, 'phones' => [
        ['display_text' => '(021) 1111-2222', 'tel_url' => 'tel:+62211111222', 'is_whatsapp' => false],
        ['display_text' => '+62 811-9870-221', 'tel_url' => 'tel:+628119870221', 'is_whatsapp' => true],
    ]]);

    $this->post(route('contact.store'), ['name' => 'John Doe', 'email' => 'john@example.com', 'message' => 'Halo'])
        ->assertRedirectContains('https://wa.me/628119870221?text=');
});

test('contact form does not redirect honeypot submissions to whatsapp', function () {
    GlobalSetting::current()->update(['whatsapp_number' => '0812-3456-7890']);

    $this->post(route('contact.store'), [
        'name' => 'Spam Bot',
        'email' => 'bot@example.com',
        'message' => 'Buy cheap links',
        'website' => 'http://spam.example.com',
    ])->assertRedirect(url('/'))->assertSessionHas('success');
});

test('contact form stays on the page when no phone is marked as whatsapp', function () {
    GlobalSetting::current()->update(['phones' => [
        ['display_text' => '(021) 1111-2222', 'tel_url' => 'tel:+62211111222', 'is_whatsapp' => false],
    ]]);

    $this->from(url('/'))
        ->post(route('contact.store'), ['name' => 'John Doe', 'email' => 'john@example.com', 'message' => 'Halo'])
        ->assertRedirect(url('/'))
        ->assertSessionHas('success');
});

test('contact form validates required fields', function () {
    $response = $this->post(route('contact.store'), []);

    $response->assertSessionHasErrors(['name', 'email', 'message']);
});

test('contact form silently drops submissions that fill the honeypot field', function () {
    $response = $this->post(route('contact.store'), [
        'name' => 'Spam Bot',
        'email' => 'bot@example.com',
        'message' => 'Buy cheap links',
        'website' => 'http://spam.example.com',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');
    $this->assertDatabaseMissing('contact_submissions', ['email' => 'bot@example.com']);
});

test('contact form returns 429 after five submissions in a minute', function () {
    $payload = ['name' => 'John Doe', 'email' => 'john@example.com', 'message' => 'Hello'];

    foreach (range(1, 5) as $attempt) {
        $this->post(route('contact.store'), $payload)->assertRedirect();
    }

    $this->post(route('contact.store'), $payload)->assertStatus(429);
});
