<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-screen">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'JUPEB Portal') }} — Login</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles

        <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js" defer></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => { lucide.createIcons(); });
        </script>
    </head>
    <body class="h-screen overflow-hidden font-sans antialiased">

        <div class="grid h-screen lg:grid-cols-[1.2fr_0.8fr]">

            {{-- ─── Left branding panel ─────────────────────────── --}}
            <div
                class="relative hidden lg:flex lg:flex-col"
                style="background-image: url('{{ asset('paau-campus.jpg') }}'); background-size: cover; background-position: center;"
            >
                {{-- Brand gradient overlay --}}
                <div class="absolute inset-0 bg-gradient-brand opacity-85"></div>

                {{-- Top: Back link --}}
                <div class="relative z-10 px-8 py-6">
                    <a
                        href="{{ route('home') }}"
                        class="group inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-2 text-sm font-medium text-white backdrop-blur-sm transition-colors hover:bg-white/20"
                        wire:navigate
                    >
                        <i data-lucide="arrow-left" class="h-4 w-4 transition-transform group-hover:-translate-x-0.5"></i>
                        Back to Homepage
                    </a>
                </div>

                {{-- Center: Heading + text --}}
                <div class="relative z-10 flex flex-1 flex-col justify-center px-16 py-12">
                    <div class="max-w-md">
                        <h2 class="text-3xl font-bold leading-tight text-white sm:text-4xl">
                            Foundation School Administration
                        </h2>
                        <p class="mt-4 text-base leading-relaxed text-white/90">
                            Authorized personnel only. Manage candidate records, process results, and issue
                            official JUPEB statements from one secure portal.
                        </p>
                    </div>
                </div>
            </div>

            {{-- ─── Right authentication panel ──────────────────── --}}
            <div class="flex flex-col bg-white px-5 py-6 sm:px-12">

                {{-- Centered form area --}}
                <div class="flex flex-1 flex-col items-center justify-center">
                    <div class="w-full max-w-sm">

                        {{-- Logo + heading --}}
                        <div class="flex flex-col items-center text-center">
                            <img
                                src="{{ asset('paau-logo.png') }}"
                                alt="Prince Abubakar Audu University logo"
                                class="h-20 w-20 object-contain"
                            />
                            <div class="mt-5">
                                <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                                    Admin Login
                                </h1>
                                <p class="mt-1.5 text-sm text-slate-500">
                                    Enter your credentials to access the dashboard.
                                </p>
                            </div>
                        </div>

                        {{-- Form slot --}}
                        <div class="mt-10">
                            {{ $slot }}
                        </div>

                        <p class="mt-8 text-center text-xs text-slate-400">
                            Having trouble signing in? Contact the Office of the Director.
                        </p>
                    </div>
                </div>

                {{-- Mobile back link (bottom) --}}
                <div class="shrink-0 pb-4 text-center lg:hidden">
                    <a
                        href="{{ route('home') }}"
                        class="group inline-flex items-center gap-2 text-sm font-medium text-slate-500 transition-colors hover:text-slate-900"
                        wire:navigate
                    >
                        <i data-lucide="arrow-left" class="h-4 w-4 transition-transform group-hover:-translate-x-0.5"></i>
                        Back to Homepage
                    </a>
                </div>
            </div>

        </div>

        @livewireScripts
    </body>
</html>
