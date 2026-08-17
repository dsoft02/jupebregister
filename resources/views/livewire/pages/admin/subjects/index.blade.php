<?php

use App\Actions\Logs\LogActivity;
use App\Models\Subject;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {
    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public bool $is_active = true;

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:subjects,name,'.$this->editingId],
        ];
    }

    public function startCreate(): void
    {
        $this->reset(['editingId', 'name']);
        $this->is_active = true;
        $this->showForm = true;
    }

    public function startEdit(Subject $subject): void
    {
        $this->editingId = $subject->id;
        $this->name = $subject->name;
        $this->is_active = $subject->is_active;
        $this->showForm = true;
    }

    public function save(LogActivity $log): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'is_active' => $this->is_active,
        ];

        $subject = $this->editingId
            ? tap(Subject::findOrFail($this->editingId))->update($data)
            : Subject::create($data);

        $log->run(
            action: $this->editingId ? 'subject.updated' : 'subject.created',
            description: ($this->editingId ? 'Updated' : 'Created').' subject "'.$subject->name.'"',
            modelType: Subject::class,
            modelId: $subject->id,
        );

        $this->reset(['showForm', 'editingId', 'name']);
        $this->dispatch('flash-message', message: 'Subject saved.');
    }

    public function toggleActive(Subject $subject, LogActivity $log): void
    {
        $subject->update(['is_active' => ! $subject->is_active]);

        $log->run(
            action: 'subject.toggled',
            description: ($subject->is_active ? 'Enabled' : 'Disabled').' subject "'.$subject->name.'"',
            modelType: Subject::class,
            modelId: $subject->id,
        );
    }

    public function delete(Subject $subject, LogActivity $log): void
    {
        $log->run(
            action: 'subject.deleted',
            description: 'Deleted subject "'.$subject->name.'"',
            modelType: Subject::class,
            modelId: $subject->id,
        );

        $subject->delete();
        $this->dispatch('flash-message', message: 'Subject deleted.');
    }

    #[Computed]
    public function subjects()
    {
        return Subject::withCount(['studentsAsSubjectOne', 'studentsAsSubjectTwo', 'studentsAsSubjectThree'])
            ->orderBy('name')
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'is_active' => $s->is_active,
                'students_count' => $s->students_as_subject_one_count
                    + $s->students_as_subject_two_count
                    + $s->students_as_subject_three_count,
            ]);
    }
};

?>

<div class="space-y-6">
    <x-admin.page-header
        title="Subjects"
        eyebrow="Configuration"
        description="Manage the JUPEB subjects available for student selection.">
        <button wire:click="startCreate" class="btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            New Subject
        </button>
    </x-admin.page-header>

    @if ($showForm)
        <form wire:submit="save" class="card p-6">
            <h3 class="mb-5 text-sm font-bold uppercase tracking-wider text-slate-500">
                {{ $editingId ? 'Edit' : 'Create' }} Subject
            </h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="name" class="label">Subject Name <span class="text-red-500">*</span></label>
                    <input type="text" id="name" wire:model="name" class="input" placeholder="e.g. Biology">
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>
            </div>
            <div class="mt-5 flex items-center justify-between">
                <label class="flex cursor-pointer items-center gap-2 text-sm font-medium text-slate-700">
                    <input type="checkbox" wire:model="is_active" class="rounded border-slate-300 text-primary-700 focus:ring-primary-500">
                    Active (available during student selection)
                </label>
                <div class="flex gap-3">
                    <button type="button" wire:click="$set('showForm', false)" class="btn-outline">Cancel</button>
                    <button type="submit" class="btn-primary">{{ $editingId ? 'Update' : 'Create' }}</button>
                </div>
            </div>
        </form>
    @endif

    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($this->subjects as $subject)
            <div class="card p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="font-bold text-slate-900">{{ $subject['name'] }}</h3>
                        <p class="mt-1 text-xs text-slate-400">
                            {{ $subject['students_count'] }} student{{ $subject['students_count'] !== 1 ? 's' : '' }} enrolled
                        </p>
                    </div>
                    <span class="badge {{ $subject['is_active'] ? 'bg-primary-100 text-primary-800' : 'bg-slate-100 text-slate-600' }}">
                        {{ $subject['is_active'] ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4">
                    <button wire:click="toggleActive({{ $subject['id'] }})" class="text-sm font-semibold text-secondary-700 hover:text-secondary-800">
                        {{ $subject['is_active'] ? 'Deactivate' : 'Activate' }}
                    </button>
                    <div class="flex gap-1">
                        <button wire:click="startEdit({{ $subject['id'] }})" title="Edit"
                            class="rounded-lg p-2 text-slate-500 transition hover:bg-primary-50 hover:text-primary-700">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                        </button>
                        <button wire:click="delete({{ $subject['id'] }})" wire:confirm="Delete this subject?"
                            title="Delete"
                            class="rounded-lg p-2 text-slate-500 transition hover:bg-red-50 hover:text-red-700">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="md:col-span-2 xl:col-span-3">
                <x-admin.empty-state
                    title="No subjects yet"
                    description="Add the JUPEB subjects available for student selection."
                    icon="layers">
                    <button wire:click="startCreate" class="btn-primary">New Subject</button>
                </x-admin.empty-state>
            </div>
        @endforelse
    </div>
</div>
