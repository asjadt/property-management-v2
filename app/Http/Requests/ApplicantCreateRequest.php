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

        'property_type' => [
        'required',
        'string'
    ],

        'no_of_beds' => [
        'required',
        'string'
    ],

        'no_of_baths' => [
        'required',
        'string'
    ],

        'deadline_to_move' => [
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


];



return $rules;
}
}


