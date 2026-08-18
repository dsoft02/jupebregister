<?php

use App\Enums\StudentStatus;
use App\Models\Result;
use App\Models\Student;
use App\Models\Subject;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $subject = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingSubject(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'status', 'subject']);
    }

    public function deleteStudent(Student $student): void
    {
        $this->authorize('delete', $student);

        $name = $student->fullName();

        $student->results()->delete();
        $student->delete();

        session()->flash('status', "Student {$name} deleted.");
    }

    public function approveStudent(Student $student): void
    {
        $this->authorize('update', $student);

        $student->update(['status' => StudentStatus::Approved]);

        session()->flash('status', "Student {$student->fullName()} approved.");
    }

    public function rejectStudent(Student $student): void
    {
        $this->authorize('update', $student);

        $student->update(['status' => StudentStatus::Rejected]);

        session()->flash('status', "Student {$student->fullName()} rejected.");
    }

    #[Computed]
    public function students()
    {
        return Student::query()
            ->with('subjectOne', 'subjectTwo', 'subjectThree', 'results')
            ->when($this->search !== '', fn ($q) => $q->search($this->search))
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->when($this->subject !== '', fn ($q) => $q->where('subject_one_id', $this->subject)
                ->orWhere('subject_two_id', $this->subject)
                ->orWhere('subject_three_id', $this->subject))
            ->latest()
            ->paginate(15);
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

<div class="space-y-6">
    <x-admin.page-header
        title="Students"
        eyebrow="Student Management"
        description="Search, filter and manage all registered students.">
        <a href="{{ route('admin.students.create') }}" class="btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Add Student
        </a>
    </x-admin.page-header>

    @if (session()->has('status'))
        <div class="rounded-2xl border border-primary-200 bg-primary-50 p-4 text-sm font-medium text-primary-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="card p-4">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-12">
            <div class="md:col-span-4">
                <div class="relative">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by name or number…" class="input pl-10">
                </div>
            </div>
            <div class="md:col-span-3">
                <select wire:model.live="status" class="input">
                    <option value="">All Statuses</option>
                    @foreach ($this->statuses as $option)
                        <option value="{{ $option->value }}">{{ $option->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-3">
                <select wire:model.live="subject" class="input">
                    <option value="">All Subjects</option>
                    @foreach ($this->subjects as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="mt-3 flex items-center justify-between text-sm text-slate-500">
            <span>{{ $this->students->total() }} student{{ $this->students->total() !== 1 ? 's' : '' }} found</span>
            @if ($search || $status || $subject)
                <button wire:click="clearFilters" class="font-semibold text-primary-700 hover:text-primary-800">Clear filters</button>
            @endif
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="border-b border-slate-100">
                    <tr>
                        <th class="th">Student</th>
                        <th class="th">Foundation No.</th>
                        <th class="th hidden lg:table-cell">Subjects</th>
                        <th class="th">Status</th>
                        <th class="th text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($this->students as $student)
                        <tr class="hover:bg-slate-50">
                            <td class="td">
                                <div class="flex items-center gap-3">
                                    @if ($student->passport)
                                        <img src="{{ Storage::url($student->passport) }}" alt="{{ $student->fullName() }}" class="h-10 w-10 rounded-full object-cover">
                                    @else
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-100 text-xs font-bold text-primary-800">
                                            {{ $student->initials() }}
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-semibold text-slate-800">{{ $student->fullName() }}</p>
                                        <p class="text-xs text-slate-400">{{ $student->foundation_number }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="td font-mono text-xs">{{ $student->foundation_number }}</td>
                            <td class="td hidden lg:table-cell">{{ implode(' / ', $student->chosenSubjectNames()) }}</td>
                            <td class="td">
                                <x-admin.status-badge :status="$student->status->value">{{ $student->status->label() }}</x-admin.status-badge>
                            </td>
                            <td class="td text-right">
                                <div class="flex items-center justify-end gap-1">
                                    @if ($student->status->value === 'pending')
                                        <button wire:click="approveStudent({{ $student->id }})" title="Approve"
                                            class="rounded-lg p-2 text-slate-500 transition hover:bg-green-50 hover:text-green-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </button>
                                        <button wire:click="rejectStudent({{ $student->id }})" title="Reject"
                                            class="rounded-lg p-2 text-slate-500 transition hover:bg-red-50 hover:text-red-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </button>
                                    @endif
                                    <a href="{{ route('admin.students.show', $student) }}" title="View"
                                        class="rounded-lg p-2 text-slate-500 transition hover:bg-secondary-50 hover:text-secondary-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </a>
                                    <a href="{{ route('admin.students.edit', $student) }}" title="Edit"
                                        class="rounded-lg p-2 text-slate-500 transition hover:bg-primary-50 hover:text-primary-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                                    </a>
                                    <a href="{{ route('admin.results.entry', $student) }}" title="Enter Result"
                                        class="rounded-lg p-2 text-slate-500 transition hover:bg-accent-50 hover:text-accent-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/></svg>
                                    </a>
                                    @if ($student->currentResult())
                                        <a href="{{ route('admin.results.pdf', $student->currentResult()) }}" target="_blank" title="Generate PDF"
                                            class="rounded-lg p-2 text-slate-500 transition hover:bg-red-50 hover:text-red-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                        </a>
                                    @endif
                                    <button x-on:click="$store.confirmModal.show({
                                        title: 'Delete Student',
                                        message: 'Delete {{ addslashes($student->fullName()) }}? This cannot be undone.',
                                        confirmText: 'Delete',
                                        onConfirm: () => @this.call('deleteStudent', {{ $student->id }})
                                    })" title="Delete"
                                        class="rounded-lg p-2 text-slate-500 transition hover:bg-red-50 hover:text-red-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-admin.empty-state
                                    title="No students found"
                                    description="Adjust your filters or register a new student to get started."
                                    icon="users">
                                    <a href="{{ route('admin.students.create') }}" class="btn-primary">Add Student</a>
                                </x-admin.empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-4 py-3">
            {{ $this->students->links() }}
        </div>
    </div>
</div>
