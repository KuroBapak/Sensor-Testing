<?php

use App\Jobs\RunBackupJob;
use App\Models\Role;
use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\actingAs;

it('requires permission to update backup settings', function () {
    $user = User::factory()->create(); // no role

    actingAs($user)
        ->post(route('admin.backup.update'), [
            'provider' => 'S3-compatible',
            'endpoint' => 'https://s3.amazonaws.com',
            'region' => 'us-east-1',
            'bucket' => 'my-backups',
            'access_key_id' => '123',
            'secret_access_key' => 'abc',
            'schedule_time' => '02:00',
            'retention_days' => 30,
            'enabled' => true,
        ])
        ->assertForbidden();
});

it('updates backup settings successfully', function () {
    $role = Role::create(['name' => 'Manager', 'is_system' => false]);
    $role->permissions()->create(['permission_key' => 'backup.manage']);
    $user = User::factory()->create(['role_id' => $role->id]);

    $site = Site::create([
        'name' => 'Test Site',
        'timezone' => 'UTC',
    ]);

    actingAs($user)
        ->post(route('admin.backup.update'), [
            'provider' => 'S3-compatible',
            'endpoint' => 'https://s3.amazonaws.com',
            'region' => 'us-east-1',
            'bucket' => 'my-backups',
            'use_path_style' => true,
            'access_key_id' => '123',
            'secret_access_key' => 'abc',
            'schedule_time' => '02:00',
            'retention_days' => 30,
            'enabled' => true,
        ])
        ->assertRedirect()
        ->assertSessionHas('success', 'Backup settings updated successfully.');

    $this->assertDatabaseHas('backup_settings', [
        'site_id' => $site->id,
        'bucket' => 'my-backups',
        'enabled' => 1,
    ]);
});

it('can trigger a manual backup', function () {
    $role = Role::create(['name' => 'Manager', 'is_system' => false]);
    $role->permissions()->create(['permission_key' => 'backup.manage']);
    $user = User::factory()->create(['role_id' => $role->id]);

    $site = Site::create([
        'name' => 'Test Site',
        'timezone' => 'UTC',
    ]);

    Queue::fake();

    actingAs($user)
        ->post(route('admin.backup.trigger'))
        ->assertRedirect()
        ->assertSessionHas('success', 'Backup triggered successfully. It will run in the background.');

    $this->assertDatabaseHas('backup_runs', [
        'site_id' => $site->id,
        'trigger' => 'manual',
        'status' => 'running',
    ]);

    Queue::assertPushed(RunBackupJob::class);
});
