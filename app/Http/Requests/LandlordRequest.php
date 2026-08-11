<?php

namespace App\Http\Requests;

use App\Models\Landlord;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LandlordRequest extends FormRequest
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
        $isUpdate = $this->isMethod('put') || $this->isMethod('patch');

        $rules = [
            'first_Name'     => 'required|string|max:255',
            'last_Name'      => 'required|string|max:255',
            'phone'          => 'nullable|string',
            'image'          => 'nullable|string',
            'address_line_1' => 'nullable|string',
            'address_line_2' => 'nullable|string',
            'lat'            => 'nullable|string',
            'long'           => 'nullable|string',
            'files'          => 'present|array',
            'files.*.file'   => 'required|string',
            'files.*.description' => 'nullable|string',
        ];

        if ($isUpdate) {
            $rules['id']       = 'required|numeric';
            $rules['country']  = 'required|string';
            $rules['city']     = 'required|string';
            $rules['postcode'] = 'required|string';

            // DYNAMIC TABLE NAME — no hardcoded strings; ignore current record's own email in both tables
            $rules['email'] = [
                'required', 'string', 'email', 'max:255',
                Rule::unique((new Landlord)->getTable(), 'email')->ignore($this->id),
                Rule::unique((new User)->getTable(), 'email')->ignore(
                    Landlord::where('id', $this->id)->value('user_id')
                ),
            ];
        } else {
            $rules['country']  = 'nullable|string';
            $rules['city']     = 'nullable|string';
            $rules['postcode'] = 'nullable|string';

            // DYNAMIC TABLE NAME — no hardcoded strings
            $rules['email'] = [
                'required', 'string', 'email', 'max:255',
                Rule::unique((new Landlord)->getTable(), 'email'),
                Rule::unique((new User)->getTable(), 'email'),
            ];
        }

        return $rules;
    }
}
