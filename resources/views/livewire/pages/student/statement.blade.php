<?php

use App\Enums\ResultStatus;
use App\Models\Result;
use App\Models\Student;
use App\Services\SettingsService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.student')] class extends Component {
    public Student $student;

    public function mount(): void
    {
        $this->student = auth()->user()->student()->firstOrFail();
    }

    #[Computed]
    public function result(): ?Result
    {
        $currentSession = app(SettingsService::class)->get('current_session')
            ?? now()->format('Y').'/'.(now()->format('Y') + 1);

        return Result::where('student_id', $this->student->id)
            ->where('session', $currentSession)
            ->where('status', ResultStatus::Published)
            ->first();
    }
};

?>

<div class="mx-auto max-w-4xl">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-primary-800">Statement of Result</h1>
        <p class="mt-1 text-sm text-slate-500">
            Your official JUPEB statement of result for the current academic session.
        </p>
    </div>

    @if ($this->result)
        <div class="card overflow-hidden">
        <div class="flex items-center gap-3 border-b border-primary-100 bg-primary-50 px-6 py-4">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-6 w-6 text-primary-600"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                <p class="font-bold text-primary-800">Result Verified</p>
                <p class="text-xs text-primary-600">This result is authentic and published by PAAU Foundation School.</p>
            </div>
        </div>

        <div class="p-6">
            <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Student Name</dt>
                    <dd class="mt-1 text-sm font-bold text-slate-800">{{ $student->lastNameFirst() }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Academic Session</dt>
                    <dd class="mt-1 text-sm font-bold text-slate-800">{{ $this->result->session }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Subjects</dt>
                    <dd class="mt-1 text-sm font-bold text-slate-800">{{ implode(' / ', $student->chosenSubjectNames()) }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Point</dt>
                    <dd class="mt-1 text-lg font-bold text-primary-800">{{ $this->result->total_point }} / {{ $this->result->maximumPoints() }}</dd>
                </div>
            </dl>

            <div class="mt-6 overflow-hidden rounded-xl border border-slate-200">
                <table class="min-w-full">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="th">Subject</th>
                            <th class="th text-center">Grade</th>
                            <th class="th text-center">Points</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach ($this->result->subjects() as $subject)
                            <tr>
                                <td class="td">{{ $subject['subject'] }}</td>
                                <td class="td text-center font-bold">{{ $subject['grade']->value }}</td>
                                <td class="td text-center">{{ $subject['point'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-5 flex flex-wrap items-center justify-between gap-3">
                <span class="badge bg-secondary-50 text-secondary-700">Verification Token: {{ $student->verification_token }}</span>
                <span class="badge bg-primary-100 text-primary-800">
                    Published {{ $this->result->published_at?->format('d M Y') }}
                </span>
            </div>

            <div class="mt-5">
                <a href="{{ route('results.download', $this->result) }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-secondary-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-secondary-800">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    Download Statement of Result
                </a>
            </div>
        </div>
    </div>
    @else
        <div class="card flex flex-col items-center gap-3 px-6 py-16 text-center">
            <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-primary-50 text-primary-600">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-7 w-7"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </span>
            <h2 class="text-lg font-bold text-slate-800">Statement of Result Not Yet Published</h2>
            <p class="max-w-md text-sm leading-relaxed text-slate-500">
                Your Statement of Result has not been published yet. It will be available here
                as soon as your results have been approved and published by the school.
            </p>
        </div>
    @endif
</div>
