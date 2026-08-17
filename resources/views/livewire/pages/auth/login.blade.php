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

        $this->redirectIntended(default: route('admin.dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login" class="space-y-5">
        <div class="space-y-2">
            <label for="email" class="flex items-center gap-2 text-sm font-medium text-slate-700">
                <i data-lucide="user-round" class="h-4 w-4 text-slate-400"></i>
                Email address
            </label>
            <x-text-input wire:model="form.email" id="email" type="email" name="email" placeholder="admin@paau.edu.ng" required autofocus autocomplete="username" />
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
            Sign in to Dashboard
        </button>
    </form>
</div>
