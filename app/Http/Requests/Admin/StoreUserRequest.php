<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username'),
            ],
            'email' => ['nullable', 'email', 'max:255'],
            'password' => ['nullable', 'string', 'min:4'],
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
            'password.min' => 'يجب ألا تقل كلمة المرور عن :min أحرف.',
            'role_id.required' => 'يرجى اختيار دور للمستخدم.',
            'role_id.exists' => 'الدور المحدد غير صالح.',
        ];
    }
}
