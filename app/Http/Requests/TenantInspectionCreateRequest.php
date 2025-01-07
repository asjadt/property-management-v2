<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TenantInspectionCreateRequest extends FormRequest
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
            "property_id"=>"required|numeric|exists:properties,id",
            'tenant_id' => 'required|numeric|exists:tenants,id',  // Assuming 'tenants' table has the id column
            'address_line_1' => 'required|string|max:255',
            'inspected_by' => 'required|string|max:255',
            'phone' => 'required|string|max:20', // Assuming a phone number format
            'date' => 'required|date',
            'next_inspection_date' => 'required|date',

        'maintenance_items' => 'present|array',
        'maintenance_items.*.maintenance_item_type_id' => 'required|numeric|exists:maintenance_item_types,id',
        'maintenance_items.*.status' => 'required|in:good,average,dirty,na,work_required,resolved',
        'maintenance_items.*.comment' => 'nullable|string|max:1000',
        'maintenance_items.*.next_follow_up_date' => 'nullable|date',
        'comments' => 'nullable|string|max:1000',

        'files' => 'present|array',
        'files.*.file' => 'required|string',
        'files.*.description' => 'nullable|string',



        ];
    }

    public function messages()
    {
        return [

            'maintenance_items.*.status.in' => 'The status must be one of the following: good, average, dirty, na, work_required, resolved.',


        ];
    }
}
