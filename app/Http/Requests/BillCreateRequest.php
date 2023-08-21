<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BillCreateRequest extends FormRequest
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
            "create_date"=>"required|date",
            "property_id"=>"required|numeric|exists:properties,id",
            "landlord_id"=>"nullable|numeric|exists:landlords,id",

            "payment_mode"=>"required|string",
            "payabble_amount"=>"required|numeric",
            "remarks"=>"nullable|string",

            "bill_items" => "required|array",
            "bill_items.*.bill_item_id" => "required|numeric|exists:bill_items,id",
            "bill_items.*.item" => "required|string",
            "bill_items.*.description" => "nullable|string",
            "bill_items.*.amount" => "required|numeric",


            "sale_items" => "required|array",
            "sale_items.*.sale_id" => "required|numeric|exists:sale_items,id",
            "sale_items.*.item" => "required|string",
            "sale_items.*.description" => "nullable|string",
            "sale_items.*.amount" => "required|numeric",


            "repair_items" => "required|array",
            "repair_items.*.repair_id" => "required|numeric|exists:repairs,id",
            "repair_items.*.item" => "required|string",
            "repair_items.*.description" => "nullable|string",
            "repair_items.*.amount" => "required|numeric",









        ];
    }
}
