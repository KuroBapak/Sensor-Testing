<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('unauthenticated users are redirected from filament admin dashboard to login', function () {
    $response = $this->get('/admin');

    $response->assertRedirect('/admin/login');
});

test('authenticated admin user can access filament admin dashboard', function () {
    $admin = User::factory()->create([
        'email' => 'admin_test@example.com',
    ]);

    $response = $this->actingAs($admin)->get('/admin');

    $response->assertOk();
});

test('authenticated admin user can access collection resources index pages', function () {
    $admin = User::factory()->create([
        'email' => 'admin_test2@example.com',
    ]);

    $routes = [
        '/admin/service-clusters',
        '/admin/services',
        '/admin/works',
        '/admin/certification-categories',
        '/admin/corporate-accreditations',
        '/admin/product-certifications',
        '/admin/portfolio-categories',
        '/admin/portfolio-items',
        '/admin/specialty-clusters',
        '/admin/skills',
        '/admin/personnels',
        '/admin/contact-submissions',
    ];

    foreach ($routes as $route) {
        $response = $this->actingAs($admin)->get($route);
        $response->assertOk();
    }
});
