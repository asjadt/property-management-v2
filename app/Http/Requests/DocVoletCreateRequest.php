<?php



namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;



class DocVoletCreateRequest extends FormRequest
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

        'title' => [
        'nullable',
        'string',

    ],

        'description' => [
        'nullable',
        'string',
    ],

        'date' => [
        'nullable',
        'string',

    ],

        'note' => [
        'nullable',
        'string',
    ],

        'files' => [
        'present',
        'array',
    ],

        'property_id' => [
        'required',
        'numeric',
        'exists:properties,id'
    ],


];



return $rules;
}
}


