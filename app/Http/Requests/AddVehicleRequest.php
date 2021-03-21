<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class AddVehicleRequest extends FormRequest
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
            'vehicle_name' => 'required|max:255',
            'plate_number' => 'required|string|unique:vehicle,id,'.$this->v_id,
            'license_number' => 'required|string|unique:vehicle,id,'.$this->v_id,
            'photo' => 'required'
        ];
    }
    public function messages()
    {
        return [
            'vehicle_name.required' => 'Please select vehicle type',
            'plate_number.required' => 'Please enter plate number',
            'plate_number.unique' => 'Plate number you entered already exists',
            'license_number.required' => 'Please enter license number',
            'license_number.unique' => 'License number you entered already exists',
            'photo.required' => 'Please select a photo for vehicle'
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        $errors = (new ValidationException($validator))->errors();
        throw new HttpResponseException(response()->json([ 'status'  => JsonResponse::HTTP_UNPROCESSABLE_ENTITY,'errors' => $errors
        ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY));
    }
}
