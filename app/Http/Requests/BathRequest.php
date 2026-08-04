<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Bath;
use App\Rules\ValidateBath;

class BathRequest extends FormRequest
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
                Rule::unique((new Bath)->getTable(), 'title')
            ],
            'description' => 'nullable|string',
        ];

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['id'] = [
                'required',
                'integer',
                new ValidateBath()
            ];
            $rules['title'] = [
                'required',
                'string',
                'max:255',
                Rule::unique((new Bath)->getTable(), 'title')->ignore($this->id)
            ];
        }

        return $rules;
    }
}
