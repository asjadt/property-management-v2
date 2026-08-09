<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\Applicant;

class ValidateApplicant implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $applicant = Applicant::where('id', $value)->first();

        if (!$applicant) {
            $fail("No applicant found.");
            return;
        }

        if ($applicant->created_by != auth()->id()) {
            $fail("You do not have permission to update this applicant due to role restrictions.");
        }
    }
}
