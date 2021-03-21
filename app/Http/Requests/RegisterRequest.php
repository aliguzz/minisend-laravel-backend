<?php
namespace App\Http\Requests;

use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'name' => 'required|max:255',
            'email' => 'required|email|string|unique:users',
            'password' => 'required|between:6,25',
            'account_type' => 'required'
        ];
    }
    public function messages()
    {
        return [
            'email.required' => 'Please enter email address',
            'email.email' => 'Please enter a valid email address',
            'email.unique' => 'Email you entered is already taken',
            'name.required' => 'Please enter your name',
            'password.required' => 'Please enter password, it should be between 6 to 25 characters',
            'account_type' => 'Please select account type'
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        $errors = (new ValidationException($validator))->errors();
        throw new HttpResponseException(response()->json([ 'status'  => JsonResponse::HTTP_UNPROCESSABLE_ENTITY,'errors' => $errors
        ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY));
    }
}
