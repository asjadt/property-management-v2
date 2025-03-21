<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TenantCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [

            'first_Name' => 'required|string|max:255',
            'last_Name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:tenants,email',
            'phone' => 'nullable|string',
            'image' => 'nullable|string',
            'address_line_1' => 'nullable|string',
            'address_line_2' => 'nullable|string',
            'country' => 'required|string',
            'city' => 'required|string',
            'postcode' => 'required|string',
            'lat' => 'nullable|string',
            'long' => 'nullable|string',

            'files' => 'present|array',
            'files.*.file' => 'required|string',
            'files.*.description' => 'nullable|string',
        ];
    }
}
