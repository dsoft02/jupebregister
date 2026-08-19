<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Artisan;

new #[Layout('layouts.app')] class extends Component {
    public array $pending = [];

    public array $ran = [];

    public string $output = '';

    public bool $ranSuccess = false;

    public bool $hasPending = false;

    public function mount(): void
    {
        $this->refreshStatus();
    }

    public function refreshStatus(): void
    {
        $this->output = '';
        $this->ranSuccess = false;

        $output = '';
        Artisan::call('migrate:status', [], $output);
        $raw = $output;

        $this->pending = [];
        $this->ran = [];

        foreach (explode("\n", $raw) as $line) {
            $line = trim($line);
            if (str_starts_with($line, '|') && preg_match('/\|\s*(\d+)\s*\|(.+)\|/', $line, $m)) {
                $batch = $m[1];
                $name = trim($m[2]);

                if (str_contains($line, 'Yes')) {
                    $this->ran[] = ['batch' => $batch, 'name' => $name];
                } else {
                    $this->pending[] = ['batch' => '-', 'name' => $name];
                }
            }
        }

        $this->hasPending = count($this->pending) > 0;
    }

    public function runMigrations(): void
    {
        $this->output = '';
        $this->ranSuccess = false;

        Artisan::call('migrate', ['--force' => true], $output);
        $this->output = $output;
        $this->ranSuccess = true;

        $this->refreshStatus();

        if (! $this->hasPending) {
            $this->output = $output ?: 'All migrations have been run successfully.';
        }

        $this->dispatch('migration-complete');
    }

    public function runFresh(): void
    {
        Artisan::call('migrate:fresh', ['--force' => true], $output);
        $this->output = $output;
        $this->ranSuccess = true;

        $this->refreshStatus();

        $this->dispatch('migration-complete');
    }
};

?>

<div class="space-y-6">
    <x-admin.page-header
        title="Database Migrations"
        eyebrow="System Maintenance"
        description="View pending migrations and run them when the application is updated.">
        <a href="{{ route('admin.dashboard') }}" class="btn-outline">&larr; Back</a>
    </x-admin.page-header>

    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
        <p class="font-semibold">Caution</p>
        <p class="mt-1 text-xs text-amber-700">
            Running migrations on a live database is irreversible. Make sure you have a backup before proceeding.
            This page is only accessible to Super Administrators.
        </p>
    </div>

    @if ($output)
        <div class="card">
            <div class="card-header">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Output</h3>
            </div>
            <div class="p-6">
                <pre class="overflow-x-auto rounded-xl bg-slate-900 p-4 text-xs text-emerald-400">{{ $output }}</pre>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Pending Migrations --}}
        <div class="card">
            <div class="card-header">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">
                    Pending Migrations
                    @if (count($this->pending))
                        <span class="ml-2 inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800">
                            {{ count($this->pending) }}
                        </span>
                    @endif
                </h3>
            </div>
            <div class="p-6">
                @if (count($this->pending))
                    <ul class="space-y-2">
                        @foreach ($this->pending as $migration)
                            <li class="flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4 shrink-0 text-amber-600">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                                </svg>
                                <span class="text-sm font-medium text-slate-700">{{ $migration['name'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="py-8 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mx-auto h-12 w-12 text-emerald-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="mt-3 text-sm font-medium text-slate-600">All migrations are up to date.</p>
                        <p class="mt-1 text-xs text-slate-400">No pending migrations found.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Recently Ran Migrations --}}
        <div class="card">
            <div class="card-header">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Recently Run</h3>
            </div>
            <div class="p-6">
                @if (count($this->ran))
                    <ul class="space-y-2">
                        @foreach ($this->ran as $migration)
                            <li class="flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4 shrink-0 text-emerald-500">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                </svg>
                                <span class="text-sm text-slate-600">{{ $migration['name'] }}</span>
                                <span class="ml-auto rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-500">Batch {{ $migration['batch'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="py-8 text-center text-sm text-slate-400">No migrations have been run yet.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <button wire:click="refreshStatus" class="btn-outline">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182"/>
            </svg>
            Refresh
        </button>

        @if ($this->hasPending)
            <button wire:click="runMigrations" wire:loading.attr="disabled" class="btn-primary"
                x-data
                x-on:click.prevent="
                    if (confirm('Run {{ count($this->pending) }} pending migration(s)? This cannot be undone.')) {
                        $wire.runMigrations();
                    }
                ">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"/>
                </svg>
                Run Pending Migrations
            </button>
        @endif
    </div>
</div>
