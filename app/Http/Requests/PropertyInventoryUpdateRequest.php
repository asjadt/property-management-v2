<?php




namespace App\Http\Requests;

use App\Models\PropertyInventory;
use Illuminate\Foundation\Http\FormRequest;

class PropertyInventoryUpdateRequest extends FormRequest
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

      $property_inventory_query_params = [
          "id" => $this->id,
      ];
      $property_inventory = PropertyInventory::where($property_inventory_query_params)
          ->first();
      if (!$property_inventory) {
          // $fail($attribute . " is invalid.");
          $fail("no property inventory found");
          return 0;
      }

          if ($property_inventory->created_by != auth()->user()->id) {
        // $fail($attribute . " is invalid.");
        $fail("You do not have permission to update this property inventory due to role restrictions.");
    }





  },
],



    'item_name' => [
    'required',
    'string',







],

    'item_location' => [
    'required',
    'string',







],

    'item_quantity' => [
    'required',
    'integer',







],

    'item_condition' => [
    'required',
    'string',







],

    'item_details' => [
    'required',
    'string',

],

    'property_id' => [
    'required',
    'numeric',
    'exists:properties,id'

],

    'files' => [
    'required',
    'array',







],







];



return $rules;
}
}



