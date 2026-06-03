<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAuthFallbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_can_fallback_from_users_table_and_sync_admin_record(): void
    {
        config(['admin.sync_from_users' => true]);

        User::factory()->create([
            'email' => 'role-admin@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'admin',
        ]);

        $response = $this->post(route('admin.login.store'), [
            'email' => 'role-admin@example.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated('admin');
        $this->assertDatabaseHas('admins', [
            'email' => 'role-admin@example.com',
            'role' => 'admin',
        ]);
    }

    public function test_admin_with_unverified_email_is_auto_verified_when_logging_in(): void
    {
        $admin = Admin::create([
            'name' => 'Admin Legacy',
            'email' => 'legacy-admin@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'admin',
            'email_verified_at' => null,
        ]);

        $response = $this->post(route('admin.login.store'), [
            'email' => 'legacy-admin@example.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated('admin');
        $this->assertNotNull($admin->fresh()->email_verified_at);
    }

    public function test_admin_login_can_fallback_from_hashed_env_credentials(): void
    {
        config([
            'admin.super_admin_email' => 'env-super@example.com',
            'admin.super_admin_password_hash' => Hash::make('super-secret-123'),
        ]);

        $response = $this->post(route('admin.login.store'), [
            'email' => 'env-super@example.com',
            'password' => 'super-secret-123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated('admin');
        $this->assertDatabaseHas('admins', [
            'email' => 'env-super@example.com',
            'role' => 'super_admin',
        ]);
    }

    public function test_admin_login_can_fallback_from_plain_env_credentials(): void
    {
        config([
            'admin.super_admin_email' => 'env-plain@example.com',
            'admin.super_admin_password' => 'plain-super-secret',
            'admin.super_admin_password_hash' => null,
        ]);

        $response = $this->post(route('admin.login.store'), [
            'email' => 'env-plain@example.com',
            'password' => 'plain-super-secret',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated('admin');
        $this->assertDatabaseHas('admins', [
            'email' => 'env-plain@example.com',
            'role' => 'super_admin',
        ]);
    }
}
