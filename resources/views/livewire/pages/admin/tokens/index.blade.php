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

    public bool $generating = false;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function clearSelection(): void
    {
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
        return $this->students->pluck('id')->toArray();
    }

    public function generateToken(Student $student): void
    {
        $this->authorize('update', $student);

        $student->generateVerificationToken();

        session()->flash('status', "Token regenerated for {$student->fullName()}.");
    }

    public function generateSelectedTokens(): void
    {
        $this->authorize('update', Student::class);

        $students = Student::whereIn('id', $this->selected)->get();

        foreach ($students as $student) {
            $student->generateVerificationToken();
        }

        $count = $students->count();
        $this->selected = [];
        session()->flash('status', "{$count} token(s) generated successfully.");
    }

    public function generateAllTokens(): void
    {
        $this->authorize('update', Student::class);

        $students = Student::all();

        foreach ($students as $student) {
            $student->generateVerificationToken();
        }

        $count = $students->count();
        session()->flash('status', "{$count} token(s) generated for all students.");
    }

    #[Computed]
    public function students()
    {
        return Student::query()
            ->with('subjectOne', 'subjectTwo', 'subjectThree')
            ->when($this->search !== '', fn ($q) => $q->search($this->search))
            ->latest()
            ->paginate(15);
    }
};

?>

<div class="space-y-6">
    <x-admin.page-header
        title="Verification Tokens"
        eyebrow="Student Verification"
        description="Generate and manage verification tokens for students to access their results.">
        <button x-on:click="$store.confirmModal.show({
            title: 'Generate All Tokens',
            message: 'This will regenerate verification tokens for ALL students. Existing tokens will be invalidated. Continue?',
            confirmText: 'Generate All',
            onConfirm: () => @this.call('generateAllTokens')
        })" class="btn-outline">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/></svg>
            Generate All Tokens
        </button>
    </x-admin.page-header>

    @if (session()->has('status'))
        <div class="rounded-2xl border border-primary-200 bg-primary-50 p-4 text-sm font-medium text-primary-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="card p-4">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-12">
            <div class="md:col-span-8">
                <div class="relative">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by name or number..." class="input pl-10">
                </div>
            </div>
        </div>
        <div class="mt-3 flex items-center justify-between text-sm text-slate-500">
            <span>{{ $this->students->total() }} student{{ $this->students->total() !== 1 ? 's' : '' }} found</span>
            @if ($search)
                <button wire:click="$set('search', '')" class="font-semibold text-primary-700 hover:text-primary-800">Clear search</button>
            @endif
        </div>
    </div>

    <div class="card overflow-hidden">
        @if (count($selected) > 0)
            <div class="flex items-center gap-3 border-b border-primary-100 bg-primary-50 px-4 py-3">
                <span class="text-sm font-semibold text-primary-800">{{ count($selected) }} selected</span>
                <div class="flex items-center gap-2">
                    <button x-on:click="$store.confirmModal.show({
                        title: 'Generate Tokens',
                        message: 'Generate verification tokens for {{ count($selected) }} student(s)?',
                        confirmText: 'Generate',
                        onConfirm: () => @this.call('generateSelectedTokens')
                    })" class="rounded-lg bg-primary-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-primary-700">Generate Tokens</button>
                </div>
                <button wire:click="clearSelection" class="ml-auto text-xs font-semibold text-primary-600 hover:text-primary-800">Clear</button>
            </div>
        @endif
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="border-b border-slate-100">
                    <tr>
                        <th class="th w-10">
                            <input type="checkbox" x-on:click="$wire.toggleSelectAll()"
                                :checked="$wire.selected.length > 0 && $wire.selected.length === {{ $this->students->count() }}"
                                class="rounded border-slate-300 text-primary-700 focus:ring-primary-500">
                        </th>
                        <th class="th">Student</th>
                        <th class="th">Foundation No.</th>
                        <th class="th hidden md:table-cell">Verification Token</th>
                        <th class="th text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($this->students as $student)
                        <tr class="hover:bg-slate-50" :class="$wire.selected.includes({{ $student->id }}) && 'bg-primary-50'">
                            <td class="td w-10">
                                <input type="checkbox" wire:model.live="selected" value="{{ $student->id }}"
                                    class="rounded border-slate-300 text-primary-700 focus:ring-primary-500">
                            </td>
                            <td class="td">
                                <p class="font-semibold text-slate-800">{{ $student->fullName() }}</p>
                                <p class="text-xs text-slate-400">{{ $student->foundation_number }}</p>
                            </td>
                            <td class="td font-mono text-xs">{{ $student->foundation_number }}</td>
                            <td class="td hidden md:table-cell">
                                @if ($student->verification_token)
                                    <code class="rounded bg-slate-100 px-2 py-1 font-mono text-[11px] text-slate-600 select-all">{{ $student->verification_token }}</code>
                                @else
                                    <span class="text-xs text-slate-400 italic">No token</span>
                                @endif
                            </td>
                            <td class="td text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button x-on:click="$store.confirmModal.show({
                                        title: 'Regenerate Token',
                                        message: 'Regenerate token for {{ addslashes($student->fullName()) }}? The old token will be invalidated.',
                                        confirmText: 'Regenerate',
                                        onConfirm: () => @this.call('generateToken', {{ $student->id }})
                                    })" title="Regenerate Token"
                                        class="rounded-lg p-2 text-slate-500 transition hover:bg-primary-50 hover:text-primary-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-admin.empty-state
                                    title="No students found"
                                    description="Adjust your search or register students first."
                                    icon="users" />
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
