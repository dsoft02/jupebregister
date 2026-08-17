<?php

use App\Enums\ResultGrade;
use App\Enums\ResultStatus;
use App\Enums\StudentStatus;
use App\Models\Result;
use App\Models\Student;
use App\Models\Subject;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {
    #[Computed]
    public function totalStudents(): int
    {
        return Student::count();
    }

    #[Computed]
    public function pendingResults(): int
    {
        return Result::where('status', ResultStatus::Draft)->count();
    }

    #[Computed]
    public function publishedResults(): int
    {
        return Result::where('status', ResultStatus::Published)->count();
    }

    #[Computed]
    public function averageScore(): string
    {
        $average = (float) Result::where('status', ResultStatus::Published)->avg('total_point') ?: 0;

        return number_format($average, 1);
    }

    #[Computed]
    public function pendingRegistrations(): int
    {
        return Student::where('status', StudentStatus::Pending)->count();
    }

    #[Computed]
    public function resultsEntered(): int
    {
        return Result::count();
    }

    #[Computed]
    public function completionPercent(): int
    {
        return $this->totalStudents > 0 ? round(($this->resultsEntered / $this->totalStudents) * 100) : 0;
    }

    #[Computed]
    public function gradeDistribution(): array
    {
        $counts = Result::where('status', ResultStatus::Published)
            ->selectRaw("grade_one as g")->get()
            ->merge(Result::where('status', ResultStatus::Published)->selectRaw('grade_two as g')->get())
            ->merge(Result::where('status', ResultStatus::Published)->selectRaw('grade_three as g')->get())
            ->pluck('g')
            ->countBy();

        $max = max(1, $counts->max());

        return collect(ResultGrade::cases())->map(fn (ResultGrade $grade) => [
            'grade' => $grade->value,
            'count' => $counts[$grade->value] ?? 0,
            'percent' => round((($counts[$grade->value] ?? 0) / $max) * 100),
        ])->all();
    }

    #[Computed]
    public function subjectDistribution(): array
    {
        $subjects = Subject::active()->get()->map(fn (Subject $s) => [
            'name' => $s->name,
            'count' => $s->totalStudents(),
        ])->sortByDesc('count')->take(6)->values()->all();

        return $subjects;
    }

    #[Computed]
    public function recentResults(): \Illuminate\Support\Collection
    {
        return Result::with('student')->latest()->take(6)->get();
    }

    #[Computed]
    public function recentStudents(): \Illuminate\Support\Collection
    {
        return Student::latest()->take(6)->get();
    }
};

?>

<div class="space-y-6">
    <x-admin.page-header
        title="Dashboard"
        eyebrow="PAAU Foundation School"
        description="Overview of student registrations and published JUPEB results.">
        <a href="{{ route('admin.students.create') }}" class="btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            New Student
        </a>
    </x-admin.page-header>

    @if ($this->pendingRegistrations > 0)
        <div class="flex items-start gap-3 rounded-2xl border border-secondary-200 bg-secondary-50 p-4">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mt-0.5 h-5 w-5 shrink-0 text-secondary-600"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
            <div>
                <p class="text-sm font-semibold text-secondary-800">
                    {{ $this->pendingRegistrations }} pending registration{{ $this->pendingRegistrations > 1 ? 's' : '' }} awaiting review.
                </p>
                <p class="text-sm text-secondary-700">
                    Review and approve new students to begin entering their results.
                </p>
            </div>
            <a href="{{ route('admin.students.index') }}" class="ml-auto shrink-0 text-sm font-semibold text-secondary-800 hover:text-secondary-900">Review now &rarr;</a>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <x-admin.stat-card label="Total Students" :value="number_format($this->totalStudents)" icon="users" tone="primary" sub="Registered candidates" />
        <x-admin.stat-card label="Pending Results" :value="number_format($this->pendingResults)" icon="clock" tone="accent" sub="Awaiting publication" />
        <x-admin.stat-card label="Published Results" :value="number_format($this->publishedResults)" icon="check-circle" tone="green" sub="Available for verification" />
        <x-admin.stat-card label="Average Score" :value="$this->averageScore" icon="chart" tone="secondary" sub="Across published results" />
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="card p-6 xl:col-span-2">
            <h3 class="mb-5 text-sm font-bold uppercase tracking-wider text-slate-500">Grade Distribution</h3>
            @forelse ($this->gradeDistribution as $item)
                <div class="mb-3 flex items-center gap-3">
                    <span class="w-6 text-right text-sm font-bold text-slate-700">{{ $item['grade'] }}</span>
                    <div class="h-3 flex-1 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full bg-primary-600" style="width: {{ $item['percent'] }}%"></div>
                    </div>
                    <span class="w-8 text-sm font-semibold text-slate-500">{{ $item['count'] }}</span>
                </div>
            @empty
                <p class="py-10 text-center text-sm text-slate-400">No published results yet.</p>
            @endforelse
        </div>

        <div class="card p-6">
            <h3 class="mb-5 text-sm font-bold uppercase tracking-wider text-slate-500">Result Completion</h3>
            <div class="flex items-center justify-center">
                <div class="relative h-36 w-36">
                    <svg viewBox="0 0 36 36" class="h-36 w-36 -rotate-90">
                        <circle cx="18" cy="18" r="15.9" fill="none" stroke="#E5E7EB" stroke-width="3.5" />
                        <circle cx="18" cy="18" r="15.9" fill="none" stroke="#0A8A4B" stroke-width="3.5"
                            stroke-dasharray="{{ $this->completionPercent }}, 100" stroke-linecap="round" />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-2xl font-bold text-slate-900">{{ $this->completionPercent }}%</span>
                        <span class="text-xs text-slate-400">complete</span>
                    </div>
                </div>
            </div>
            <p class="mt-4 text-center text-sm text-slate-500">
                {{ $this->totalStudents }} students &middot; results entered for those with published status
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="card xl:col-span-2">
            <div class="card-header flex items-center justify-between">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Recent Students</h3>
                <a href="{{ route('admin.students.index') }}" class="text-sm font-semibold text-primary-700 hover:text-primary-800">View all &rarr;</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($this->recentStudents as $student)
                            <tr class="hover:bg-slate-50">
                                <td class="td">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-primary-100 text-xs font-bold text-primary-800">
                                            {{ $student->initials() }}
                                        </div>
                                        <div>
                                            <p class="font-semibold text-slate-800">{{ $student->fullName() }}</p>
                                            <p class="text-xs text-slate-400">{{ $student->foundation_number }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="td hidden sm:table-cell">{{ implode(' / ', $student->chosenSubjectNames()) }}</td>
                                <td class="td text-right">
                                    <x-admin.status-badge :status="$student->status->value">{{ $student->status->label() }}</x-admin.status-badge>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-10 text-center text-sm text-slate-400">No students registered yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Subject Distribution</h3>
            </div>
            <div class="space-y-4 p-6">
                @forelse ($this->subjectDistribution as $item)
                    <div>
                        <div class="mb-1 flex items-center justify-between text-sm">
                            <span class="font-medium text-slate-700">{{ $item['name'] }}</span>
                            <span class="font-semibold text-slate-500">{{ $item['count'] }}</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                            @php $percent = $this->totalStudents > 0 ? round(($item['count'] / $this->totalStudents) * 100) : 0; @endphp
                            <div class="h-full rounded-full bg-accent-500" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="py-10 text-center text-sm text-slate-400">No subjects in use yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
