<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PropertyUpdateRequest extends FormRequest
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
            'id' => "required|numeric",
            'name'=>"nullable|string",
            'image'=>"nullable|string",
            'address'=>"nullable|string",
            'country'=>"required|string",
            'city'=>"required|string",
            'postcode'=>"required|string",
            'lat' => 'nullable|string',
            'long' => 'nullable|string',
            'type'=>"required|string",
            'size'=>"required|string",
            'amenities'=>"nullable|string",
            'reference_no' => 'required|string|max:255|unique:properties,reference_no',
            'landlord_id' => "nullable|numeric|exists:landlords,id",
            'tenant_ids' => 'nullable|array',
            'tenant_ids.*' => 'exists:tenants,id',
        ];
    }
}
