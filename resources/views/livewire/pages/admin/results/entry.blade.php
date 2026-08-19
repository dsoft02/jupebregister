<?php

use App\Actions\Results\UpsertResult;
use App\Enums\ResultGrade;
use App\Enums\ResultStatus;
use App\Models\Result;
use App\Models\Student;
use App\Services\GradeService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {
    public Student $student;

    public ?string $grade_one = null;

    public ?string $grade_two = null;

    public ?string $grade_three = null;

    public bool $publish = false;

    public function mount(Student $student): void
    {
        $this->authorize('enter', Result::class);

        $this->student = $student->load('subjectOne', 'subjectTwo', 'subjectThree', 'results');

        if ($result = $student->currentResult()) {
            $this->grade_one = $result->grade_one->value;
            $this->grade_two = $result->grade_two->value;
            $this->grade_three = $result->grade_three->value;
            $this->publish = $result->status === ResultStatus::Published;
        }
    }

    public function rules(): array
    {
        return [
            'grade_one' => ['required', Rule::enum(ResultGrade::class)],
            'grade_two' => ['required', Rule::enum(ResultGrade::class)],
            'grade_three' => ['required', Rule::enum(ResultGrade::class)],
        ];
    }

    #[Computed]
    public function preview(): array
    {
        $grades = app(GradeService::class);

        if (! $this->grade_one || ! $this->grade_two || ! $this->grade_three) {
            return ['point_one' => null, 'point_two' => null, 'point_three' => null, 'bonus_point' => null, 'total_point' => null];
        }

        return $grades->calculate($this->grade_one, $this->grade_two, $this->grade_three);
    }

    public function save(UpsertResult $action): void
    {
        $this->validate();

        $subjects = $this->student->chosenSubjects();

        $action->run($this->student->id, [
            'subject_one' => $subjects[0]->name,
            'subject_two' => $subjects[1]->name,
            'subject_three' => $subjects[2]->name,
            'grade_one' => $this->grade_one,
            'grade_two' => $this->grade_two,
            'grade_three' => $this->grade_three,
            'status' => $this->publish ? ResultStatus::Published->value : ResultStatus::Draft->value,
        ]);

        session()->flash('status', 'Result saved successfully.');

        $this->redirect(route('admin.students.show', $this->student), navigate: true);
    }

    #[Computed]
    public function grades(): array
    {
        return ResultGrade::cases();
    }

    #[Computed]
    public function result(): ?Result
    {
        return $this->student->currentResult();
    }
};

?>

<div class="mx-auto max-w-3xl space-y-6">
    <x-admin.page-header
        title="Enter Result"
        eyebrow="Result Management"
        :description="$student->fullName().' ('.$student->foundation_number.')'">
        <a href="{{ route('admin.students.show', $student) }}" class="btn-outline">&larr; Back</a>
    </x-admin.page-header>

    @if (session()->has('status'))
        <div class="rounded-2xl border border-primary-200 bg-primary-50 p-4 text-sm font-medium text-primary-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="card overflow-hidden">
        <div class="card-header flex items-center justify-between bg-primary-50">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-primary-700">Subject Combination</p>
                <p class="text-base font-bold text-primary-900">{{ implode(' / ', $student->chosenSubjectNames()) }}</p>
            </div>
            @if ($this->result)
                <x-admin.status-badge :status="$this->result->status->value">{{ $this->result->status->label() }}</x-admin.status-badge>
            @endif
        </div>

        <form wire:submit="save" class="p-6">
            <div class="space-y-4">
                @foreach (['one', 'two', 'three'] as $key)
                    @php $subject = $student->chosenSubjects()[$loop->index] ?? null; @endphp
                    <div class="flex items-center gap-4 rounded-xl border border-slate-200 p-4">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary-700 text-sm font-bold text-white">{{ $loop->iteration }}</span>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-slate-800">{{ $subject?->name }}</p>
                            <p class="text-xs text-slate-400">Enter the grade letter only &mdash; points calculate automatically.</p>
                        </div>
                        <select wire:model="grade_{{ $key }}" class="input w-36 text-center font-bold">
                            <option value="">Select Grade</option>
                            @foreach ($this->grades as $grade)
                                <option value="{{ $grade->value }}">{{ $grade->value }} &mdash; {{ $grade->points() }} pts</option>
                            @endforeach
                        </select>
                    </div>
                    @error('grade_'.$key)
                        <p class="text-sm font-medium text-red-600">{{ $message }}</p>
                    @enderror
                @endforeach
            </div>

            @if ($this->preview['total_point'] !== null)
                <div class="mt-6 rounded-2xl border border-primary-100 bg-primary-50 p-5">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-primary-800">Automatic Calculation</h3>
                        <span class="text-xs font-medium text-primary-700">Maximum {{ $this->preview['bonus_point'] ? 16 : 15 }}</span>
                    </div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-5">
                        <div class="rounded-xl bg-white p-3 text-center">
                            <p class="text-xs font-medium text-slate-400">Subject Points</p>
                            <p class="text-lg font-bold text-slate-800">{{ $this->preview['point_one'] + $this->preview['point_two'] + $this->preview['point_three'] }}</p>
                        </div>
                        <div class="rounded-xl bg-white p-3 text-center">
                            <p class="text-xs font-medium text-slate-400">Bonus</p>
                            <p class="text-lg font-bold {{ $this->preview['bonus_point'] ? 'text-primary-600' : 'text-slate-400' }}">{{ $this->preview['bonus_point'] }}</p>
                        </div>
                        <div class="rounded-xl bg-white p-3 text-center">
                            <p class="text-xs font-medium text-slate-400">Total</p>
                            <p class="text-lg font-bold text-primary-800">{{ $this->preview['total_point'] }}</p>
                        </div>
                        <div class="rounded-xl bg-white p-3 text-center sm:col-span-2">
                            <p class="text-xs font-medium text-slate-400">Grade Point</p>
                            <p class="text-lg font-bold text-secondary-800">{{ $this->preview['total_point'] }}/{{ $this->preview['bonus_point'] ? 16 : 15 }}</p>
                        </div>
                    </div>
                    @if ($this->preview['bonus_point'] === 0 && ($this->preview['point_one'] + $this->preview['point_two'] + $this->preview['point_three']) > 0)
                        <p class="mt-3 text-xs text-primary-700">
                            Bonus withheld &mdash; all three subjects must be passed (A&ndash;E) to earn the extra point.
                        </p>
                    @endif
                </div>
            @endif

            <div class="mt-6 flex flex-col items-center justify-between gap-4 border-t border-slate-100 pt-5 sm:flex-row">
                <label class="flex cursor-pointer items-center gap-2 text-sm font-medium text-slate-700">
                    <input type="checkbox" wire:model="publish" class="rounded border-slate-300 text-primary-700 focus:ring-primary-500">
                    Publish immediately (visible for online verification)
                </label>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.students.show', $student) }}" class="btn-outline">Cancel</a>
                    <button type="submit" class="btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.75V16.5L12 14.25 7.5 16.5V3.75m9 0H18A2.25 2.25 0 0120.25 6v12A2.25 2.25 0 0118 20.25H6A2.25 2.25 0 013.75 18V6A2.25 2.25 0 016 3.75h1.5m9 0h-9"/></svg>
                        Save Result
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
