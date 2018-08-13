<?php
namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class EmailVerificationRequest extends FormRequest
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
            'verification_email' => 'required|email|unique:users,email'
        ];
    }

    /**
     * format the messages to be more meaningful
     *
     * @return array
     */
    public function messages()
    {
        return [
            'verification_email.unique' => trans('message.user.email_unique'),
        ];
    }
}
