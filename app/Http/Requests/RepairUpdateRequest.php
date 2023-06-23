<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RepairUpdateRequest extends FormRequest
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
            'id'=> "required|numeric|exists:repairs,id",
            'property_id'=> "required|numeric|exists:properties,id",
            'repair_category'=>"required|string",
            'item_description'=>"nullable|string",
            'receipt'=>"required|string",
            'price'=>"required|numeric",
            'create_date'=>"required|date",
            'images' => 'nullable|array',
            'images.*' => 'nullable|string',
        ];
    }
}
