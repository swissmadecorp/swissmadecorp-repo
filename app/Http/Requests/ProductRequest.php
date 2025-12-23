<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
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

    public function messages()
    {
        return [
            'p_serial.unique' => 'The serial number must be a unique number. Please correct the problem and try again.',
            'p_condition.not_in' => 'Product condition must be specified.'
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'p_category' => 'required',
            'p_condition' => 'required|not_in:0',
            'p_qty' => 'required',
            'supplier' => 'required',
            'p_serial' => [
                'required',
                Rule::unique('products')->where(function ($query) {
                    $query
                        ->where('category_id', $this->category_selected)
                        ->where('p_qty','=', 1)
                        ->where('p_serial', '<>', 'N/A');
                    
                })
            ],
            'p_price' => 'required',
            'p_color' => 'required'    
        ];
    }
}
