<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\PropertyStatus;

class PropertyUpdateRequestV2 extends FormRequest
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

            'id' => 'required|integer|exists:properties,id',
            'name' => "nullable|string",
            'image' => "nullable|string",
            'images' => "present|array",
            'images.*' => "string",
            'address' => "nullable|string",
            'country' => "required|string",
            'city' => "required|string",
            'postcode' => "required|string",
            'town' => "nullable|string",
            'lat' => 'nullable|numeric',
            'long' => 'nullable|numeric',

            // NEW ID FIELDS
            'property_type_id' => ['required', 'integer', new \App\Rules\ValidatePropertyType()],
            'bed_id' => ['required', 'integer', new \App\Rules\ValidateBed()],
            'bath_id' => ['required', 'integer', new \App\Rules\ValidateBath()],

            'reference_no' => 'required|string|max:255',
            'current_status' => 'nullable|string|in:' . implode(',', PropertyStatus::values()),
            'is_active' => 'nullable|boolean',



            'tenant_ids' => 'nullable|array',
            'tenant_ids.*' => 'nullable|exists:tenants,id',

            'landlord_ids' => 'present|array',
            'landlord_ids.*' => 'numeric|exists:landlords,id',

            // Added fields from Software 2
            'date_of_instruction' => 'nullable|date',
            'howDetached' => 'nullable|string',
            'is_garden' => 'required|boolean',
            'propertyFloor' => 'nullable|string',
            'category' => 'required|in:let_property,manage_property,sale_property',
            'price' => 'nullable|numeric',
            'purpose' => 'nullable|string',
            'property_door_no' => 'nullable|string',
            'property_road' => 'nullable|string',
            'county' => 'nullable|string',
            'is_dss' => 'nullable|required_if:category,let_property,manage_property|boolean',
            'maintenance_item_type_ids' => 'present|array',
            'maintenance_item_type_ids.*' => 'nullable|exists:maintenance_item_types,id',

            //
            "min_price" => "nullable|numeric",
            "max_price" => "nullable|numeric|gt:min_price",
        ];
    }
    public function messages()
    {
        return [

            'category.in' => 'The type must be one of the following: let_property, manage_property, sale_property.',
        ];
    }
}
