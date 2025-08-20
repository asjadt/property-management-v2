<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExpenseCreateRequest extends FormRequest
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
            'property_id'=> "nullable|numeric|exists:properties,id",
             "payment_method" => "required|string",
            'expense_category_id'=>"required|numeric|exists:expense_categories,id",
            'item_description'=>"nullable|string",
            'status'=>"required|string",
            'price'=>"required|numeric",
            'create_date'=>"required|date",
            'receipt' => 'nullable|array',
            'receipt.*' => 'nullable|string',

            "paid_by" => "required|string|in:landlord,agent",


        ];
    }

    public function message() {
        return [
            "paid_by.in" => "Paid by must be landlord or agent"
        ];
    }
}
