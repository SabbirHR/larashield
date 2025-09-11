<?php

namespace Larashield\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class RoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    protected function prepareForValidation()
    {
        // Add user_id to the request data before validation
        $this->merge(['guard_name' => 'web']);
        // DEBUG: log all request input
        Log::info('🔍 RoleRequest Input:', $this->all());

        // DEBUG: log route parameter
        Log::info('🔍 RoleRequest Route Parameterssss:', ['role' => $this->route('role')]);
    }
    public function rules(): array
    {
        switch ($this->method()) {
            case 'GET': // Validation rules for the index method
                return [];

            case 'POST': // Validation rules for the store method

                return [
                    'name' => 'required|unique:roles|max:50',
                    'permissions' => 'array', // Ensures permissions is an array
                    'permissions.*' => [
                        'exists:permissions,name',
                        Rule::notIn(config('setup-config.protected_permissions')), // Prevent protected permissions
                    ],
                    'guard_name' => 'required|in:web',
                ];


            case 'PUT':
            case 'PATCH': // Validation rules for the update method
                $role = $this->route('role');
                if (is_string($role)) {
                    $role = Role::findOrFail($role);
                }

                return [
                    'name' => 'required|max:50|unique:roles,name,' . $role->id,
                    'permissions' => 'array',
                    'permissions.*' => [
                        'exists:permissions,name',
                        Rule::notIn(config('setup-config.protected_permissions')),
                    ],
                    'guard_name' => 'required|in:web',
                ];


            case 'DELETE': // Validation rules for the delete method (if needed)
                return [
                    // Add any validation rules specific to the delete method here
                ];

            default:
                return [];
        }
    }
}
