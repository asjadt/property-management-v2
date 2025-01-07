<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TenancyAgreementUpdateRequest extends FormRequest
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
            "id"=> "required|numeric|exists:tenancy_agreements,id",
            'property_id' => 'required|exists:properties,id',
            'agreed_rent' => 'required|string|max:255',
            'security_deposit_hold' => 'required|string|max:255',
            'tenant_ids' => 'required|array|min:1',
            'tenant_ids.*' => 'numeric|exists:tenants,id',
            'rent_payment_option' => 'required|string|max:255',
            'tenant_contact_duration' => 'required|string|max:255',
            'date_of_moving' => 'required|date',
            'let_only_agreement_expired_date' => 'nullable|date',
            'tenant_contact_expired_date' => 'nullable|date',
            'rent_due_date' => 'required|date',
            'no_of_occupants' => 'required|string|max:255',
            'renewal_fee' => 'required|string|max:255',
            'housing_act' => 'required|string|max:255',
            'let_type' => 'required|string|max:255',
            'terms_and_conditions' => 'nullable|string',
            'agency_name' => 'required|string|max:255',
            'landlord_name' => 'required|string|max:255',
            'agency_witness_name' => 'required|string|max:255',
            'tenant_witness_name' => 'required|string|max:255',
            'agency_witness_address' => 'required|string|max:255',
            'tenant_witness_address' => 'required|string|max:255',
            'guarantor_name' => 'nullable|string|max:255',
            'guarantor_address' => 'nullable|string|max:255',

            'files' => 'present|array',
            'files.*.file' => 'required|string',
            'files.*.description' => 'nullable|string',
        ];
    }
}
