<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
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
        $role = $this->route('role');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')
                    ->where('guard_name', 'web')
                    ->ignore($role?->id),
            ],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => [
                'required',
                'string',
                // config/permissions.php is the single source of truth for the
                // permission catalogue — validating against it (rather than the
                // permissions table) lets newly-added catalogue permissions be
                // selected before the seeder has run.
                Rule::in(collect(config('permissions'))->flatten()->unique()->values()->all()),
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
            'name.required' => 'اسم الدور مطلوب.',
            'name.max' => 'يجب ألا يتجاوز اسم الدور :max حرفًا.',
            'name.unique' => 'يوجد دور بهذا الاسم بالفعل.',
            'permissions.*' => 'واحدة أو أكثر من الصلاحيات المحددة غير صالحة.',
        ];
    }
}
