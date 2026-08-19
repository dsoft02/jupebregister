<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component {
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/');
    }
};

?>

<div class="flex items-center gap-3">
    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-accent-500 text-sm font-bold text-primary-900">
        {{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
    </div>
    <div class="min-w-0 flex-1">
        <a href="{{ route('admin.profile') }}" class="block truncate text-sm font-semibold text-white hover:text-emerald-300 transition">{{ auth()->user()->name }}</a>
        <p class="truncate text-[11px] text-primary-200 capitalize">
            {{ auth()->user()->getRoleNames()->first() ? str_replace('_', ' ', auth()->user()->getRoleNames()->first()) : 'User' }}
        </p>
    </div>
    <button wire:click="logout" title="Sign out"
        class="rounded-lg p-2 text-primary-200 transition hover:bg-primary-800 hover:text-white">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
        </svg>
    </button>
</div>
