<?php
namespace App\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductCategoryRequest extends FormRequest
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
        $id = isset($this->segments()[3])? $this->segments()[3] : '';

        //todo check the parent id valitions on empty
        return [
            //'parent_id' => 'sometimes|present|exists:product_categories,id',
            'name' => ['required','min:3','max:255', Rule::unique('product_categories', 'name')->ignore($id),],
            'code' => 'sometimes|present|min:3|max:20',
            'active' => 'sometimes|required|boolean'
        ];
    }
}
