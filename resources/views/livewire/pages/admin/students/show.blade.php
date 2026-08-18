<?php

use App\Enums\ResultStatus;
use App\Enums\StudentStatus;
use App\Models\Student;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {
    public Student $student;

    public function mount(Student $student): void
    {
        $this->student = $student->load('subjectOne', 'subjectTwo', 'subjectThree', 'results');
    }

    public function approveStudent(): void
    {
        $this->authorize('update', $this->student);

        $this->student->update(['status' => StudentStatus::Approved]);

        $this->student->refresh();

        session()->flash('status', "Student {$this->student->fullName()} approved.");
    }

    public function rejectStudent(): void
    {
        $this->authorize('update', $this->student);

        $this->student->update(['status' => StudentStatus::Rejected]);

        $this->student->refresh();

        session()->flash('status', "Student {$this->student->fullName()} rejected.");
    }
};

?>

<div class="space-y-6">
    <x-admin.page-header
        title="Student Profile"
        eyebrow="Student Management"
        :description="$student->fullName().' ('.$student->foundation_number.')'">
        <a href="{{ route('admin.students.index') }}" class="btn-outline">&larr; Back</a>
        @if ($student->status->value === 'pending')
            <button wire:click="rejectStudent" class="btn-outline border-red-300 text-red-700 hover:bg-red-50">Reject</button>
            <button wire:click="approveStudent" class="btn-primary bg-green-600 hover:bg-green-700">Approve</button>
        @else
            <a href="{{ route('admin.students.edit', $student) }}" class="btn-secondary">Edit Student</a>
            <a href="{{ route('admin.results.entry', $student) }}" class="btn-accent">Enter Result</a>
            @if ($student->currentResult())
                <a href="{{ route('admin.results.pdf', $student->currentResult()) }}" target="_blank" class="btn-primary">Generate PDF</a>
            @endif
        @endif
    </x-admin.page-header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="card p-6">
            <div class="flex flex-col items-center text-center">
                @if ($student->passport)
                    <img src="{{ Storage::url($student->passport) }}" alt="Passport" class="h-32 w-32 rounded-2xl object-cover shadow">
                @else
                    <div class="flex h-32 w-32 items-center justify-center rounded-2xl bg-primary-100 text-4xl font-bold text-primary-800">
                        {{ $student->initials() }}
                    </div>
                @endif
                <h2 class="mt-4 text-lg font-bold text-slate-900">{{ $student->fullName() }}</h2>
                <p class="text-sm text-slate-500">{{ implode(' / ', $student->chosenSubjectNames()) }}</p>
                <div class="mt-3">
                    <x-admin.status-badge :status="$student->status->value">{{ $student->status->label() }}</x-admin.status-badge>
                </div>
            </div>
            <dl class="mt-6 space-y-3 border-t border-slate-100 pt-5 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">Foundation No.</dt>
                    <dd class="font-mono font-semibold text-slate-800">{{ $student->foundation_number }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">Examination No.</dt>
                    <dd class="font-mono font-semibold text-slate-800">{{ $student->examination_number }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">Session</dt>
                    <dd class="font-semibold text-slate-800">{{ $student->session }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">Registered</dt>
                    <dd class="font-semibold text-slate-800">{{ $student->registered_at?->format('d M Y') ?? '—' }}</dd>
                </div>
            </dl>
        </div>

        <div class="space-y-6 lg:col-span-2">
            <div class="card">
                <div class="card-header flex items-center justify-between">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Subjects</h3>
                    <span class="badge bg-secondary-50 text-secondary-700">{{ count($student->chosenSubjects()) }} Subject{{ count($student->chosenSubjects()) !== 1 ? 's' : '' }}</span>
                </div>
                <div class="divide-y divide-slate-50">
                    @foreach ($student->chosenSubjects() as $subject)
                        <div class="flex items-center gap-3 px-6 py-3.5">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-primary-50 text-xs font-bold text-primary-700">{{ $loop->iteration }}</span>
                            <p class="text-sm font-medium text-slate-700">{{ $subject->name }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            @forelse ($student->results->sortByDesc('session') as $result)
                <div class="card">
                    <div class="card-header flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Result</h3>
                            <span class="badge bg-slate-100 text-slate-700">{{ $result->session }}</span>
                        </div>
                        <x-admin.status-badge :status="$result->status->value">{{ $result->status->label() }}</x-admin.status-badge>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr>
                                    <th class="th">Subject</th>
                                    <th class="th text-center">Grade</th>
                                    <th class="th text-center">Points</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach ($result->subjects() as $subject)
                                    <tr>
                                        <td class="td">{{ $subject['subject'] }}</td>
                                        <td class="td text-center font-bold text-slate-800">{{ $subject['grade']->value }}</td>
                                        <td class="td text-center">{{ $subject['point'] }}</td>
                                    </tr>
                                @endforeach
                                <tr class="bg-slate-50">
                                    <td class="td font-semibold">Bonus Point</td>
                                    <td class="td"></td>
                                    <td class="td text-center font-semibold">{{ $result->bonus_point }}</td>
                                </tr>
                                <tr class="bg-primary-50">
                                    <td class="td font-bold text-primary-800">Total Points</td>
                                    <td class="td"></td>
                                    <td class="td text-center font-bold text-primary-800">{{ $result->total_point }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="flex items-center justify-between px-6 py-4">
                        <p class="text-sm text-slate-500">Grade Point</p>
                        <p class="text-lg font-bold text-slate-900">{{ $result->gradePointLabel() }}</p>
                    </div>
                    <div class="border-t border-slate-100 px-6 py-4">
                        <a href="{{ route('results.download', $result) }}"
                           class="inline-flex items-center gap-2 rounded-lg bg-secondary-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-secondary-800 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            Download Result
                        </a>
                    </div>
                </div>
            @empty
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Result</h3>
                    </div>
                    <div class="p-8">
                        <x-admin.empty-state
                            title="No result entered yet"
                            description="Enter grades for this student to generate their Statement of Result."
                            icon="clipboard">
                            <a href="{{ route('admin.results.entry', $student) }}" class="btn-primary">Enter Result</a>
                        </x-admin.empty-state>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>
