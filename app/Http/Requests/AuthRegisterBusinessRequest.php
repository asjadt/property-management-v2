<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AuthRegisterBusinessRequest extends FormRequest
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
            'user.first_Name' => 'required|string|max:255',
            'user.last_Name' => 'required|string|max:255',
            'user.email' => 'required|string|email|max:255|unique:users,email',
            'user.password' => 'required|confirmed|string|min:6',
            'user.phone' => 'nullable|string',
            'user.image' => 'nullable|string',


            'business.name' => 'required|string|unique:businesses,name',

            'business.about' => 'nullable|string',
            'business.web_page' => 'nullable|string',
            'business.phone' => 'nullable|string',

            'business.email' => 'required|string|email|max:255|unique:businesses,email',
            'business.additional_information' => 'nullable|string',

            'business.lat' => 'nullable|string',
            'business.long' => 'nullable|string',
            'business.country' => 'nullable|string',
            'business.city' => 'nullable|string',

            'business.currency' => 'nullable|string',

            'business.postcode' => 'nullable|string',
            'business.address_line_1' => 'required|string',
            'business.address_line_2' => 'nullable|string',


            'business.logo' => 'nullable|string',

            'business.image' => 'nullable|string',
            "business.invoice_title" => "nullable|string",

            "business.footer_text" => "nullable|string",
            "business.receipt_footer" => "nullable|string",

            "business.is_reference_manual" => "required|boolean",
            "business.receipt_footer" => "nullable|string",


            "business.account_name" => "nullable|string",
            "business.account_number" => "nullable|string",
            "business.send_email_alert" => "nullable|boolean",

            "business.sort_code" => "nullable|string",



            "business.pin" => "required|string",

            "business.type" => "required|string|in:other,property_dealer",



            "bill_items" => "present|array",
            "bill_items.*.bill_item_id" => "required|numeric|exists:bill_items,id",



            "sale_items" => "present|array",
            "sale_items.*.sale_id" => "nullable|numeric|exists:sale_items,id",
            "sale_items.*.item" => "required|string",
            "sale_items.*.description" => "nullable|string",
            "sale_items.*.amount" => "required|numeric",


















        ];

    }


    public function customRequiredMessage($property) {

        return "The ".$property." must be required";
    }

    public function messages()
    {

        return [
            'user.first_Name.required' => $this->customRequiredMessage("first name"),
            'user.last_Name.required' => $this->customRequiredMessage("last name"),
            'user.email.required' => $this->customRequiredMessage("email"),
            'user.password.required' => $this->customRequiredMessage("password"),




            'business.name.required' => $this->customRequiredMessage("business name"),
            'business.email.required' => $this->customRequiredMessage("business email"),
            'business.country.required' => $this->customRequiredMessage("business country"),
            'business.city.required' => $this->customRequiredMessage("business city"),
            'business.postcode.required' => $this->customRequiredMessage("business postcode"),
            'business.address_line_1.required' => $this->customRequiredMessage("business address line 1"),

        ];
    }

}
