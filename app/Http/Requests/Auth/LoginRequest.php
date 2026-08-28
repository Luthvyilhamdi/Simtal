<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nik' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('nik', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            // Ditulis langsung, bukan trans('auth.failed'), sebab locale aplikasi
            // masih 'en' sehingga berkas bahasa bawaan Laravel akan memberi
            // pesan berbahasa Inggris di halaman login yang seluruhnya Indonesia.
            // Sengaja tidak menyebut mana yang salah (NIK atau password) —
            // itu praktik keamanan standar agar akun tidak bisa ditebak.
            throw ValidationException::withMessages([
                'nik' => 'NIK atau password tidak sesuai. Periksa kembali dan coba lagi.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'nik' => $seconds >= 60
                ? 'Terlalu banyak percobaan masuk. Silakan coba lagi dalam ' . ceil($seconds / 60) . ' menit.'
                : 'Terlalu banyak percobaan masuk. Silakan coba lagi dalam ' . $seconds . ' detik.',
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('nik')).'|'.$this->ip());
    }
}
