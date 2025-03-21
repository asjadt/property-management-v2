<?php


namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;



class AccreditationCreateRequest extends FormRequest
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

        'name' => [
        'required',
        'string'
    ],

        'description' => [
        'nullable',
        'string'
    ],

        'accreditation_start_date' => [
        'required',
        'date'
    ],

        'accreditation_expiry_date' => [
        'required',
        'date'
    ],

        'logo' => [
        'nullable',
        'string',

    ],

     




];



return $rules;
}
}


