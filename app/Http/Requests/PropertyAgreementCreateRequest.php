<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PropertyAgreementCreateRequest extends FormRequest
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
            'landlord_id' => 'required|exists:landlords,id',
            'property_id' => 'required|exists:properties,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'payment_arrangement' => 'nullable|in:By_Cash,By_Cheque,Bank_Transfer',
            'cheque_payable_to' => 'nullable|string',
            'agent_commission' => 'nullable|numeric|min:0',
            'terms_conditions' => 'nullable|string',
            'legal_representative' => 'nullable|string|max:255',
    'min_price' => 'nullable|numeric|min:0',
    'max_price' => 'nullable|numeric|min:0|gt:min_price', // Ensures max_price is greater than min_price if provided
    'agency_type' => 'nullable|string|max:255',
    'type' => 'nullable|in:let_property,manage_property,sale_property',

        ];
    }

    public function messages()
    {
        return [
            'payment_arrangement.required' => 'The payment arrangement is required.',
            'payment_arrangement.in' => 'The payment arrangement must be one of the following: By_Cash, By_Cheque, Bank_Transfer.',
            'type.in' => 'The type must be one of the following: let_property, manage_property, sale_property.',
        ];
    }
}
