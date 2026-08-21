<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    public function mount(): void
    {
        $user = Auth::user();

        if ($user && $user->hasAnyRole(['super_admin', 'programme_officer', 'director'])) {
            $this->redirect(route('admin.dashboard'), navigate: true);
        } elseif ($user && $user->isStudent()) {
            $this->redirect(route('student.dashboard'), navigate: true);
        }
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $user = Auth::user();
        $default = $user->hasAnyRole(['super_admin', 'programme_officer', 'director'])
            ? route('admin.dashboard', absolute: false)
            : route('student.dashboard', absolute: false);

        $this->redirectIntended(default: $default, navigate: true);
    }
}; ?>

<div>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login" class="space-y-5">
        <div class="space-y-2">
            <label for="email" class="flex items-center gap-2 text-sm font-medium text-slate-700">
                <i data-lucide="user-round" class="h-4 w-4 text-slate-400"></i>
                Email or Foundation Number
            </label>
            <x-text-input wire:model="form.email" id="email" type="text" name="email" placeholder="you@paau.edu.ng or your Foundation Number" required autofocus autocomplete="username" />
            <p class="text-xs text-slate-400">Students: sign in with the Foundation Number used during registration.</p>
            <x-input-error :messages="$errors->get('form.email')" />
        </div>

        <div class="space-y-2">
            <label for="password" class="flex items-center gap-2 text-sm font-medium text-slate-700">
                <i data-lucide="lock" class="h-4 w-4 text-slate-400"></i>
                Password
            </label>
            <x-text-input wire:model="form.password" id="password" type="password" name="password" placeholder="••••••••" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('form.password')" />
        </div>

        <button
            type="submit"
            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary-700 px-5 py-2.5 text-base font-semibold text-white shadow-soft transition-all duration-200 hover:bg-primary-800 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
        >
            Sign in
        </button>
    </form>
</div>
