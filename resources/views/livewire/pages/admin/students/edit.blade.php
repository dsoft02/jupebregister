<?php

use App\Actions\Students\UpdateStudent;
use App\Enums\StudentStatus;
use App\Models\Student;
use App\Models\Subject;
use App\Services\SettingsService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.app')] class extends Component {
    use WithFileUploads;

    public Student $student;

    public string $surname = '';

    public string $first_name = '';

    public string $middle_name = '';

    public string $foundation_number = '';

    public ?string $examination_number = null;

    public ?int $subject_one_id = null;

    public ?int $subject_two_id = null;

    public ?int $subject_three_id = null;

    public $passport = null;

    public string $status = 'pending';

    public function mount(Student $student): void
    {
        $this->student = $student;
        $this->surname = $student->surname;
        $this->first_name = $student->first_name;
        $this->middle_name = $student->middle_name ?? '';
        $this->foundation_number = $student->foundation_number;
        $this->examination_number = $student->examination_number;
        $this->subject_one_id = $student->subject_one_id;
        $this->subject_two_id = $student->subject_two_id;
        $this->subject_three_id = $student->subject_three_id;
        $this->status = $student->status->value;
    }

    public function rules(): array
    {
        $settings = app(SettingsService::class);

        return [
            'surname' => ['required', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'foundation_number' => ['required', 'string', 'size:'.$settings->foundationNumberLength(), 'unique:students,foundation_number,'.$this->student->id],
            'examination_number' => ['required', 'string', 'size:'.$settings->examinationNumberLength(), 'unique:students,examination_number,'.$this->student->id],
            'subject_one_id' => ['required', 'exists:subjects,id'],
            'subject_two_id' => ['required', 'exists:subjects,id', 'different:subject_one_id'],
            'subject_three_id' => ['required', 'exists:subjects,id', 'different:subject_one_id', 'different:subject_two_id'],
            'passport' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'status' => ['required', 'in:pending,approved,rejected'],
        ];
    }

    public function save(UpdateStudent $action): void
    {
        $this->validate();

        $action->run($this->student, $this->all());

        session()->flash('status', 'Student updated successfully.');

        $this->redirect(route('admin.students.show', $this->student), navigate: true);
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
        title="Edit Student"
        eyebrow="Student Management"
        :description="$student->fullName().' ('.$student->foundation_number.')'">
        <a href="{{ route('admin.students.show', $student) }}" class="btn-outline">&larr; Back</a>
    </x-admin.page-header>

    @if (session()->has('status'))
        <div class="rounded-2xl border border-primary-200 bg-primary-50 p-4 text-sm font-medium text-primary-800">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="save" class="card space-y-6 p-6">
        <div>
            <h3 class="mb-4 text-sm font-bold uppercase tracking-wider text-slate-500">Personal Information</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label for="surname" class="label">Surname <span class="text-red-500">*</span></label>
                    <input type="text" id="surname" wire:model="surname" class="input">
                    <x-input-error :messages="$errors->get('surname')" class="mt-1" />
                </div>
                <div>
                    <label for="first_name" class="label">First Name <span class="text-red-500">*</span></label>
                    <input type="text" id="first_name" wire:model="first_name" class="input">
                    <x-input-error :messages="$errors->get('first_name')" class="mt-1" />
                </div>
                <div>
                    <label for="middle_name" class="label">Middle Name</label>
                    <input type="text" id="middle_name" wire:model="middle_name" class="input">
                    <x-input-error :messages="$errors->get('middle_name')" class="mt-1" />
                </div>
            </div>
        </div>

        <div>
            <h3 class="mb-4 text-sm font-bold uppercase tracking-wider text-slate-500">Registration Numbers</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="foundation_number" class="label">Foundation Number <span class="text-red-500">*</span></label>
                    <input type="text" id="foundation_number" wire:model="foundation_number" class="input">
                    <x-input-error :messages="$errors->get('foundation_number')" class="mt-1" />
                </div>
                <div>
                    <label for="examination_number" class="label">Examination Number <span class="text-red-500">*</span></label>
                    <input type="text" id="examination_number" wire:model="examination_number" class="input">
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
                        :selected="$student->subject_one_id"
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
                        :selected="$student->subject_two_id"
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
                        :selected="$student->subject_three_id"
                        :required="true"
                        group="subjects"
                    />
                    <x-input-error :messages="$errors->get('subject_three_id')" class="mt-1" />
                </div>
            </div>

        </div>

        <div>
            <h3 class="mb-4 text-sm font-bold uppercase tracking-wider text-slate-500">Passport Photo</h3>
            @if ($student->passport)
                <div class="mb-4 flex items-center gap-4 rounded-xl border border-slate-200 p-4">
                    <img src="{{ Storage::url($student->passport) }} " alt="Current passport" class="h-16 w-16 rounded-lg object-cover">
                    <div>
                        <p class="text-sm font-medium text-slate-700">Current passport photo</p>
                        <p class="text-xs text-slate-400">Upload a new photo below to replace it.</p>
                    </div>
                </div>
            @endif
            <div class="rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 p-8 text-center transition hover:border-primary-400 hover:bg-primary-50/50">
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
            <button type="submit" class="btn-primary">Update Student</button>
        </div>
    </form>
</div>
