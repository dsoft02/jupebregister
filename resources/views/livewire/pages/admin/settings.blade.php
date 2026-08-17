<?php

use App\Actions\Logs\LogActivity;
use App\Services\SettingsService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.app')] class extends Component {
    use WithFileUploads;

    public $letterhead_image;

    public $official_stamp;

    public $director_signature;

    public ?string $director_name = null;

    public ?string $director_credentials = null;

    public ?string $vice_chancellor_name = null;

    public ?string $vice_chancellor_credentials = null;

    public ?string $issue_date = null;

    public ?string $current_session = null;

    public function mount(SettingsService $settings): void
    {
        $this->director_name = $settings->get('director_name');
        $this->director_credentials = $settings->get('director_credentials');
        $this->vice_chancellor_name = $settings->get('vice_chancellor_name');
        $this->vice_chancellor_credentials = $settings->get('vice_chancellor_credentials');
        $this->issue_date = $settings->get('issue_date');
        $this->current_session = $settings->get('current_session');
    }

    public function rules(): array
    {
        return [
            'letterhead_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'official_stamp' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'director_signature' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'director_name' => ['nullable', 'string', 'max:255'],
            'director_credentials' => ['nullable', 'string', 'max:255'],
            'vice_chancellor_name' => ['nullable', 'string', 'max:255'],
            'vice_chancellor_credentials' => ['nullable', 'string', 'max:255'],
            'issue_date' => ['nullable', 'date'],
            'current_session' => ['nullable', 'string', 'max:20'],
        ];
    }

    public function save(SettingsService $settings, LogActivity $log): void
    {
        $this->validate();

        $textValues = $this->only([
            'director_name',
            'director_credentials',
            'vice_chancellor_name',
            'vice_chancellor_credentials',
            'issue_date',
            'current_session',
        ]);

        $settings->set($textValues);

        foreach ([
            'letterhead_image' => 'settings/letterhead',
            'official_stamp' => 'settings/stamp',
            'director_signature' => 'settings/signature',
        ] as $key => $folder) {
            if ($this->{$key}) {
                $oldPath = $settings->get($key);
                if ($oldPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($oldPath)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
                }
                $settings->set([$key => $this->{$key}->store($folder, 'public')]);
            }
        }

        $log->run(
            action: 'settings.updated',
            description: 'Statement of Result settings were updated',
        );

        $this->dispatch('flash-message', message: 'Settings saved. Future PDFs will use the latest assets.');

        session()->flash('status', 'Settings saved successfully. Future PDFs will use the latest assets.');
    }

    #[Computed]
    public function currentAssets(): array
    {
        $settings = app(SettingsService::class);

        return [
            'letterhead_image' => $settings->fileUrl('letterhead_image'),
            'official_stamp' => $settings->fileUrl('official_stamp'),
            'director_signature' => $settings->fileUrl('director_signature'),
        ];
    }
};

?>

<div class="space-y-6">
    <x-admin.page-header
        title="Settings"
        eyebrow="Configuration"
        description="Manage Statement of Result letterhead, stamp, signature and official text." />

    @if (session()->has('status'))
        <div class="rounded-2xl border border-primary-200 bg-primary-50 p-4 text-sm font-medium text-primary-800">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">
        <div class="card">
            <div class="card-header">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Statement of Result Assets</h3>
                <p class="mt-1 text-xs text-slate-400">
                    The letterhead replaces the printed header — the PDF renders only dynamic student data over it.
                </p>
            </div>
            <div class="grid grid-cols-1 gap-6 p-6 lg:grid-cols-3">
                @php
                    $uploadFields = [
                        ['key' => 'letterhead_image', 'label' => 'Letterhead Image', 'help' => 'Full-page official letterhead with header, logos and watermark. Recommended 1240×1754 (A4).'],
                        ['key' => 'official_stamp', 'label' => 'Official Stamp', 'help' => 'Official school stamp image (PNG with transparency works best).'],
                        ['key' => 'director_signature', 'label' => 'Director Signature', 'help' => 'Scanned signature of the Director.'],
                    ];
                @endphp
                @foreach ($uploadFields as $field)
                    <div class="rounded-2xl border border-slate-200 p-5">
                        <h4 class="text-sm font-semibold text-slate-800">{{ $field['label'] }}</h4>
                        <p class="mt-1 text-xs text-slate-400">{{ $field['help'] }}</p>

                        @if ($this->currentAssets[$field['key']])
                            <div class="mt-4 flex items-center justify-center rounded-xl bg-slate-50 p-3">
                                <img src="{{ $this->currentAssets[$field['key']] }}" alt="{{ $field['label'] }}"
                                    class="max-h-28 rounded-lg border border-slate-200 object-contain">
                            </div>
                        @endif

                        <label class="btn-outline mt-4 w-full cursor-pointer">
                            {{ $this->currentAssets[$field['key']] ? 'Replace' : 'Upload' }}
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                            <input type="file" wire:model="{{ $field['key'] }}" accept="image/*" class="sr-only">
                        </label>
                        @error($field['key'])
                            <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                        @if (${$field['key']})
                            <p class="mt-2 text-xs font-medium text-primary-700">Ready to upload: {{ ${$field['key']}->getClientOriginalName() }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Official Text</h3>
            </div>
            <div class="grid grid-cols-1 gap-5 p-6 sm:grid-cols-2">
                <div>
                    <label for="director_name" class="label">Director Name</label>
                    <input type="text" id="director_name" wire:model="director_name" class="input" placeholder="e.g. Prof. John A. Smith">
                    <x-input-error :messages="$errors->get('director_name')" class="mt-1" />
                </div>
                <div>
                    <label for="director_credentials" class="label">Director Credentials</label>
                    <input type="text" id="director_credentials" wire:model="director_credentials" class="input" placeholder="e.g. Director, PAAU Foundation School">
                    <x-input-error :messages="$errors->get('director_credentials')" class="mt-1" />
                </div>
                <div>
                    <label for="vice_chancellor_name" class="label">Vice Chancellor Name</label>
                    <input type="text" id="vice_chancellor_name" wire:model="vice_chancellor_name" class="input" placeholder="Vice Chancellor">
                    <x-input-error :messages="$errors->get('vice_chancellor_name')" class="mt-1" />
                </div>
                <div>
                    <label for="vice_chancellor_credentials" class="label">Vice Chancellor Credentials</label>
                    <input type="text" id="vice_chancellor_credentials" wire:model="vice_chancellor_credentials" class="input" placeholder="Vice Chancellor, PAAU">
                    <x-input-error :messages="$errors->get('vice_chancellor_credentials')" class="mt-1" />
                </div>
                <div>
                    <label for="issue_date" class="label">Default Issue Date</label>
                    <input type="date" id="issue_date" wire:model="issue_date" class="input">
                    <x-input-error :messages="$errors->get('issue_date')" class="mt-1" />
                </div>
                <div>
                    <label for="current_session" class="label">Current Academic Session</label>
                    <input type="text" id="current_session" wire:model="current_session" class="input" placeholder="2025/2026">
                    <x-input-error :messages="$errors->get('current_session')" class="mt-1" />
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.75V16.5L12 14.25 7.5 16.5V3.75m9 0H18A2.25 2.25 0 0120.25 6v12A2.25 2.25 0 0118 20.25H6A2.25 2.25 0 013.75 18V6A2.25 2.25 0 016 3.75h1.5m9 0h-9"/></svg>
                Save Settings
            </button>
        </div>
    </form>
</div>
