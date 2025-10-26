<?php

namespace App\Http\Requests;

use App\Rules\ValidateInventoryItem;
use Illuminate\Foundation\Http\FormRequest;

class InventoryItemRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ];

        if ($this->isMethod('patch') || $this->isMethod('put')) {
            $rules['id'] = [
                'required',
                'integer',
                new ValidateInventoryItem(),
            ];
        }

        return $rules;
    }
}
