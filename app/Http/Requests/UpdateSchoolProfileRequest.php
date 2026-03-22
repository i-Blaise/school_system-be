<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSchoolProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'logo_url' => ['nullable', 'string', 'url'],
            'description' => ['nullable', 'string'],
            'awards' => ['nullable', 'array'],
            'awards.*' => ['string', 'max:255'],
            'locations' => ['nullable', 'array'],
            'locations.*.address' => ['required_with:locations', 'string'],
            'locations.*.is_primary' => ['required_with:locations', 'boolean'],
        ];
    }
}
