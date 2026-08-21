<?php

use App\Actions\Students\CreateStudent;
use App\Enums\StudentStatus;
use App\Models\Subject;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.app')] class extends Component {
    use WithFileUploads;

    public string $surname = '';

    public string $first_name = '';

    public string $middle_name = '';

    public string $foundation_number = '';

    public ?string $examination_number = null;

    public ?int $subject_one_id = null;

    public ?int $subject_two_id = null;

    public ?int $subject_three_id = null;

    public $passport = null;

    public string $status = 'approved';

    public function rules(): array
    {
        return [
            'surname' => ['required', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'foundation_number' => ['required', 'string', 'max:50', 'unique:students,foundation_number'],
            'examination_number' => ['required', 'string', 'max:50', 'unique:students,examination_number'],
            'subject_one_id' => ['required', 'exists:subjects,id'],
            'subject_two_id' => ['required', 'exists:subjects,id', 'different:subject_one_id'],
            'subject_three_id' => ['required', 'exists:subjects,id', 'different:subject_one_id', 'different:subject_two_id'],
            'passport' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'status' => ['required', 'in:pending,approved,rejected'],
        ];
    }

    public function save(CreateStudent $action): void
    {
        $this->validate();

        $student = $action->run($this->all(), public: false);

        $this->redirect(route('admin.students.show', $student), navigate: true);
    }

    #[Computed]
    public function subjects(): array
    {
        return Subject::active()->orderBy('name')->pluck('name', 'id')->all();
    }

    #[Computed]
    public function statuses(): array
    {
        return StudentStatus::cases();
    }
};

?>

<div class="mx-auto max-w-3xl space-y-6">
    <x-admin.page-header
        title="Add Student"
        eyebrow="Student Management"
        description="Create a new student record.">
        <a href="{{ route('admin.students.index') }}" class="btn-outline">&larr; Back</a>
    </x-admin.page-header>

    <form wire:submit="save" class="card space-y-6 p-6">
        <div>
            <h3 class="mb-4 text-sm font-bold uppercase tracking-wider text-slate-500">Personal Information</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label for="surname" class="label">Surname <span class="text-red-500">*</span></label>
                    <input type="text" id="surname" wire:model="surname" class="input" placeholder="Surname">
                    <x-input-error :messages="$errors->get('surname')" class="mt-1" />
                </div>
                <div>
                    <label for="first_name" class="label">First Name <span class="text-red-500">*</span></label>
                    <input type="text" id="first_name" wire:model="first_name" class="input" placeholder="First name">
                    <x-input-error :messages="$errors->get('first_name')" class="mt-1" />
                </div>
                <div>
                    <label for="middle_name" class="label">Middle Name</label>
                    <input type="text" id="middle_name" wire:model="middle_name" class="input" placeholder="Middle name">
                    <x-input-error :messages="$errors->get('middle_name')" class="mt-1" />
                </div>
            </div>
        </div>

        <div>
            <h3 class="mb-4 text-sm font-bold uppercase tracking-wider text-slate-500">Registration Numbers</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="foundation_number" class="label">Foundation Number <span class="text-red-500">*</span></label>
                    <input type="text" id="foundation_number" wire:model="foundation_number" class="input" placeholder="e.g. PAAU/FS/001">
                    <x-input-error :messages="$errors->get('foundation_number')" class="mt-1" />
                </div>
                <div>
                    <label for="examination_number" class="label">Examination Number <span class="text-red-500">*</span></label>
                    <input type="text" id="examination_number" wire:model="examination_number" class="input" placeholder="e.g. PAAU/EXM/001">
                    <x-input-error :messages="$errors->get('examination_number')" class="mt-1" />
                </div>
            </div>
        </div>

        <div>
            <h3 class="mb-4 text-sm font-bold uppercase tracking-wider text-slate-500">Academics</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <x-vanilla-searchable-select
                        name="subject_one_id"
                        wireModel="subject_one_id"
                        label="Subject 1"
                        :options="$this->subjects"
                        placeholder="Select first subject…"
                        :required="true"
                        group="subjects"
                    />
                    <x-input-error :messages="$errors->get('subject_one_id')" class="mt-1" />
                </div>
                <div>
                    <x-vanilla-searchable-select
                        name="subject_two_id"
                        wireModel="subject_two_id"
                        label="Subject 2"
                        :options="$this->subjects"
                        placeholder="Select second subject…"
                        :required="true"
                        group="subjects"
                    />
                    <x-input-error :messages="$errors->get('subject_two_id')" class="mt-1" />
                </div>
                <div>
                    <x-vanilla-searchable-select
                        name="subject_three_id"
                        wireModel="subject_three_id"
                        label="Subject 3"
                        :options="$this->subjects"
                        placeholder="Select third subject…"
                        :required="true"
                        group="subjects"
                    />
                    <x-input-error :messages="$errors->get('subject_three_id')" class="mt-1" />
                </div>
            </div>

        </div>

        <div>
            <h3 class="mb-4 text-sm font-bold uppercase tracking-wider text-slate-500">Passport Photo <span class="text-red-500">*</span></h3>
            <div
                x-data="{ uploading: false, name: null }"
                x-on:livewire-upload-start="uploading = true"
                x-on:livewire-upload-finish="uploading = false; name = @js(null)"
                x-on:livewire-upload-error="uploading = false"
                class="rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 p-8 text-center transition hover:border-primary-400 hover:bg-primary-50/50">
                <div x-show="! uploading">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="mx-auto h-10 w-10 text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                    <p class="mt-3 text-sm font-medium text-slate-700">Drag &amp; drop a passport photo here, or</p>
                    <label class="btn-secondary mt-3 cursor-pointer">
                        Browse Files
                        <input type="file" wire:model="passport" accept="image/*" class="sr-only" />
                    </label>
                    <p class="mt-2 text-xs text-slate-400">JPG, PNG or WebP &middot; maximum 2MB</p>
                    @error('passport')
                        <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div x-show="uploading" class="text-sm font-medium text-slate-500">Uploading&hellip;</div>
            </div>
            @if ($passport)
                <div class="mt-4 flex items-center gap-3 rounded-xl bg-primary-50 p-3">
                    <img src="{{ $passport->temporaryUrl() }}" class="h-14 w-14 rounded-lg object-cover" alt="Preview">
                    <p class="text-sm font-medium text-primary-800">{{ $passport->getClientOriginalName() }}</p>
                </div>
            @endif
        </div>

        <div class="flex items-center justify-between border-t border-slate-100 pt-5">
            <div class="flex items-center gap-2">
                <label for="status" class="label mb-0">Status</label>
                <select id="status" wire:model="status" class="input w-40">
                    @foreach ($this->statuses as $option)
                        <option value="{{ $option->value }}">{{ $option->label() }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Save Student
            </button>
        </div>
    </form>
</div>
