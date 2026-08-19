<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;

new #[Layout('layouts.app')] class extends Component {
    use WithFileUploads;

    public string $name = '';

    public string $email = '';

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(): void
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
    }

    public function updateProfile(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('flash-message', message: 'Profile updated successfully.');
    }

    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('flash-message', message: 'Password updated successfully.');
    }
};

?>

<div class="space-y-6">
    <x-admin.page-header
        title="My Profile"
        eyebrow="Account"
        description="Update your account information and password.">
        <a href="{{ route('admin.dashboard') }}" class="btn-outline">&larr; Back</a>
    </x-admin.page-header>

    @if (session()->has('status'))
        <div class="rounded-2xl border border-primary-200 bg-primary-50 p-4 text-sm font-medium text-primary-800">
            {{ session('status') }}
        </div>
    @endif

    {{-- Profile Information --}}
    <div class="card">
        <div class="card-header">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Profile Information</h3>
            <p class="mt-1 text-xs text-slate-400">Update your name and email address.</p>
        </div>
        <form wire:submit="updateProfile" class="p-6 space-y-5">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label for="name" class="label">Name</label>
                    <input type="text" id="name" wire:model="name" class="input" required autofocus autocomplete="name">
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>
                <div>
                    <label for="email" class="label">Email</label>
                    <input type="email" id="email" wire:model="email" class="input" required autocomplete="username">
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>
            </div>

            <div class="flex items-center gap-4">
                <button type="submit" class="btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.75V16.5L12 14.25 7.5 16.5V3.75m9 0H18A2.25 2.25 0 0120.25 6v12A2.25 2.25 0 0118 20.25H6A2.25 2.25 0 013.75 18V6A2.25 2.25 0 016 3.75h1.5m9 0h-9"/></svg>
                    Save Profile
                </button>
            </div>
        </form>
    </div>

    {{-- Update Password --}}
    <div class="card">
        <div class="card-header">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Update Password</h3>
            <p class="mt-1 text-xs text-slate-400">Ensure your account is using a long, random password to stay secure.</p>
        </div>
        <form wire:submit="updatePassword" class="p-6 space-y-5">
            <div>
                <label for="current_password" class="label">Current Password</label>
                <input type="password" id="current_password" wire:model="current_password" class="input" autocomplete="current-password">
                <x-input-error :messages="$errors->get('current_password')" class="mt-1" />
            </div>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label for="password" class="label">New Password</label>
                    <input type="password" id="password" wire:model="password" class="input" autocomplete="new-password">
                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                </div>
                <div>
                    <label for="password_confirmation" class="label">Confirm Password</label>
                    <input type="password" id="password_confirmation" wire:model="password_confirmation" class="input" autocomplete="new-password">
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
                </div>
            </div>

            <div class="flex items-center gap-4">
                <button type="submit" class="btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                    Update Password
                </button>
            </div>
        </form>
    </div>
</div>
