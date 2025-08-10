<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        Setting::setValue('company_name', 'PT Swastika Investama Prima');
        Setting::setValue('company_email', 'info@swastika.co.id');
    }

    public function test_contact_form_submits_successfully_with_valid_data(): void
    {
        Mail::fake();
        
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Business Inquiry',
            'message' => 'I would like to know more about your services.',
        ];
        
        $response = $this->post('/contact', $data);
        
        $response->assertRedirect('/contact');
        $response->assertSessionHas('success', 'Thank you for your message. We will get back to you soon!');
        
        $this->assertDatabaseHas('contact_messages', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Business Inquiry',
            'message' => 'I would like to know more about your services.',
            'handled' => false,
        ]);
    }

    public function test_contact_form_validates_required_fields(): void
    {
        $response = $this->post('/contact', []);
        
        $response->assertSessionHasErrors(['name', 'email', 'subject', 'message']);
    }

    public function test_contact_form_validates_email_format(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'subject' => 'Test Subject',
            'message' => 'Test message',
        ];
        
        $response = $this->post('/contact', $data);
        
        $response->assertSessionHasErrors(['email']);
    }

    public function test_contact_form_validates_maximum_lengths(): void
    {
        $data = [
            'name' => str_repeat('a', 256), // Too long
            'email' => 'test@' . str_repeat('a', 250) . '.com', // Too long
            'subject' => str_repeat('a', 501), // Too long
            'message' => str_repeat('a', 5001), // Too long
        ];
        
        $response = $this->post('/contact', $data);
        
        $response->assertSessionHasErrors(['name', 'email', 'subject', 'message']);
    }

    public function test_contact_form_prevents_spam_with_honeypot(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Business Inquiry',
            'message' => 'I would like to know more about your services.',
            'website' => 'http://spam.com', // Honeypot field
        ];
        
        $response = $this->post('/contact', $data);
        
        $response->assertStatus(422);
        $this->assertDatabaseMissing('contact_messages', [
            'email' => 'john@example.com',
        ]);
    }

    public function test_contact_form_prevents_spam_with_rate_limiting(): void
    {
        // Mock the rate limiter to simulate hitting the limit
        \Illuminate\Support\Facades\RateLimiter::shouldReceive('tooManyAttempts')
            ->once()
            ->with('contact-form.127.0.0.1', 5)
            ->andReturn(true);
            
        \Illuminate\Support\Facades\RateLimiter::shouldReceive('availableIn')
            ->once()
            ->with('contact-form.127.0.0.1')
            ->andReturn(60);
        
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Business Inquiry',
            'message' => 'I would like to know more about your services.',
        ];
        
        $response = $this->post('/contact', $data);
        
        $response->assertStatus(429); // Too Many Requests
    }

    public function test_contact_form_stores_client_ip(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Business Inquiry',
            'message' => 'I would like to know more about your services.',
        ];
        
        $response = $this->post('/contact', $data);
        
        $this->assertDatabaseHas('contact_messages', [
            'email' => 'john@example.com',
            'created_by_ip' => '127.0.0.1',
        ]);
    }

    public function test_contact_form_sanitizes_input(): void
    {
        $data = [
            'name' => '<script>alert("xss")</script>John Doe',
            'email' => 'john@example.com',
            'subject' => '<b>Important</b> Subject',
            'message' => '<p>Hello</p><script>alert("xss")</script>',
        ];
        
        $response = $this->post('/contact', $data);
        
        $this->assertDatabaseHas('contact_messages', [
            'name' => 'alert("xss")John Doe', // Script tags removed but content preserved
            'email' => 'john@example.com',
            'subject' => 'Important Subject', // HTML tags cleaned
            'message' => 'Helloalert("xss")', // Script and p tags removed
        ]);
    }

    public function test_contact_form_sends_notification_email(): void
    {
        Mail::fake();
        
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Business Inquiry',
            'message' => 'I would like to know more about your services.',
        ];
        
        $response = $this->post('/contact', $data);
        
        Mail::assertQueued(\App\Mail\ContactMessageNotification::class, function ($mail) {
            return $mail->hasTo('info@swastika.co.id') &&
                   $mail->contactMessage->name === 'John Doe';
        });
    }

    public function test_contact_form_sends_auto_reply_email(): void
    {
        Mail::fake();
        
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Business Inquiry',
            'message' => 'I would like to know more about your services.',
        ];
        
        $response = $this->post('/contact', $data);
        
        Mail::assertQueued(\App\Mail\ContactAutoReply::class, function ($mail) {
            return $mail->hasTo('john@example.com') &&
                   $mail->contactMessage->name === 'John Doe';
        });
    }

    public function test_contact_form_handles_missing_company_email_gracefully(): void
    {
        Mail::fake();
        Setting::setValue('company_email', null);
        
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Business Inquiry',
            'message' => 'I would like to know more about your services.',
        ];
        
        $response = $this->post('/contact', $data);
        
        $response->assertRedirect('/contact');
        $response->assertSessionHas('success');
        
        // Should still save to database
        $this->assertDatabaseHas('contact_messages', [
            'email' => 'john@example.com',
        ]);
        
        // Should not send notification email if company email is not set
        Mail::assertNotQueued(\App\Mail\ContactMessageNotification::class);
        
        // Should still send auto-reply
        Mail::assertQueued(\App\Mail\ContactAutoReply::class);
    }
}