<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class AddLoadRequest extends FormRequest
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
            'title' => 'required|max:255',
            'pickup_datetime' => 'required|date',
            'pickup_location' => 'required|string|min:5',
            'dropout_location' => 'required|string|min:5',
            'length' => 'required|numeric',
            'width' => 'required|numeric',
            'height' => 'required|numeric',
            'weight' => 'required|numeric',
            'cost' => 'required|numeric'
        ];
    }
    public function messages()
    {
        return [
            'title.required' => 'Please enter title',
            'pickup_datetime.required' => 'Please enter pickup date',
            'pickup_datetime.date' => 'Please enter valid date for pickup',
            'pickup_location.required' => 'Please enter pickup location',
            'pickup_location.min' => 'Please enter minimum 5 characters for pickup location',
            'dropout_location.required' => 'Please enter dropout location',
            'dropout_location.min' => 'Please enter minimum 5 characters for dropout location',
            'length.required' => 'Please enter length of load',
            'length.numeric' => 'Please enter only digits for load length',
            'width.required' => 'Please enter width of load',
            'width.numeric' => 'Please enter only digits for load width',
            'height.required' => 'Please enter height of load',
            'height.numeric' => 'Please enter only digits for load height',
            'weight.required' => 'Please enter weight of load',
            'weight.numeric' => 'Please enter only digits for load weight',
            'cost.required' => 'Please enter cost of load',
            'cost.numeric' => 'Please enter only digits for load cost',
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        $errors = (new ValidationException($validator))->errors();
        throw new HttpResponseException(response()->json([ 'status'  => JsonResponse::HTTP_UNPROCESSABLE_ENTITY,'errors' => $errors
        ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY));
    }
}
