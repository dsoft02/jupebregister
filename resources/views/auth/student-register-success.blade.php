<x-public-layout>
    <x-slot name="title">Registration Submitted</x-slot>

    <div class="mx-auto max-w-2xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="card overflow-hidden">
            <div class="bg-primary-700 px-6 py-10 text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-white/10">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="h-9 w-9 text-accent-400"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h1 class="mt-4 text-2xl font-bold text-white">Registration Submitted</h1>
                <p class="mt-2 text-primary-100">
                    Thank you, {{ $student->first_name }}. Your registration is now <strong>Pending</strong> review.
                </p>
            </div>

            <div class="p-6 sm:p-8">
                <dl class="space-y-3 rounded-2xl border border-slate-200 p-5 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500">Full Name</dt>
                        <dd class="font-semibold text-slate-800">{{ $student->fullName() }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500">Foundation Number</dt>
                        <dd class="font-mono font-semibold text-slate-800">{{ $student->foundation_number }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500">Subjects</dt>
                        <dd class="font-semibold text-slate-800">{{ implode(' / ', $student->chosenSubjectNames()) }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500">Status</dt>
                        <dd>
                            <span class="badge bg-secondary-100 text-secondary-800">Pending Review</span>
                        </dd>
                    </div>
                </dl>

                <div class="mt-6 rounded-2xl border border-secondary-100 bg-secondary-50 p-5 text-sm text-secondary-800">
                    <p class="font-semibold">What happens next?</p>
                    <p class="mt-1">
                        The Programme Officer will review your registration and publish your result.
                        Once published, you can verify it online using your Foundation number.
                    </p>
                </div>

                <div class="mt-6 flex flex-wrap justify-center gap-3">
                    <a href="{{ route('verify') }}" class="btn-primary">Verify a Result</a>
                    <a href="{{ route('home') }}" class="btn-outline">Back to Home</a>
                </div>
            </div>
        </div>
    </div>
</x-public-layout>
