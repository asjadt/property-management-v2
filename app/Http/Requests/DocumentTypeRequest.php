<?php

namespace App\Http\Requests;

use App\Rules\ValidDocumentType;
use Illuminate\Foundation\Http\FormRequest;

class DocumentTypeRequest extends FormRequest
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
            "name" => "required|string",
            "icon" => "nullable|string",
            "description" => "nullable|string",
            "is_active" => "nullable|boolean"
        ];

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules["id"] = ["required", 'integer', new ValidDocumentType()];
        }

        return $rules;
    }
}
