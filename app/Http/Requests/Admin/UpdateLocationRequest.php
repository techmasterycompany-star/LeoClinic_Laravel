<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $location = $this->route('location');

        return [
            'name' => 'required|string|max:255|unique:locations,name,' . $location->id,
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
        ];
    }
}
