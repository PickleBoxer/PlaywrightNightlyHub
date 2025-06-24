<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class EmailDomain implements ValidationRule
{
    /**
     * The allowed email domain.
     */
    private string $allowedDomain = 'aer-wsale.com';

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $domain = explode('@', $value)[1] ?? '';

        if ($domain !== $this->allowedDomain) {
            $fail("The {$attribute} must be from the {$this->allowedDomain} domain.");
        }
    }
}
