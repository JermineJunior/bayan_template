<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9_.-]+$/',
                Rule::unique('users', 'username')->ignore($user?->id),
            ],
            'email' => ['nullable', 'email', 'max:255'],
            'role_id' => [
                'required',
                'integer',
                Rule::exists('roles', 'id')->where('guard_name', 'web'),
            ],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'حقل الاسم مطلوب.',
            'name.max' => 'يجب ألا يتجاوز الاسم :max حرفًا.',
            'username.required' => 'حقل اسم المستخدم مطلوب.',
            'username.max' => 'يجب ألا يتجاوز اسم المستخدم :max حرفًا.',
            'username.unique' => 'اسم المستخدم مستخدم بالفعل.',
            'username.regex' => 'اسم المستخدم يجب أن يحتوي على أحرف إنجليزية وأرقام و _ و - و . فقط.',
            'email.email' => 'يرجى إدخال بريد إلكتروني صالح.',
            'email.max' => 'يجب ألا يتجاوز البريد الإلكتروني :max حرفًا.',
            'role_id.required' => 'يرجى اختيار دور للمستخدم.',
            'role_id.exists' => 'الدور المحدد غير صالح.',
        ];
    }
}
