<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class LoginThrottleTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
        
        // Clear any existing rate limits
        RateLimiter::clear('login:' . request()->ip());
    }

    public function test_login_throttle_after_multiple_failed_attempts(): void
    {
        // Make 5 failed login attempts
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ]);
            
            if ($i < 4) {
                // First 4 attempts should redirect back with error
                $response->assertRedirect();
                $response->assertSessionHasErrors(['email']);
            }
        }
        
        // The 6th attempt should be throttled
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);
        
        $response->assertSessionHasErrors(['email']);
        $errors = session('errors')->get('email');
        $this->assertStringContainsString('Too many login attempts', $errors[0]);
    }

    public function test_throttle_message_shows_wait_time(): void
    {
        // Force throttling
        for ($i = 0; $i < 6; $i++) {
            $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ]);
        }
        
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);
        
        $response->assertSessionHasErrors(['email']);
        $errors = session('errors')->get('email');
        $this->assertMatchesRegularExpression('/Please try again in \d+ second/', $errors[0]);
    }

    public function test_successful_login_resets_throttle(): void
    {
        // Make some failed attempts
        for ($i = 0; $i < 3; $i++) {
            $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ]);
        }
        
        // Successful login
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);
        
        $response->assertRedirect('/admin/dashboard');
        
        // Logout
        $this->post('/logout');
        
        // Should be able to make failed attempts again without immediate throttling
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);
        
        $response->assertSessionHasErrors(['email']);
        $errors = session('errors')->get('email');
        $this->assertStringNotContainsString('Too many login attempts', $errors[0]);
    }

    public function test_throttle_is_per_ip_and_email_combination(): void
    {
        // Make failed attempts for one user
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ]);
        }
        
        // Different email should not be throttled
        $otherUser = User::factory()->create([
            'email' => 'other@example.com',
            'password' => bcrypt('password'),
        ]);
        
        $response = $this->post('/login', [
            'email' => 'other@example.com',
            'password' => 'wrong-password',
        ]);
        
        $response->assertSessionHasErrors(['email']);
        $errors = session('errors')->get('email');
        $this->assertStringNotContainsString('Too many login attempts', $errors[0]);
    }
}