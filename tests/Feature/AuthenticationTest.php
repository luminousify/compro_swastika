<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => UserRole::ADMIN,
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_login_throttling_works(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Make 5 failed attempts
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'admin@example.com',
                'password' => 'wrong-password',
            ]);
        }

        // 6th attempt should be throttled
        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors(['email']);
        $errors = session('errors')->get('email');
        $this->assertStringContainsString('Too many login attempts', $errors[0]);
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::ADMIN,
        ]);

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_password_reset_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_password_reset_link_can_be_requested(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
        ]);

        $this->post('/forgot-password', ['email' => 'admin@example.com']);

        // Check that a password reset token was created
        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => 'admin@example.com',
        ]);
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
        ]);

        // Send password reset request
        $this->post('/forgot-password', ['email' => 'admin@example.com']);

        // Get the token from database
        $tokenRecord = \DB::table('password_reset_tokens')
            ->where('email', 'admin@example.com')
            ->first();

        // The token in database is hashed, we need to use a plain token
        // Let's create a fresh token for testing
        $plainToken = \Str::random(60);
        \DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => 'admin@example.com'],
            [
                'email' => 'admin@example.com',
                'token' => \Hash::make($plainToken),
                'created_at' => now(),
            ]
        );

        $response = $this->post('/reset-password', [
            'token' => $plainToken,
            'email' => 'admin@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect('/login');
        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    }

    public function test_forced_password_change_redirects_to_change_password(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::ADMIN,
            'force_password_change' => true,
        ]);

        $response = $this->actingAs($user)->get('/admin/dashboard');

        $response->assertRedirect(route('password.change'));
    }

    public function test_password_change_screen_can_be_rendered(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::ADMIN,
        ]);

        $response = $this->actingAs($user)->get('/change-password');

        $response->assertStatus(200);
    }

    public function test_password_can_be_changed(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123'),
            'force_password_change' => true,
        ]);

        $response = $this->actingAs($user)->post('/change-password', [
            'current_password' => 'oldpassword123',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
        $this->assertFalse($user->fresh()->force_password_change);
    }

    public function test_weak_passwords_are_rejected(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123'),
        ]);

        $response = $this->actingAs($user)->post('/change-password', [
            'current_password' => 'oldpassword123',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_short_passwords_are_rejected(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123'),
        ]);

        $response = $this->actingAs($user)->post('/change-password', [
            'current_password' => 'oldpassword123',
            'password' => '1234567',
            'password_confirmation' => '1234567',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    protected function tearDown(): void
    {
        RateLimiter::clear('admin@example.com|127.0.0.1');
        parent::tearDown();
    }
}