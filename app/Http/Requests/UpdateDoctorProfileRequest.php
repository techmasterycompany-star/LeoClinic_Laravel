<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDoctorProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'specialty_id' => 'sometimes|exists:specialties,id',
            'price' => 'sometimes|numeric|min:0',
            'bio' => 'sometimes|nullable|string',
            'contact_number' => 'sometimes|nullable|string|max:20',

            'profile_image' => 'sometimes|nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }
}
