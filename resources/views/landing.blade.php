<x-public-layout>
    <x-slot name="title">PAAU JUPEB Result Management System</x-slot>

    {{-- ─── Hero ────────────────────────────────────────────── --}}
    <section class="relative overflow-hidden bg-gradient-soft">
        <div class="mx-auto grid max-w-6xl items-center gap-12 px-5 py-20 lg:grid-cols-[1.05fr_0.95fr] lg:py-28">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3.5 py-1.5 text-xs font-semibold tracking-wide text-secondary-700 uppercase">
                    <i data-lucide="graduation-cap" class="h-3.5 w-3.5"></i>
                    Joint Universities Preliminary Examinations Board
                </span>
                <h1 class="mt-6 font-display text-4xl font-bold leading-[1.08] tracking-tight text-slate-900 sm:text-5xl lg:text-[3.4rem]">
                    The official <span class="text-gradient-brand">JUPEB Result</span> Management System
                </h1>
                <p class="mt-5 max-w-xl text-lg leading-relaxed text-slate-500">
                    Prince Abubakar Audu University, Anyigba &mdash; Foundation School. One secure portal for
                    candidate registration, result processing and institutional verification.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a
                        href="{{ route('register') }}"
                        class="group inline-flex items-center gap-2 rounded-full bg-gradient-brand px-6 py-3 text-sm font-semibold text-white shadow-soft transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lift"
                    >
                        Student Registration
                        <i data-lucide="arrow-right" class="h-4 w-4 transition-transform group-hover:translate-x-1"></i>
                    </a>
                    <a
                        href="{{ route('verify') }}"
                        class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-6 py-3 text-sm font-semibold text-slate-900 transition-colors hover:bg-slate-100"
                    >
                        Verify a Result
                    </a>
                </div>
            </div>

            <div class="relative">
                <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-soft">
                    <img
                        src="{{ asset('paau-campus.jpg') }}"
                        alt="Prince Abubakar Audu University campus building in Anyigba"
                        class="h-72 w-full object-cover sm:h-96"
                        loading="lazy"
                    />
                </div>
            </div>
        </div>
    </section>

    {{-- ─── How It Works ────────────────────────────────────── --}}
    <section class="mx-auto max-w-6xl px-5 py-24 lg:py-28">
        <div class="max-w-2xl">
            <p class="text-sm font-semibold tracking-widest text-primary-700 uppercase">
                How it works
            </p>
            <h2 class="mt-3 font-display text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                Four steps from registration to result
            </h2>
        </div>

        <ol class="relative mt-14 grid gap-10 md:grid-cols-4 md:gap-6">
            <span
                aria-hidden
                class="absolute top-7 left-7 hidden h-px w-[calc(100%-3.5rem)] bg-gradient-brand opacity-40 md:block"
            ></span>
            <span
                aria-hidden
                class="absolute top-7 bottom-7 left-7 w-px bg-gradient-brand opacity-40 md:hidden"
            ></span>

            @php
                $steps = [
                    ['icon' => 'user-plus',     'title' => 'Register Online',           'text' => 'Create your Foundation School profile with your JUPEB registration number and personal details.'],
                    ['icon' => 'clipboard-list', 'title' => 'Admin Reviews Information', 'text' => 'The Office of the Director validates your record against the official Foundation School register.'],
                    ['icon' => 'file-search',    'title' => 'Results Are Processed',     'text' => 'Subject scores are compiled, moderated and approved by the examinations board.'],
                    ['icon' => 'download',       'title' => 'Download or Verify Result',  'text' => 'Access your statement of result, or let institutions verify its authenticity instantly.'],
                ];
            @endphp

            @foreach ($steps as $i => $step)
                <li class="group relative flex gap-5 md:block">
                    <span class="relative z-10 flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl border border-slate-200 bg-white text-primary-700 shadow-soft transition-all duration-200 group-hover:-translate-y-1 group-hover:bg-gradient-brand group-hover:text-white group-hover:shadow-lift">
                        <i data-lucide="{{ $step['icon'] }}" class="h-6 w-6"></i>
                    </span>
                    <div class="md:mt-6">
                        <p class="text-xs font-semibold tracking-widest text-slate-400 uppercase">
                            Step {{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}
                        </p>
                        <h3 class="mt-1.5 font-display text-lg font-semibold text-slate-900">{{ $step['title'] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-500">{{ $step['text'] }}</p>
                    </div>
                </li>
            @endforeach
        </ol>
    </section>
</x-public-layout>
