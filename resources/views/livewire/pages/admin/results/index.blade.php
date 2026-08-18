<?php

use App\Actions\Logs\LogActivity;
use App\Actions\Results\UpsertResult;
use App\Enums\ResultStatus;
use App\Models\Result;
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

    public array $selected = [];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function toggleSelectAll(): void
    {
        $this->selected = $this->selected === $this->resultIds()
            ? []
            : $this->resultIds();
    }

    public function resultIds(): array
    {
        return $this->results->pluck('id')->toArray();
    }

    public function togglePublish(Result $result, UpsertResult $action): void
    {
        $this->authorize('publish', $result);

        if ($result->status === ResultStatus::Published) {
            $result->update(['status' => ResultStatus::Draft]);
        } else {
            $action->publish($result);
        }

        $this->dispatch('flash-message', message: 'Result status updated.');
    }

    public function bulkPublish(UpsertResult $action): void
    {
        $this->authorize('publish', Result::class);

        $results = Result::whereIn('id', $this->selected)
            ->where('status', '!=', ResultStatus::Published)
            ->get();

        foreach ($results as $result) {
            $action->publish($result);
        }

        $count = $results->count();
        $this->selected = [];
        session()->flash('status', "{$count} result(s) published.");
    }

    public function bulkUnpublish(): void
    {
        $this->authorize('publish', Result::class);

        $count = Result::whereIn('id', $this->selected)
            ->where('status', ResultStatus::Published)
            ->update(['status' => ResultStatus::Draft]);

        $this->selected = [];
        session()->flash('status', "{$count} result(s) unpublished.");
    }

    #[Computed]
    public function results()
    {
        return Result::query()
            ->with('student.subjectOne', 'student.subjectTwo', 'student.subjectThree', 'createdBy')
            ->when($this->search !== '', fn ($q) => $q->whereHas('student', fn ($s) => $s->search($this->search)))
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->latest()
            ->paginate(15);
    }

    #[Computed]
    public function statuses(): array
    {
        return ResultStatus::cases();
    }
};

?>

