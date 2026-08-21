<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title.' — ' : '' }}{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles

        <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js" defer></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => { lucide.createIcons(); });
        </script>
    </head>
    <body class="flex min-h-screen flex-col font-sans antialiased">
        <header class="sticky top-0 z-50 border-b border-slate-200/70 bg-white/85 backdrop-blur-md">
            <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-5">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <img
                        src="{{ asset('paau-logo.png') }}"
                        alt="Prince Abubakar Audu University logo"
                        class="h-11 w-11 object-contain"
                    />
                    <span class="leading-tight">
                        <span class="block font-display text-[0.95rem] font-semibold tracking-tight text-slate-900 sm:text-base">
                            Prince Abubakar Audu University
                        </span>
                        <span class="block text-[0.72rem] font-medium tracking-wide text-slate-500">
                            Foundation School &middot; JUPEB Results
                        </span>
                    </span>
                </a>
                <nav class="flex items-center gap-2 sm:gap-3">
                    @auth
                        @hasanyrole('super_admin|programme_officer|director')
                            <a
                                href="{{ route('admin.dashboard') }}"
                                class="hidden rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-900 transition-colors hover:bg-slate-50 sm:inline-flex"
                            >
                                <i data-lucide="layout-dashboard" class="mr-1.5 h-4 w-4"></i>
                                Dashboard
                            </a>
                        @endhasanyrole
                        @if (auth()->user()->isStudent())
                            <a
                                href="{{ route('student.dashboard') }}"
                                class="rounded-full bg-gradient-brand px-4 py-2 text-sm font-semibold text-white shadow-soft transition-transform duration-200 hover:-translate-y-0.5 hover:shadow-lift"
                            >
                                My Dashboard
                            </a>
                        @endif
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-900 transition-colors hover:bg-slate-50"
                        >
                            <i data-lucide="log-in" class="h-4 w-4"></i>
                            Sign In
                        </a>
                    @endauth
                    <a
                        href="{{ route('register') }}"
                        class="hidden rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-900 transition-colors hover:bg-slate-50 sm:inline-flex"
                    >
                        Student Registration
                    </a>
                    <a
                        href="{{ route('verify') }}"
                        class="rounded-full bg-gradient-brand px-4 py-2 text-sm font-semibold text-white shadow-soft transition-transform duration-200 hover:-translate-y-0.5 hover:shadow-lift"
                    >
                        Verify Result
                    </a>
                </nav>
            </div>
        </header>

        <main class="flex-1">
            {{ $slot }}
        </main>

        <footer class="border-t border-slate-200 bg-white">
            <div class="mx-auto flex max-w-6xl flex-col gap-4 px-5 py-10 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('paau-logo.png') }}" alt="" class="h-9 w-9 object-contain" />
                    <p class="text-sm text-slate-500">
                        Prince Abubakar Audu University, P.M.B. 1008, Anyigba, Kogi State, Nigeria.
                    </p>
                </div>
                <p class="text-xs text-slate-500">
                    &copy; {{ now()->year }} Foundation School &middot; JUPEB
                </p>
            </div>
        </footer>

        @livewireScripts
    </body>
</html>
