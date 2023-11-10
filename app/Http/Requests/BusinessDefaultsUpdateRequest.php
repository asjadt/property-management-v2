<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BusinessDefaultsUpdateRequest extends FormRequest
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
            "business_id" => "required|numeric",
            "bill_items" => "present|array",
            "bill_items.*.bill_item_id" => "required|numeric|exists:bill_items,id",



            "sale_items" => "present|array",
            "sale_items.*.sale_id" => "nullable|numeric|exists:sale_items,id",
            "sale_items.*.item" => "required|string",
            "sale_items.*.description" => "nullable|string",
            "sale_items.*.amount" => "required|numeric",
        ];
    }
}
