<?php

use App\Actions\Students\UpdateStudent;
use App\Models\Student;
use App\Models\Subject;
use App\Services\SettingsService;
use App\Services\StudentAccountService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.student')] class extends Component {
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

    public bool $editing = false;

    public int $formToken = 0;

    public string $current_password = '';

    public string $new_password = '';

    public string $new_password_confirmation = '';

    public function mount(): void
    {
        $this->student = auth()->user()->student()->firstOrFail();

        $this->fillProfile();
    }

    protected function fillProfile(): void
    {
        $this->surname = $this->student->surname;
        $this->first_name = $this->student->first_name;
        $this->middle_name = $this->student->middle_name ?? '';
        $this->foundation_number = $this->student->foundation_number;
        $this->examination_number = $this->student->examination_number;
        $this->subject_one_id = $this->student->subject_one_id;
        $this->subject_two_id = $this->student->subject_two_id;
        $this->subject_three_id = $this->student->subject_three_id;
    }

    public function rules(): array
    {
        $settings = app(SettingsService::class);

        return [
            'surname' => ['required', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'foundation_number' => ['required', 'string', 'size:'.$settings->foundationNumberLength(), 'unique:students,foundation_number,'.$this->student->id],
            'examination_number' => ['nullable', 'string', 'size:'.$settings->examinationNumberLength(), 'unique:students,examination_number,'.$this->student->id],
            'subject_one_id' => ['required', 'exists:subjects,id'],
            'subject_two_id' => ['required', 'exists:subjects,id', 'different:subject_one_id'],
            'subject_three_id' => ['required', 'exists:subjects,id', 'different:subject_one_id', 'different:subject_two_id'],
            'passport' => [$this->student->passport ? 'nullable' : 'required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'passport.required' => 'Please upload a passport photo.',
            'current_password.current_password' => 'Your current password is incorrect.',
        ];
    }

    public function startEdit(): void
    {
        $this->fillProfile();
        $this->passport = null;
        $this->resetErrorBag();
        $this->formToken++;
        $this->editing = true;
    }

    public function cancelEdit(): void
    {
        $this->fillProfile();
        $this->passport = null;
        $this->resetErrorBag();
        $this->formToken++;
        $this->editing = false;
    }

    public function updatePassword(): void
    {
        $this->validateOnly('current_password', [
            'current_password' => ['required', 'string', 'current_password:web'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        auth()->user()->update([
            'password' => $this->new_password,
        ]);

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
        $this->resetErrorBag();

        $this->dispatch('flash-message', message: 'Password updated successfully.');
    }

    public function save(UpdateStudent $action): void
    {
        $this->validate();

        $action->run($this->student, [
            ...$this->only([
                'surname',
                'first_name',
                'middle_name',
                'foundation_number',
                'examination_number',
                'subject_one_id',
                'subject_two_id',
                'subject_three_id',
                'passport',
            ]),
            'examination_number' => $this->examination_number ?: null,
        ]);

        app(StudentAccountService::class)->syncIdentifier($this->student->refresh());

        $this->editing = false;
        $this->passport = null;
        $this->formToken++;

        $this->dispatch('flash-message', message: 'Profile updated successfully.');
    }

    #[Computed]
    public function subjects(): array
    {
        return Subject::orderBy('name')->get()->pluck('name', 'id')->toArray();
    }
};

?>

<div class="mx-auto max-w-5xl">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-primary-800">My Profile</h1>
        <p class="mt-1 text-sm text-slate-500">
            Review and update your registration details.
        </p>
    </div>

    <div class="card overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-6 py-4">
            <div>
                <h2 class="font-bold text-slate-800">My Information</h2>
                <p class="text-xs text-slate-400">Keep your details accurate — contact the school office if something is wrong beyond these fields.</p>
            </div>
            @if (! $editing)
                <button wire:click="startEdit" class="btn-outline">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                    Edit Profile
                </button>
            @endif
        </div>

        <form wire:submit="save" class="p-6">
            <fieldset {{ ! $editing ? 'disabled' : '' }}>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
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
                    <div>
                        <label for="foundation_number" class="label">Foundation Number <span class="text-red-500">*</span></label>
                        <input type="text" id="foundation_number" wire:model="foundation_number" class="input">
                        <p class="mt-1 text-xs text-slate-400">Used to sign in — must not already belong to another student.</p>
                        <x-input-error :messages="$errors->get('foundation_number')" class="mt-1" />
                    </div>
                    <div>
                        <label for="examination_number" class="label">Examination Number</label>
                        <input type="text" id="examination_number" wire:model="examination_number" class="input">
                        <x-input-error :messages="$errors->get('examination_number')" class="mt-1" />
                    </div>
                </div>

                <h3 class="mt-6 mb-4 text-sm font-bold uppercase tracking-wider text-slate-500">Subject Combination</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3" wire:key="subject-selects-{{ $formToken }}">
                    <div>
                        <x-vanilla-searchable-select
                            name="subject_one_id"
                            label="First Subject"
                            placeholder="Select first subject"
                            :options="$this->subjects"
                            :selected="$subject_one_id"
                            :required="true"
                            wireModel="subject_one_id"
                            group="subjects"
                        />
                        <x-input-error :messages="$errors->get('subject_one_id')" class="mt-1" />
                    </div>
                    <div>
                        <x-vanilla-searchable-select
                            name="subject_two_id"
                            label="Second Subject"
                            placeholder="Select second subject"
                            :options="$this->subjects"
                            :selected="$subject_two_id"
                            :required="true"
                            wireModel="subject_two_id"
                            group="subjects"
                        />
                        <x-input-error :messages="$errors->get('subject_two_id')" class="mt-1" />
                    </div>
                    <div>
                        <x-vanilla-searchable-select
                            name="subject_three_id"
                            label="Third Subject"
                            placeholder="Select third subject"
                            :options="$this->subjects"
                            :selected="$subject_three_id"
                            :required="true"
                            wireModel="subject_three_id"
                            group="subjects"
                        />
                        <x-input-error :messages="$errors->get('subject_three_id')" class="mt-1" />
                    </div>
                </div>

                <div class="mt-6">
                    <label class="label">Passport Photo @if (! $student->passport)<span class="text-red-500">*</span>@endif</label>
                    @if ($student->passport && ! $passport)
                        <div class="flex items-center gap-4">
                            <img src="{{ asset('storage/'.$student->passport) }}" alt="Passport"
                                class="h-16 w-16 rounded-xl border border-slate-200 object-cover">
                            <p class="text-xs text-slate-400">Upload a new photo below to replace.</p>
                        </div>
                    @endif
                    <label class="btn-outline mt-2 inline-flex cursor-pointer">
                        {{ $passport ? 'Change File' : ($student->passport ? 'Replace Passport' : 'Upload Passport') }}
                        <input type="file" wire:model="passport" accept="image/jpeg,image/png,image/webp" class="sr-only">
                    </label>
                    @error('passport')
                        <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                @if ($editing)
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" wire:click="cancelEdit" class="btn-outline">Cancel</button>
                        <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="save">
                            Save Changes
                        </button>
                    </div>
                @endif
            </fieldset>
        </form>
    </div>

    {{-- ─── Change Password ────────────────────────────────────── --}}
    <div class="card mt-8 overflow-hidden">
        <div class="border-b border-slate-100 px-6 py-4">
            <h2 class="font-bold text-slate-800">Change Password</h2>
            <p class="text-xs text-slate-400">Update the password you use to sign in to the student portal.</p>
        </div>

        <form wire:submit="updatePassword" class="p-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label for="current_password" class="label">Current Password <span class="text-red-500">*</span></label>
                    <input type="password" id="current_password" wire:model="current_password" class="input" autocomplete="current-password">
                    <x-input-error :messages="$errors->get('current_password')" class="mt-1" />
                </div>
                <div>
                    <label for="new_password" class="label">New Password <span class="text-red-500">*</span></label>
                    <input type="password" id="new_password" wire:model="new_password" class="input" autocomplete="new-password">
                    <p class="mt-1 text-xs text-slate-400">Minimum 8 characters.</p>
                    <x-input-error :messages="$errors->get('new_password')" class="mt-1" />
                </div>
                <div>
                    <label for="new_password_confirmation" class="label">Confirm New Password <span class="text-red-500">*</span></label>
                    <input type="password" id="new_password_confirmation" wire:model="new_password_confirmation" class="input" autocomplete="new-password">
                    <x-input-error :messages="$errors->get('new_password_confirmation')" class="mt-1" />
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="updatePassword">
                    Update Password
                </button>
            </div>
        </form>
    </div>
</div>
