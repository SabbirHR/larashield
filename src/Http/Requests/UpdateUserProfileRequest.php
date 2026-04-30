<?php

namespace Larashield\Http\Requests;

use Larashield\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        if ($this->phone) {
            $this->merge([
                'phone' => '+88' . substr($this->phone, -11), // Normalize phone number to include country code
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'email' => $this->emailRules(),
            'phone' => $this->phoneRules(),
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'gender' => 'nullable|in:male,female,other',
        ];
    }

    /**
     * Define the validation rules for the email field.
     */
    private function emailRules(): array|string
    {
        $currentEmail = $this->user()->email; // Get the current email from the authenticated user
        $userType = $this->user()->user_type ?? 'b2c';
        if ($userType === 'b2c') {
            return ['nullable', 'email'];
        }

        if (is_null($currentEmail)) {
            // Allow email updates if the current email is null
            return ['email', 'email', 'unique:users,email,' . $this->user()->id];
        }
        // If the email is already set, don't allow changes to it but still allow updating other fields
        if ($currentEmail && $this->email && $this->email !== $currentEmail) {
            return ['prohibited'];
        }

        return []; // No validation needed if the email is not being updated
    }

    /**
     * Define the validation rules for the phone field.
     */
    private function phoneRules(): array|string
    {
        $currentPhone = $this->user()->phone; // Get the current phone from the authenticated user

        if (is_null($currentPhone)) {
            // Allow phone updates if the current phone is null
            return [
                'required',
                'numeric',
                'regex:/^(?:\+?88)?01[3-9]\d{8}$/',
                function ($attribute, $value, $fail) {
                    $existingUser = User::where('phone', $value)->first();
                    if ($existingUser) {
                        $fail("The phone number is already in use by another user.");
                    }
                },
            ];
        }
        // If the phone is already set, don't allow changes to it but still allow updating other fields
        if ($currentPhone && $this->phone && $this->phone !== $currentPhone) {
            return ['prohibited'];
        }

        return []; // No validation needed if the phone is not being updated
    }

    /**
     * Custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.prohibited' => 'Your email address is already set and cannot be changed.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address is already in use. Please choose a different one.',
            'phone.prohibited' => 'Your phone number is already set and cannot be changed.',
            'phone.required' => 'A phone number is required.',
            'phone.numeric' => 'The phone number must contain only numbers.',
            'phone.regex' => 'The phone number must be a valid Bangladeshi number (e.g., +8801XXXXXXXXX).',
            'phone.string' => 'The phone number must be a valid string.',
            'phone.max' => 'The phone number must not exceed 20 characters.',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'The image must be in one of the following formats: jpeg, png, jpg, gif.',
            'image.max' => 'The image size must not exceed 2 MB.',
            'gender.in' => 'The gender must be one of: male, female, or other.',
        ];
    }
}
