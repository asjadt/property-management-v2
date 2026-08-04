<?php

namespace App\Http\Requests;

use App\Rules\ValidateBath;
use App\Rules\ValidateBed;
use Illuminate\Foundation\Http\FormRequest;

class SyncPropertyTypeRelationsRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'bed_ids' => ['nullable', 'array'],
            'bed_ids.*' => [new ValidateBed()],
            'bath_ids' => ['nullable', 'array'],
            'bath_ids.*' => [new ValidateBath()],
        ];
    }
}
