<?php

namespace App\Livewire\Forms;

use App\Models\Student;
use App\Services\StudentAccountService;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class LoginForm extends Form
{
    /**
     * Either an email address (staff) or a Foundation Number (students).
     */
    #[Validate('required|string')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    #[Validate('boolean')]
    public bool $remember = false;

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if ($this->isEmailAddress()) {
            $attempted = Auth::attempt($this->only(['email', 'password']), $this->remember);
        } else {
            $attempted = $this->attemptWithFoundationNumber();
        }

        if (! $attempted) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'form.email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    private function isEmailAddress(): bool
    {
        return filter_var($this->email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Students sign in with their Foundation Number. The portal account is
     * provisioned lazily so every registered student can sign in with the
     * default password without any manual setup.
     */
    private function attemptWithFoundationNumber(): bool
    {
        $student = Student::withTrashed()
            ->whereRaw('LOWER(foundation_number) = ?', [Str::lower(trim($this->email))])
            ->first();

        if (! $student || $student->trashed()) {
            return false;
        }

        $user = app(StudentAccountService::class)->ensureFor($student);

        if (! Hash::check($this->password, $user->password)) {
            return false;
        }

        Auth::login($user, $this->remember);

        return true;
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'form.email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }
}
