<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PropertyCreateRequestV2 extends FormRequest
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
            'name' => "nullable|string",
            'image' => "nullable|string",
            'images' => "present|array",
            'images.*' => "string",

            'documents' => "present|array",
            'documents.*.gas_start_date' => "required|date",
            'documents.*.gas_end_date' => "required|date",
            'documents.*.description' => "nullable|string",
            'documents.*.document_type_id' => "required|numeric|exists:document_types,id",
            'documents.*.files' => "required|array",
            'documents.*.files.*' => "string",


            'address' => "nullable|string",
            'country' => "required|string",
            'city' => "required|string",
            'postcode' => "required|string",
            'town' => "nullable|string",
            'lat' => 'nullable|numeric',
            'long' => 'nullable|numeric',
            'type' => "required|string",
            'reference_no' => 'required|string|max:255',



            'tenant_ids' => 'nullable|array',
            'tenant_ids.*' => 'nullable|exists:tenants,id',

            'landlord_ids' => 'present|array',
            'landlord_ids.*' => 'numeric|exists:landlords,id',

            // Added fields from Software 2
            'date_of_instruction' => 'nullable|date',
            'howDetached' => 'nullable|string',

            'no_of_beds' => 'required|string',

            'no_of_baths' => 'required|string',
            'is_garden' => 'required|boolean',

            'propertyFloor' => 'nullable|string',
            'category' => 'required|in:let_property,manage_property,sale_property',
            'min_price' => 'nullable|numeric',
            'max_price' => 'nullable|numeric',
            'purpose' => 'nullable|string',
            'property_door_no' => 'nullable|string',
            'property_road' => 'nullable|string',
            'county' => 'nullable|string',
            'is_dss' => 'nullable|required_if:category,let_property,manage_property|boolean',

            'maintenance_item_type_ids' => 'present|array',
            'maintenance_item_type_ids.*' => 'nullable|exists:maintenance_item_types,id',


        ];
    }
    public function messages()
    {
        return [

            'category.in' => 'The type must be one of the following: let_property, manage_property, sale_property.',
        ];
    }

}
