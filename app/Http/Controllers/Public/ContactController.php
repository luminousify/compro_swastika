<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactRequest;
use App\Mail\ContactAutoReply;
use App\Mail\ContactMessageNotification;
use App\Models\ContactMessage;
use App\Models\Setting;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class ContactController extends Controller
{
    /**
     * Display the contact page
     */
    public function index()
    {
        $seo = [
            'title' => 'Contact Us - ' . Setting::getValue('company_name', 'Company'),
            'description' => 'Get in touch with us for any inquiries or business opportunities',
        ];
        
        $contact = [
            'company_name' => Setting::getValue('company_name'),
            'address' => Setting::getValue('company_address'),
            'phone' => Setting::getValue('company_phone'),
            'email' => Setting::getValue('company_email'),
            'map' => Setting::getValue('company_map'),
        ];
        
        return view('contact.index', compact('seo', 'contact'));
    }
    
    /**
     * Store a contact message
     */
    public function store(ContactRequest $request)
    {
        // Rate limiting - 5 attempts per minute per IP
        $key = 'contact-form.' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            abort(429, "Too many attempts. Please try again in {$seconds} seconds.");
        }
        
        RateLimiter::hit($key, 60);
        
        // Create contact message
        $contactMessage = ContactMessage::create([
            'name' => $request->validated()['name'],
            'email' => $request->validated()['email'],
            'subject' => $request->validated()['subject'],
            'message' => $request->validated()['message'],
            'created_by_ip' => $request->ip(),
            'handled' => false,
        ]);
        
        // Send notification email to company if email is configured
        $companyEmail = Setting::getValue('company_email');
        if ($companyEmail) {
            Mail::to($companyEmail)->queue(new ContactMessageNotification($contactMessage));
        }
        
        // Send auto-reply to sender
        Mail::to($contactMessage->email)->queue(new ContactAutoReply($contactMessage));
        
        // Clear rate limit on successful submission
        RateLimiter::clear($key);
        
        return redirect()->route('contact')
            ->with('success', 'Thank you for your message. We will get back to you soon!');
    }
}