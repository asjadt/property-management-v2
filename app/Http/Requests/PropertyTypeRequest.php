<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\PropertyType;
use App\Rules\ValidatePropertyType;
use App\Rules\ValidateBath;
use App\Rules\ValidateBed;

class PropertyTypeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique((new PropertyType)->getTable(), 'title')
            ],
            'description' => 'nullable|string',
            'bed_ids' => ['nullable', 'array'],
            'bed_ids.*' => [new ValidateBed()],
            'bath_ids' => ['nullable', 'array'],
            'bath_ids.*' => [new ValidateBath()],
        ];

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['id'] = [
                'required',
                'integer',
                new ValidatePropertyType()
            ];
            $rules['title'] = [
                'required',
                'string',
                'max:255',
                Rule::unique((new PropertyType)->getTable(), 'title')->ignore($this->id)
            ];
        }

        return $rules;
    }
}
