<?php

namespace App\Http\Requests;

use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
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
            'token' => 'required|exists:forgot_password',
            'email' => 'required|email',
            'password' => 'required|confirmed|between:6,25'
        ];
    }
    public function messages()
    {
        return [
            'token.required' => 'Invalid/empty token provided',
            'token.exists' => 'Your requested token expired or invalid',
            'email.email' => 'Provided email address is invalid',
            'email.required' => 'No email address provided',
            'password.required' => 'Please enter password',
            'password.confirmed' => 'Please enter same password for confirm password',
            'password.between' => 'Password should be between 6 to 25 characters',
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        $errors = (new ValidationException($validator))->errors();
        throw new HttpResponseException(response()->json([ 'status'  => JsonResponse::HTTP_UNPROCESSABLE_ENTITY,'errors' => $errors
        ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY));
    }
}
