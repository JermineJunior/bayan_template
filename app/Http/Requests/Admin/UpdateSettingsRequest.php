<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
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
            'app_name' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:2048'],
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
            'app_name.required' => 'حقل اسم التطبيق مطلوب.',
            'app_name.max' => 'يجب ألا يتجاوز اسم التطبيق :max حرفًا.',
            'logo.image' => 'يجب أن يكون الشعار صورة صالحة.',
            'logo.max' => 'يجب ألا يتجاوز حجم الشعار 2 ميجابايت.',
        ];
    }
}