<div class="space-y-6">
    <x-admin.page-header
        title="Results"
        eyebrow="Result Management"
        description="Review, publish and generate official statements for entered results." />

    <div class="card p-4">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-12">
            <div class="md:col-span-8">
                <div class="relative">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by student name or number…" class="input pl-10">
                </div>
            </div>
            <div class="md:col-span-4">
                <select wire:model.live="status" class="input">
                    <option value="">All Statuses</option>
                    @foreach ($this->statuses as $option)
                        <option value="{{ $option->value }}">{{ $option->label() }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="card overflow-hidden">
        @if (count($selected) > 0)
            <div class="flex items-center gap-3 border-b border-primary-100 bg-primary-50 px-4 py-3">
                <span class="text-sm font-semibold text-primary-800">{{ count($selected) }} selected</span>
                <div class="flex items-center gap-2">
                    <button x-on:click="$store.confirmModal.show({
                        title: 'Publish Results',
                        message: 'Publish {{ count($selected) }} result(s)?',
                        confirmText: 'Publish',
                        onConfirm: () => @this.call('bulkPublish')
                    })" class="rounded-lg bg-green-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-green-700">Publish</button>
                    <button x-on:click="$store.confirmModal.show({
                        title: 'Unpublish Results',
                        message: 'Unpublish {{ count($selected) }} result(s)?',
                        confirmText: 'Unpublish',
                        onConfirm: () => @this.call('bulkUnpublish')
                    })" class="rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-700">Unpublish</button>
                    <button x-on:click="$store.confirmModal.show({
                        title: 'Download Results',
                        message: 'Download {{ count($selected) }} result(s) as a ZIP file?',
                        confirmText: 'Download',
                        onConfirm: () => {
                            const form = document.getElementById('bulk-download-form');
                            const container = document.getElementById('bulk-download-ids');
                            container.innerHTML = '';
                            $wire.selected.forEach(id => {
                                const input = document.createElement('input');
                                input.type = 'hidden';
                                input.name = 'ids[]';
                                input.value = id;
                                container.appendChild(input);
                            });
                            form.submit();
                        }
                    })" class="rounded-lg bg-primary-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-primary-700">Download ZIP</button>
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
                                :checked="$wire.selected.length > 0 && $wire.selected.length === {{ $this->results->count() }}"
                                class="rounded border-slate-300 text-primary-700 focus:ring-primary-500">
                        </th>
                        <th class="th">Student</th>
                        <th class="th hidden md:table-cell">Session</th>
                        <th class="th hidden lg:table-cell">Grades</th>
                        <th class="th text-center">Total</th>
                        <th class="th">Status</th>
                        <th class="th hidden xl:table-cell">Published</th>
                        <th class="th text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($this->results as $result)
                        <tr class="hover:bg-slate-50" :class="$wire.selected.includes({{ $result->id }}) && 'bg-primary-50'">
                            <td class="td w-10">
                                <input type="checkbox" wire:model.live="selected" value="{{ $result->id }}"
                                    class="rounded border-slate-300 text-primary-700 focus:ring-primary-500">
                            </td>
                            <td class="td">
                                <p class="font-semibold text-slate-800">{{ $result->student->fullName() }}</p>
                                <p class="text-xs text-slate-400">{{ $result->student->foundation_number }}</p>
                            </td>
                            <td class="td hidden md:table-cell">
                                <span class="badge bg-slate-100 text-slate-700">{{ $result->session }}</span>
                            </td>
                            <td class="td hidden lg:table-cell">
                                <div class="flex items-center gap-2">
                                    @foreach ($result->subjects() as $subject)
                                        <span class="rounded-lg bg-slate-100 px-2 py-1 text-xs font-bold text-slate-700">
                                            {{ $subject['grade']->value }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="td text-center">
                                <span class="text-base font-bold text-primary-800">{{ $result->total_point }}</span>
                                <span class="text-xs text-slate-400">/16</span>
                            </td>
                            <td class="td">
                                <x-admin.status-badge :status="$result->status->value">{{ $result->status->label() }}</x-admin.status-badge>
                            </td>
                            <td class="td hidden xl:table-cell">
                                {{ $result->published_at?->format('d M Y') ?? '—' }}
                            </td>
                            <td class="td text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.students.show', $result->student) }}" title="View Student"
                                        class="rounded-lg p-2 text-slate-500 transition hover:bg-secondary-50 hover:text-secondary-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </a>
                                    <a href="{{ route('admin.results.entry', $result->student) }}" title="Edit Result"
                                        class="rounded-lg p-2 text-slate-500 transition hover:bg-primary-50 hover:text-primary-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                                    </a>
                                    <a href="{{ route('admin.results.pdf', $result) }}" target="_blank" title="Statement of Result"
                                        class="rounded-lg p-2 text-slate-500 transition hover:bg-red-50 hover:text-red-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                    </a>
                                    <a href="{{ route('results.download', $result) }}" target="_blank" title="Download Result"
                                        class="rounded-lg p-2 text-slate-500 transition hover:bg-emerald-50 hover:text-emerald-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                    </a>
                                    @can('results.publish')
                                        <button wire:click="togglePublish({{ $result->id }})"
                                            title="{{ $result->status === \App\Enums\ResultStatus::Published ? 'Unpublish' : 'Publish' }}"
                                            class="rounded-lg p-2 text-slate-500 transition hover:bg-primary-50 hover:text-primary-700">
                                            @if ($result->status === \App\Enums\ResultStatus::Published)
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                            @endif
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <x-admin.empty-state
                                    title="No results found"
                                    description="Enter results for students to see them here."
                                    icon="clipboard" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-4 py-3">
            {{ $this->results->links() }}
        </div>
    </div>

    <form id="bulk-download-form" action="{{ route('admin.results.bulk-download') }}" method="POST" class="hidden">
        @csrf
        <div id="bulk-download-ids"></div>
    </form>
</div>
