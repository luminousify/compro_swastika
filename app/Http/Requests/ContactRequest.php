<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class ContactRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:500',
            'message' => 'required|string|max:5000',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Simple honeypot spam protection
            if ($this->filled('website')) {
                abort(422, 'Spam detected.');
            }
        });
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Sanitize input data - remove all HTML tags and decode HTML entities
        $this->merge([
            'name' => html_entity_decode(strip_tags($this->input('name') ?? ''), ENT_QUOTES, 'UTF-8'),
            'subject' => html_entity_decode(strip_tags($this->input('subject') ?? ''), ENT_QUOTES, 'UTF-8'),
            'message' => html_entity_decode(strip_tags($this->input('message') ?? ''), ENT_QUOTES, 'UTF-8'),
        ]);
    }
}
