<?php




namespace App\Http\Requests;

use App\Models\MaintenanceItemType;
use Illuminate\Foundation\Http\FormRequest;

class MaintenanceItemTypeUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return  bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return  array
     */
    public function rules()
    {

        $rules = [

            'id' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) {

                    $maintenance_item_type_query_params = [
                        "id" => $this->id,
                        "created_by" => auth()->user()->id
                    ];
                    $maintenance_item_type = MaintenanceItemType::where($maintenance_item_type_query_params)
                        ->first();
                    if (!$maintenance_item_type) {
                        // $fail($attribute . " is invalid.");
                        $fail("no maintenance item type found");
                        return 0;
                    }



                },
            ],



            'name' => [
                'required',
                'string',
            ],

        ];



        return $rules;
    }
}
