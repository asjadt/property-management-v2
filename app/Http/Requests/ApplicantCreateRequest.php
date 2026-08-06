<?php



namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;



class ApplicantCreateRequest extends FormRequest
{
/**
* Determine if the user is authorized to make this request.
*
* @return  bool
*/
public function authorize()
{
return true;
}

/**
* Get the validation rules that apply to the request.
*
* @return  array
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
            new \App\Rules\ValidatePropertyType()
        ],

        'bed_ids' => [
            'required',
            'array'
        ],
        'bed_ids.*' => [
            'integer',
            new \App\Rules\ValidateBed()
        ],

        'bath_ids' => [
            'required',
            'array'
        ],
        'bath_ids.*' => [
            'integer',
            new \App\Rules\ValidateBath()
        ],

        'deadline_to_move' => [
        'nullable',
        'string'
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


];



return $rules;
}
}


