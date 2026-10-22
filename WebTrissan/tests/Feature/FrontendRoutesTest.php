<?php

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
        'corporateAccreditations',
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
        'featuredWorks',
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
        'specialtyClusters',
        'personnel',
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

test('contact form validates required fields', function () {
    $response = $this->post(route('contact.store'), []);

    $response->assertSessionHasErrors(['name', 'email', 'message']);
});
