<?php

use App\Enums\ResultStatus;
use App\Models\Result;
use App\Models\Student;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.public')] class extends Component {
    public string $query = '';

    public ?int $verifiedStudentId = null;

    public bool $searched = false;

    public function verify(): void
    {
        $this->searched = true;
        $this->verifiedStudentId = null;

        if (blank($this->query)) {
            return;
        }

        $query = trim($this->query);

        $student = Student::where('foundation_number', $query)
            ->orWhere('jupeb_number', $query)
            ->first();

        if (! $student) {
            return;
        }

        $result = Result::where('student_id', $student->id)
            ->where('status', ResultStatus::Published)
            ->first();

        if ($result) {
            $this->verifiedStudentId = $student->id;
        }
    }

    #[Computed]
    public function verified(): ?Student
    {
        return $this->verifiedStudentId
            ? Student::with('subjectOne', 'subjectTwo', 'subjectThree', 'result')->find($this->verifiedStudentId)
            : null;
    }
};

?>

<div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="mb-8 text-center">
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-secondary-700">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-8 w-8 text-white"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
        </div>
        <h1 class="text-3xl font-bold text-primary-800">Result Verification</h1>
        <p class="mt-2 text-sm text-slate-500">
            Enter a Foundation Number or JUPEB Number to verify a published result.
            Only published results are available for verification.
        </p>
    </div>

    <form wire:submit="verify" class="card flex flex-col gap-3 p-5 sm:flex-row">
        <div class="relative flex-1">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input type="text" wire:model="query" placeholder="e.g. PAAU/FS/001 or 23J/1234"
                class="input py-3 pl-11" autofocus>
        </div>
        <button type="submit" class="btn-primary px-8 py-3">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            Verify
        </button>
    </form>

    @if ($searched)
        @if ($this->verified)
            <div class="card mt-6 overflow-hidden">
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
                            <dd class="mt-1 text-sm font-bold text-slate-800">{{ $this->verified->fullName() }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Academic Session</dt>
                            <dd class="mt-1 text-sm font-bold text-slate-800">{{ $this->verified->session }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Subjects</dt>
                            <dd class="mt-1 text-sm font-bold text-slate-800">{{ implode(' / ', $this->verified->chosenSubjectNames()) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Point</dt>
                            <dd class="mt-1 text-lg font-bold text-primary-800">{{ $this->verified->result->total_point }} / 16</dd>
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
                                @foreach ($this->verified->result->subjects() as $subject)
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
                        <span class="badge bg-primary-100 text-primary-800">
                            Published {{ $this->verified->result->published_at?->format('d M Y') }}
                        </span>
                        <span class="badge bg-secondary-50 text-secondary-700">Verification Ref: {{ strtoupper($this->verified->foundation_number) }}</span>
                    </div>
                </div>
            </div>
        @else
            <div class="card mt-6 flex flex-col items-center p-8 text-center">
                <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-red-100">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-6 w-6 text-red-600"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                </div>
                <h3 class="text-base font-bold text-slate-900">No Published Result Found</h3>
                <p class="mt-1 max-w-sm text-sm text-slate-500">
                    No published result matches the number entered. The record may not exist yet or the result
                    has not been published. Please check the number and try again.
                </p>
            </div>
        @endif
    @endif
</div>
