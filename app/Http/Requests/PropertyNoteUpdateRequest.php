<?php




namespace App\Http\Requests;

use App\Models\PropertyNote;
use Illuminate\Foundation\Http\FormRequest;

class PropertyNoteUpdateRequest extends FormRequest
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

'id' => [
  'required',
  'numeric',
  function ($attribute, $value, $fail) {

      $property_note_query_params = [
          "id" => $this->id,
      ];
      $property_note = PropertyNote::where($property_note_query_params)
          ->first();
      if (!$property_note) {
          // $fail($attribute . " is invalid.");
          $fail("no property note found");
          return 0;
      }



          if ($property_note->created_by != auth()->user()->id) {
        // $fail($attribute . " is invalid.");
        $fail("You do not have permission to update this property note due to role restrictions.");
    }



  },
],



    'title' => [
    'nullable',
    'string',

],

    'description' => [
    'nullable',
    'string',
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



