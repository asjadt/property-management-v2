<?php


namespace App\Http\Requests;

use App\Rules\ValidateInventoryItem;
use App\Rules\ValidateInventoryLocation;
use Illuminate\Foundation\Http\FormRequest;



class PropertyInventoryCreateRequest extends FormRequest
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

            'inventory_item_id' => [
                'required',
                'numeric',
                new ValidateInventoryItem(),
            ],

            'inventory_location_id' => [
                'required',
                'numeric',
                new ValidateInventoryLocation(),
            ],

            'item_quantity' => [
                'required',
                'integer'
            ],

            'item_condition' => [
                'required',
                'string'
            ],

            'item_details' => [
                'required',
                'string'
            ],

            'property_id' => [
                'required',
                'numeric',
                'exists:properties,id'
            ],

            'files' => [
                'present',
                'array'
            ],


        ];



        return $rules;
    }
}
