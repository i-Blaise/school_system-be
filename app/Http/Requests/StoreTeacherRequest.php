<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeacherRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only admins can create teachers
        return $this->user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $isDraft = $this->input('registration_status', 'completed') === 'draft';

        // Draft mode: nearly everything is optional so the admin can save partial progress
        // Active mode: core fields are required to formally register the teacher
        $requiredIfActive = $isDraft ? 'nullable' : 'required';

        return [
            // Registration Status
            'registration_status'   => 'required|in:draft,completed',

            // Employment Status
            'status'                => 'nullable|string|in:Active,Inactive,Leave',

            // Personal Information
            'full_name'             => "{$requiredIfActive}|string|max:255",
            'date_of_birth'         => 'nullable|date|before:today',
            'gender'                => "{$requiredIfActive}|string|in:male,female",
            'profile_photo'         => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

            // Contact Information
            'email'                 => "{$requiredIfActive}|email|max:255",
            'phone_country_code'    => 'nullable|string|max:10',
            'phone'                 => 'nullable|string|max:30',
            'address'               => 'nullable|string|max:1000',

            // Professional Info
            'department'            => 'nullable|string|max:255',
            'designation'           => 'nullable|string|max:255',
            'joining_date'          => 'nullable|date',
            'qualification'         => 'nullable|string|max:255',
            'specialization'        => 'nullable|string|max:255',

            // Health Info
            'medical_condition_alert'   => 'nullable|boolean',
            'medical_condition_details' => 'nullable|required_if:medical_condition_alert,true|string|max:2000',

            // Emergency Contact
            'emergency_contact'                     => 'nullable|array',
            'emergency_contact.name'                => 'nullable|string|max:255',
            'emergency_contact.relation'            => 'nullable|string|max:255',
            'emergency_contact.phone_country_code'  => 'nullable|string|max:10',
            'emergency_contact.phone'               => 'nullable|string|max:30',
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'full_name.required'    => 'The teacher\'s full name is required.',
            'email.required'        => 'An email address is required for active teachers.',
            'gender.required'       => 'Please select a gender.',
            'profile_photo.max'     => 'The profile photo must not exceed 2MB.',
            'profile_photo.mimes'   => 'The profile photo must be a JPG or PNG file.',
        ];
    }
}
