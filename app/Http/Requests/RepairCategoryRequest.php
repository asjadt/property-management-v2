<?php

namespace App\Http\Requests;

use App\Rules\ValidateRepairCategory;
use Illuminate\Foundation\Http\FormRequest;

class RepairCategoryRequest extends FormRequest
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
            'name'=>"required|string",
            'icon'=>"required|string",
        ];

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['id'] = ['required', 'numeric', new ValidateRepairCategory()];
        }

        return $rules;
    }
}
