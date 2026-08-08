<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ValidateMaintenanceItemType;

class MaintenanceItemTypeRequest extends FormRequest
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
        $rules = [
            'name' => [
                'required',
                'string',
            ],
        ];

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['id'] = [
                'required',
                'numeric',
                new ValidateMaintenanceItemType()
            ];
        }

        return $rules;
    }
}
