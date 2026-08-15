<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ValidateApplicant;
use App\Rules\ValidatePropertyType;
use App\Rules\ValidateBed;
use App\Rules\ValidateBath;

class ApplicantRequest extends FormRequest
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
        $rules = [
            'customer_name' => [
                'required',
                'string'
            ],
            'customer_phone' => [
                'required',
                'string'
            ],
            'email' => [
                'required',
                'string',
                'email'
            ],
            'country' => [
                'required',
                'string'
            ],
            'city' => [
                'required',
                'string'
            ],
            'postcode' => [
                'required',
                'string'
            ],
            'min_price' => [
                'required',
                'numeric'
            ],
            'max_price' => [
                'required',
                'numeric'
            ],
            'address_line_1' => [
                'required',
                'string'
            ],
            'latitude' => [
                'nullable',
                'numeric'
            ],
            'longitude' => [
                'nullable',
                'numeric'
            ],
            'radius' => [
                'nullable',
                'numeric'
            ],
            'property_type_ids' => [
                'required',
                'array'
            ],
            'property_type_ids.*' => [
                'integer',
                new ValidatePropertyType()
            ],
            'bed_ids' => [
                'required',
                'array'
            ],
            'bed_ids.*' => [
                'integer',
                new ValidateBed()
            ],
            'bath_ids' => [
                'required',
                'array'
            ],
            'bath_ids.*' => [
                'integer',
                new ValidateBath()
            ],
            'deadline_to_move' => [
                'nullable',
                'string'
            ],
            'expiry_date' => [
                'nullable',
                'date'
            ],
            'working' => [
                'nullable',
                'string'
            ],
            'job_title' => [
                'nullable',
                'string'
            ],
            'is_dss' => [
                'required',
                'boolean'
            ],
            'is_send_alert' => [
                'nullable',
                'boolean'
            ],
        ];

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['id'] = [
                'required',
                'numeric',
                new ValidateApplicant()
            ];
        }

        return $rules;
    }
}
