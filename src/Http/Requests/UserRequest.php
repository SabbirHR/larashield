<?php
namespace Larashield\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation()
    {
        $this->merge([
            'user_type' => config('setup-config.admin.user_type'),
            'phone' => '+88' . substr($this->phone, -11),
        ]);
    }

    public function rules(): array
    {
        switch ($this->method()) {
            case 'GET': return [];
            case 'POST':
                return [
                    'image'=>'image|mimes:jpeg,png,jpg,gif|max:2048',
                    'name'=>'required|string|max:255',
                    'email'=>'required|email|unique:users|max:255',
                    'phone'=>'required|numeric|regex:/^(?:\+?88)?01[3-9]\d{8}$/|unique:users,phone',
                    'gender'=>'required|in:male,female,other',
                    'password'=>'required|string|min:6|confirmed',
                    'status'=>'boolean',
                    'email_verified_at'=>'nullable|date',
                    'role'=>['required','not_in:superadmin,b2b,b2c','exists:roles,name'],
                    'permissions'=>'array',
                    'permissions.*'=>'distinct|exists:permissions,name'
                ];
            case 'PUT':
            case 'PATCH':
                $id = $this->route('user') ? $this->route('user')->id : null;
                return [
                    'name'=>'required|string|max:255',
                    'email'=>'required|email|max:255|unique:users,email,' . $id,
                    'phone'=>['required','numeric','regex:/^(?:\+?88)?01[3-9]\d{8}$/','unique:users,phone,' . $id],
                    'gender'=>'required|in:male,female,other',
                    'user_type'=>'nullable|in:sadmin,admin,b2b,b2c',
                    'password'=>'nullable|string|min:6|confirmed',
                    'status'=>'sometimes|boolean',
                    'email_verified_at'=>'nullable|date',
                    'role'=>['nullable','not_in:superadmin,b2b,b2c','exists:roles,name'],
                    'permissions'=>'array',
                    'permissions.*'=>'distinct|exists:permissions,name'
                ];
            case 'DELETE': return [];
            default: return [];
        }
    }
}
