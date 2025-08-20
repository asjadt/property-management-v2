<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExpenseUpdateRequest extends FormRequest
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
            'id'=> "required|numeric|exists:expenses,id",
            "payment_method" => "required|string",
            'property_id'=> "nullable|numeric|exists:properties,id",
            'expense_category_id'=>"required|numeric|exists:expense_categories,id",
            "paid_by" => "required|string|in:landlord,agent",
            'item_description'=>"nullable|string",
            'status'=>"required|string",
            'price'=>"required|numeric",
            'create_date'=>"required|date",
            'images' => 'nullable|array',
            'images.*' => 'nullable|string',
            'receipt' => 'nullable|array',
            'receipt.*' => 'nullable|string',
        ];
    }
     public function message() {
        return [
            "paid_by.in" => "Paid by must be landlord or agent"
        ];
    }
}
