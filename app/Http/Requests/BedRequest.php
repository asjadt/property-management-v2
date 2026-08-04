<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Bed;
use App\Rules\ValidateBed;

class BedRequest extends FormRequest
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
                Rule::unique((new Bed)->getTable(), 'title')
            ],
            'description' => 'nullable|string',
        ];

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['id'] = [
                'required',
                'integer',
                new ValidateBed()
            ];
            $rules['title'] = [
                'required',
                'string',
                'max:255',
                Rule::unique((new Bed)->getTable(), 'title')->ignore($this->id)
            ];
        }

        return $rules;
    }
}
