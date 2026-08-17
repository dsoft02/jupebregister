<?php

use App\Actions\Logs\LogActivity;
use App\Imports\SubjectsImport;
use App\Models\Subject;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

new #[Layout('layouts.app')] class extends Component {
    use WithPagination, WithFileUploads;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public bool $is_active = true;

    public $importFile;

    public bool $showImportForm = false;

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

    public function importSubjects(LogActivity $log): void
    {
        $this->validate([
            'importFile' => ['required', 'file', 'mimes:csv,xlsx,xls', 'max:2048'],
        ]);

        $importer = new SubjectsImport();

        Excel::import($importer, $this->importFile);

        $log->run(
            action: 'subjects.imported',
            description: 'Imported subjects spreadsheet — '.$importer->created.' created, '.$importer->updated.' updated, '.$importer->skipped.' skipped',
            properties: ['created' => $importer->created, 'updated' => $importer->updated, 'skipped' => $importer->skipped],
        );

        $this->reset(['importFile', 'showImportForm']);
        $this->dispatch('flash-message', message: "Import complete: {$importer->created} created, {$importer->updated} updated, {$importer->skipped} skipped.");
    }

    #[Computed]
    public function subjects()
    {
        return Subject::withCount(['studentsAsSubjectOne', 'studentsAsSubjectTwo', 'studentsAsSubjectThree'])
            ->orderBy('name')
            ->paginate(12)
            ->through(fn ($s) => [
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
        <button wire:click="$set('showImportForm', true)" class="btn-outline">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
            Import Subjects
        </button>
        <button wire:click="startCreate" class="btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            New Subject
        </button>
    </x-admin.page-header>

    @if (session()->has('status'))
        <div class="rounded-2xl border border-primary-200 bg-primary-50 p-4 text-sm font-medium text-primary-800">
            {{ session('status') }}
        </div>
    @endif

    @if ($showImportForm)
        <form wire:submit="importSubjects" class="card p-6">
            <h3 class="mb-5 text-sm font-bold uppercase tracking-wider text-slate-500">Import Subjects</h3>
            <div class="space-y-4">
                <div class="rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 p-8 text-center transition hover:border-primary-400">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="mx-auto h-10 w-10 text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                    <p class="mt-3 text-sm font-medium text-slate-700">Drag & drop your spreadsheet here, or</p>
                    <label class="btn-secondary mt-3 cursor-pointer">
                        Choose File
                        <input type="file" wire:model="importFile" accept=".csv,.xlsx,.xls" class="sr-only">
                    </label>
                    <p class="mt-2 text-xs text-slate-400">CSV, XLSX or XLS &middot; maximum 2MB</p>
                    @error('importFile')
                        <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="rounded-xl border border-slate-200 p-4 text-sm">
                    <p class="font-semibold text-slate-700">Required Columns:</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <span class="rounded-lg bg-slate-100 px-2 py-1 font-mono text-[11px] text-slate-600">name</span>
                        <span class="rounded-lg bg-slate-100 px-2 py-1 font-mono text-[11px] text-slate-600">is_active (optional)</span>
                    </div>
                    <p class="mt-2 text-xs text-slate-400">Subjects with matching names will be updated. Restored if previously deleted.</p>
                </div>
            </div>
            <div class="mt-5 flex items-center justify-end gap-3">
                <button type="button" wire:click="$set('showImportForm', false)" class="btn-outline">Cancel</button>
                <button type="submit" class="btn-primary">Import Subjects</button>
            </div>
        </form>
    @endif

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
                        <button x-on:click="$store.confirmModal.show({
                            title: 'Delete Subject',
                            message: 'Delete this subject?',
                            confirmText: 'Delete',
                            onConfirm: () => @this.call('delete', {{ $subject['id'] }})
                        })"
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

    @if ($this->subjects->hasPages())
        <div class="border-t border-slate-100 px-4 py-3">
            {{ $this->subjects->links() }}
        </div>
    @endif
</div>
