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

    public $letterhead_landscape;

    public $watermark_image;

    public $official_stamp;

    public $director_signature;

    public ?string $director_name = null;

    public ?string $issue_date = null;

    public ?string $current_session = null;

    public ?string $result_year = null;

    public bool $verification_enabled = false;

    public bool $show_stamp = true;

    public bool $show_signature = true;

    public function mount(SettingsService $settings): void
    {
        $this->director_name = $settings->get('director_name');
        $this->issue_date = $settings->get('issue_date');
        $this->current_session = $settings->get('current_session');
        $this->result_year = $settings->get('result_year');
        $this->verification_enabled = $settings->get('verification_enabled') === '1';
        $this->show_stamp = $settings->get('show_stamp') !== '0';
        $this->show_signature = $settings->get('show_signature') !== '0';
    }

    public function rules(): array
    {
        return [
            'letterhead_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'letterhead_landscape' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'watermark_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'official_stamp' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'director_signature' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'director_name' => ['nullable', 'string', 'max:255'],
            'issue_date' => ['nullable', 'date'],
            'current_session' => ['nullable', 'string', 'max:20'],
            'result_year' => ['nullable', 'string', 'max:10'],
        ];
    }

    public function save(SettingsService $settings, LogActivity $log): void
    {
        $this->validate();

        $textValues = $this->only([
            'director_name',
            'issue_date',
            'current_session',
            'result_year',
        ]);

        $textValues['verification_enabled'] = $this->verification_enabled ? '1' : '0';
        $textValues['show_stamp'] = $this->show_stamp ? '1' : '0';
        $textValues['show_signature'] = $this->show_signature ? '1' : '0';

        $settings->set($textValues);

        foreach ([
            'letterhead_image' => 'settings/letterhead',
            'letterhead_landscape' => 'settings/letterhead-landscape',
            'watermark_image' => 'settings/watermark',
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

        $this->dispatch('flash-message', message: 'Settings saved successfully.');
    }

    #[Computed]
    public function currentAssets(): array
    {
        $settings = app(SettingsService::class);

        return [
            'letterhead_image' => $settings->fileUrl('letterhead_image'),
            'letterhead_landscape' => $settings->fileUrl('letterhead_landscape'),
            'watermark_image' => $settings->fileUrl('watermark_image'),
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

    <form wire:submit="save" class="space-y-6">
        <div class="card">
            <div class="card-header">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Statement of Result Assets</h3>
                <p class="mt-1 text-xs text-slate-400">
                    The letterhead replaces the printed header — the PDF renders only dynamic student data over it.
                </p>
            </div>
            <div class="grid grid-cols-1 gap-6 p-6 md:grid-cols-2 xl:grid-cols-4">
                @php
                    $uploadFields = [
                        ['key' => 'letterhead_image', 'label' => 'Letterhead Image (Portrait)', 'help' => 'Full-page official letterhead for single-statement PDFs. Recommended 1240×1754 (A4 portrait).'],
                        ['key' => 'letterhead_landscape', 'label' => 'Letterhead Image (Landscape)', 'help' => 'Full-page official letterhead for combined statement PDFs. Recommended 1754×1240 (A4 landscape).'],
                        ['key' => 'watermark_image', 'label' => 'Watermark', 'help' => 'Watermark image displayed behind the content on the result slip.'],
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
                    <label for="issue_date" class="label">Default Issue Date</label>
                    <input type="date" id="issue_date" wire:model="issue_date" class="input">
                    <x-input-error :messages="$errors->get('issue_date')" class="mt-1" />
                </div>
                <div>
                    <label for="current_session" class="label">Current Academic Session</label>
                    <input type="text" id="current_session" wire:model="current_session" class="input" placeholder="2025/2026">
                    <x-input-error :messages="$errors->get('current_session')" class="mt-1" />
                </div>
                <div>
                    <label for="result_year" class="label">Result Year</label>
                    <input type="text" id="result_year" wire:model="result_year" class="input" placeholder="2025">
                    <p class="mt-1 text-xs text-slate-400">Year shown on statement of result PDFs (e.g. 2025). Defaults to 2025 if blank.</p>
                    <x-input-error :messages="$errors->get('result_year')" class="mt-1" />
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Verification</h3>
                <p class="mt-1 text-xs text-slate-400">
                    Control whether students can verify and download results from the public portal.
                </p>
            </div>
            <div class="p-6">
                <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-4 text-sm">
                    <input type="checkbox" wire:model="verification_enabled" value="1"
                        class="mt-0.5 rounded border-slate-300 text-primary-700 focus:ring-primary-500">
                    <span>
                        <span class="font-semibold text-slate-700">Enable Result Verification</span>
                        <span class="block text-xs text-slate-400">
                            When enabled, students can use their Foundation Number and Verification Token to verify and download published results.
                        </span>
                    </span>
                </label>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">PDF Display Options</h3>
                <p class="mt-1 text-xs text-slate-400">
                    Control which elements are displayed on generated Statement of Result PDFs.
                </p>
            </div>
            <div class="space-y-3 p-6">
                <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-4 text-sm">
                    <input type="checkbox" wire:model="show_stamp" value="1"
                        class="mt-0.5 rounded border-slate-300 text-primary-700 focus:ring-primary-500">
                    <span>
                        <span class="font-semibold text-slate-700">Show Official Stamp</span>
                        <span class="block text-xs text-slate-400">
                            Display the official stamp image on generated PDFs.
                        </span>
                    </span>
                </label>
                <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-4 text-sm">
                    <input type="checkbox" wire:model="show_signature" value="1"
                        class="mt-0.5 rounded border-slate-300 text-primary-700 focus:ring-primary-500">
                    <span>
                        <span class="font-semibold text-slate-700">Show Director Signature</span>
                        <span class="block text-xs text-slate-400">
                            Display the director's signature on generated PDFs.
                        </span>
                    </span>
                </label>
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
