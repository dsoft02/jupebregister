<?php

use App\Models\Student;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    public array $selected = [];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search']);
        $this->selected = [];
    }

    public function toggleSelectAll(): void
    {
        $this->selected = $this->selected === $this->studentIds()
            ? []
            : $this->studentIds();
    }

    public function studentIds(): array
    {
        return $this->trashedStudents->pluck('id')->toArray();
    }

    public function restoreStudent(int $studentId): void
    {
        $student = Student::withTrashed()->findOrFail($studentId);
        $this->authorize('delete', $student);

        $student->restore();
        $student->results()->restore();

        session()->flash('status', "Student {$student->fullName()} restored.");
    }

    public function forceDeleteStudent(int $studentId): void
    {
        $student = Student::withTrashed()->findOrFail($studentId);
        $this->authorize('delete', $student);

        $name = $student->fullName();

        $student->results()->forceDelete();
        $student->forceDelete();

        session()->flash('status', "Student {$name} permanently deleted.");
    }

    public function bulkRestore(): void
    {
        $this->authorize('delete', Student::class);

        $students = Student::whereIn('id', $this->selected)->withTrashed()->get();

        foreach ($students as $student) {
            $student->restore();
            $student->results()->restore();
        }

        $count = $students->count();
        $this->selected = [];
        session()->flash('status', "{$count} student(s) restored.");
    }

    public function bulkForceDelete(): void
    {
        $this->authorize('delete', Student::class);

        $students = Student::whereIn('id', $this->selected)->withTrashed()->get();

        foreach ($students as $student) {
            $student->results()->forceDelete();
            $student->forceDelete();
        }

        $count = $students->count();
        $this->selected = [];
        session()->flash('status', "{$count} student(s) permanently deleted.");
    }

    #[Computed]
    public function trashedStudents()
    {
        return Student::with('subjectOne', 'subjectTwo', 'subjectThree')
            ->withTrashed()
            ->onlyTrashed()
            ->when($this->search !== '', fn ($q) => $q->search($this->search))
            ->latest()
            ->paginate(15);
    }
};

?>

<div class="space-y-6">
    <x-admin.page-header
        title="Student Trash"
        eyebrow="Soft-Deleted Students"
        description="View, restore or permanently delete soft-deleted student records.">
        <a href="{{ route('admin.students.index') }}" class="btn-outline">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            Back to Students
        </a>
    </x-admin.page-header>

    @if (session()->has('status'))
        <div class="rounded-2xl border border-primary-200 bg-primary-50 p-4 text-sm font-medium text-primary-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="card p-4">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-6">
            <div class="md:col-span-4">
                <div class="relative">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by name or number…" class="input pl-10">
                </div>
            </div>
        </div>
        <div class="mt-3 flex items-center justify-between text-sm text-slate-500">
            <span>{{ $this->trashedStudents->total() }} trashed student{{ $this->trashedStudents->total() !== 1 ? 's' : '' }} found</span>
            @if ($search)
                <button wire:click="clearFilters" class="font-semibold text-primary-700 hover:text-primary-800">Clear filters</button>
            @endif
        </div>
    </div>

    <div class="card overflow-hidden">
        @if (count($selected) > 0)
            <div class="flex items-center gap-3 border-b border-primary-100 bg-primary-50 px-4 py-3">
                <span class="text-sm font-semibold text-primary-800">{{ count($selected) }} selected</span>
                <div class="flex items-center gap-2">
                    <button x-on:click="$store.confirmModal.show({
                        title: 'Restore Students',
                        message: 'Restore {{ count($selected) }} student(s)?',
                        confirmText: 'Restore',
                        onConfirm: () => @this.call('bulkRestore')
                    })" class="rounded-lg bg-green-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-green-700">Restore</button>
                    <button x-on:click="$store.confirmModal.show({
                        title: 'Permanently Delete Students',
                        message: 'Permanently delete {{ count($selected) }} student(s)? This action cannot be undone.',
                        confirmText: 'Delete Permanently',
                        onConfirm: () => @this.call('bulkForceDelete')
                    })" class="rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700">Delete Permanently</button>
                </div>
                <button wire:click="$set('selected', [])" class="ml-auto text-xs font-semibold text-primary-600 hover:text-primary-800">Clear</button>
            </div>
        @endif
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="border-b border-slate-100">
                    <tr>
                        <th class="th w-10">
                            <input type="checkbox" x-on:click="$wire.toggleSelectAll()"
                                :checked="$wire.selected.length > 0 && $wire.selected.length === {{ $this->trashedStudents->count() }}"
                                class="rounded border-slate-300 text-primary-700 focus:ring-primary-500">
                        </th>
                        <th class="th">Student</th>
                        <th class="th">Foundation No.</th>
                        <th class="th hidden lg:table-cell">Subjects</th>
                        <th class="th">Deleted At</th>
                        <th class="th text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($this->trashedStudents as $student)
                        <tr class="hover:bg-slate-50" :class="$wire.selected.includes({{ $student->id }}) && 'bg-primary-50'">
                            <td class="td w-10">
                                <input type="checkbox" wire:model.live="selected" value="{{ $student->id }}"
                                    class="rounded border-slate-300 text-primary-700 focus:ring-primary-500">
                            </td>
                            <td class="td">
                                <div class="flex items-center gap-3">
                                    @if ($student->passport)
                                        <img src="{{ Storage::url($student->passport) }}" alt="{{ $student->fullName() }}" class="h-10 w-10 rounded-full object-cover opacity-50">
                                    @else
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-500">
                                            {{ $student->initials() }}
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-semibold text-slate-600">{{ $student->fullName() }}</p>
                                        <p class="text-xs text-slate-400">{{ $student->foundation_number }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="td font-mono text-xs text-slate-500">{{ $student->foundation_number }}</td>
                            <td class="td hidden lg:table-cell text-slate-500">{{ implode(' / ', $student->chosenSubjectNames()) }}</td>
                            <td class="td text-xs text-slate-400">{{ $student->deleted_at->diffForHumans() }}</td>
                            <td class="td text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button x-on:click="$store.confirmModal.show({
                                        title: 'Restore Student',
                                        message: 'Restore {{ addslashes($student->fullName()) }}?',
                                        confirmText: 'Restore',
                                        onConfirm: () => @this.call('restoreStudent', {{ $student->id }})
                                    })" title="Restore"
                                        class="rounded-lg p-2 text-slate-500 transition hover:bg-green-50 hover:text-green-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182"/></svg>
                                    </button>
                                    <button x-on:click="$store.confirmModal.show({
                                        title: 'Permanently Delete Student',
                                        message: 'Permanently delete {{ addslashes($student->fullName()) }}? This cannot be undone.',
                                        confirmText: 'Delete Permanently',
                                        onConfirm: () => @this.call('forceDeleteStudent', {{ $student->id }})
                                    })" title="Delete Permanently"
                                        class="rounded-lg p-2 text-slate-500 transition hover:bg-red-50 hover:text-red-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-admin.empty-state
                                    title="Trash is empty"
                                    description="No soft-deleted student records found."
                                    icon="users">
                                    <a href="{{ route('admin.students.index') }}" class="btn-primary">Back to Students</a>
                                </x-admin.empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-4 py-3">
            {{ $this->trashedStudents->links() }}
        </div>
    </div>
</div>
