<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        
        return $this->user()->patientProfile === null;
    }

    public function rules(): array
    {
        return [
            'contact_number' => ['required', 'string', 'max:20'],
            'date_of_birth'  => ['required', 'date', 'before:today'],
            'address'        => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'contact_number.required' => 'رقم التليفون مطلوب',
            'date_of_birth.before'    => 'تاريخ الميلاد لازم يكون قبل النهاردة',
        ];
    }
}