<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StrongPassword implements ValidationRule
{
    /**
     * Common weak passwords to reject
     */
    protected array $weakPasswords = [
        'password', '12345678', 'qwerty123', 'admin123', 'password123',
        'welcome123', 'letmein123', 'monkey123', '11111111', '00000000',
        'abcdefgh', 'password1', 'qwertyui', 'asdfghjk', 'zxcvbnm1',
    ];

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (strlen($value) < 8) {
            $fail('The :attribute must be at least 8 characters.');
            return;
        }

        if (in_array(strtolower($value), $this->weakPasswords)) {
            $fail('The :attribute is too weak. Please choose a stronger password.');
            return;
        }

        // Check for common patterns
        if (preg_match('/^(.)\1+$/', $value)) {
            $fail('The :attribute cannot be all the same character.');
            return;
        }

        if (preg_match('/^(012|123|234|345|456|567|678|789|890|987|876|765|654|543|432|321|210)+/', $value)) {
            $fail('The :attribute cannot be a simple sequence.');
            return;
        }
    }
}