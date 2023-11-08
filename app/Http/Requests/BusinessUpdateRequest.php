<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BusinessUpdateRequest extends FormRequest
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
            'user.id' => 'required|numeric|exists:users,id',
            'user.first_Name' => 'required|string|max:255',
            'user.last_Name' => 'required|string|max:255',

            'user.email' => 'required|string|unique:users,email,' . $this->user["id"] . ',id',

            'user.password' => 'nullable|confirmed|string|min:6',
            'user.phone' => 'nullable|string',
            'user.image' => 'nullable',

            'business.id' => 'required|numeric|required|exists:businesses,id',
            'business.name' => 'required|string|max:255',
            'business.about' => 'nullable|string',
            'business.web_page' => 'nullable|string',
            'business.phone' => 'nullable|string',

            'business.email' => 'required|string|unique:businesses,email,' . $this->business["id"] . ',id',
            'business.additional_information' => 'nullable|string',


            'business.lat' => 'nullable|string',
            'business.long' => 'nullable|string',
            'business.country' => 'nullable|string',
            'business.city' => 'nullable|string',
            'business.postcode' => 'nullable|string',
            'business.address_line_1' => 'required|string',
            'business.address_line_2' => 'nullable|string',


            'business.logo' => 'nullable|string',
            'business.image' => 'nullable|string',

            "business.invoice_title" => "nullable|string",
            "business.footer_text" => "nullable|string",
            "business.is_reference_manual" => "required|boolean",
            "business.receipt_footer" => "nullable|string",

            "business.account_name" => "nullable|string",
            "business.account_number" => "nullable|string",
            "business.sort_code" => "nullable|string",
            "business.pin" => "required|string",
            "business.type" => "required|string|in:other,property_dealer",

            // 'business.images' => 'nullable|array',
            // 'business.images.*' => 'nullable|string',


            // "bill_items" => "required|array",
            // "bill_items.*.bill_item_id" => "required|numeric|exists:bill_items,id",



            // "sale_items" => "required|array",
            // "sale_items.*.sale_id" => "nullable|numeric|exists:sale_items,id",
            // "sale_items.*.item" => "required|string",
            // "sale_items.*.description" => "nullable|string",
            // "sale_items.*.amount" => "required|numeric",







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
            // 'user.password.confirmed' => $this->customRequiredMessage("password"),




            'business.name.required' => $this->customRequiredMessage("business name"),
            'business.email.required' => $this->customRequiredMessage("business email"),
            'business.country.required' => $this->customRequiredMessage("business country"),
            'business.city.required' => $this->customRequiredMessage("business city"),
            'business.postcode.required' => $this->customRequiredMessage("business postcode"),
            'business.address_line_1.required' => $this->customRequiredMessage("business address line 1"),



        ];
    }
}
