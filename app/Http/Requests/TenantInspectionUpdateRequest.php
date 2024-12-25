<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TenantInspectionUpdateRequest extends FormRequest
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
            'id' => 'required|numeric|exists:tenant_inspections,id',
            'tenant_id' => 'required|numeric|exists:tenants,id',
            'address_line_1' => 'required|string|max:255',
            'inspected_by' => 'required|string|max:255',
            'phone' => 'required|string|max:20', // Assuming a phone number format
            'date' => 'required|date',
            'garden' => 'nullable|string|max:255',
            'entrance' => 'nullable|string|max:255',
            'exterior_paintwork' => 'nullable|string|max:255',
            'windows_outside' => 'nullable|string|max:255',
            'kitchen_floor' => 'nullable|string|max:255',
            'oven' => 'nullable|string|max:255',
            'sink' => 'nullable|string|max:255',
            'lounge' => 'nullable|string|max:255',
            'downstairs_carpet' => 'nullable|string|max:255',
            'upstairs_carpet' => 'nullable|string|max:255',
            'window_1' => 'nullable|string|max:255',
            'window_2' => 'nullable|string|max:255',
            'window_3' => 'nullable|string|max:255',
            'window_4' => 'nullable|string|max:255',
            'windows_inside' => 'nullable|string|max:255',
            'wc' => 'nullable|string|max:255',
            'shower' => 'nullable|string|max:255',
            'bath' => 'nullable|string|max:255',
            'hand_basin' => 'nullable|string|max:255',
            'smoke_detective' => 'nullable|string|max:255',
            'general_paintwork' => 'nullable|string|max:255',
            'carbon_monoxide' => 'nullable|string|max:255',
            'overall_cleanliness' => 'nullable|string|max:255',
            'comments' => 'nullable|string|max:1000',
        ];
    }
}
