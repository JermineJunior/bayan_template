<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
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
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
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
            'username.required' => 'حقل اسم المستخدم مطلوب.',
            'password.required' => 'حقل كلمة المرور مطلوب.',
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * A deactivated account is rejected with its own clear message even when
     * the credentials are correct. The password is still verified first (via
     * Auth::validate, which does not create a session) so the "deactivated"
     * message never reveals whether a username exists on its own.
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $credentials = $this->only('username', 'password');

        $user = User::where('username', $this->string('username'))->first();

        if ($user !== null && ! $user->is_active && Auth::validate($credentials)) {
            throw ValidationException::withMessages([
                'username' => 'تم تعطيل هذا الحساب. يرجى التواصل مع المسؤول.',
            ]);
        }

        if (! Auth::attempt($credentials, $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'username' => 'بيانات الاعتماد هذه لا تطابق سجلاتنا.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'username' => str_replace(
                ':seconds',
                $seconds,
                'محاولات تسجيل دخول كثيرة جدًا. يرجى المحاولة مرة أخرى بعد :seconds ثانية.',
            ),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('username')).'|'.$this->ip());
    }
}
