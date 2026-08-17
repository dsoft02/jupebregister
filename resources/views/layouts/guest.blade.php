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

        <div class="flex h-screen">

            {{-- ─── Left branding panel ─────────────────────────── --}}
            <div class="relative hidden w-[45%] shrink-0 overflow-hidden lg:block xl:w-1/2">

                {{-- Background image --}}
                <img
                    src="{{ asset('paau-campus.jpg') }}"
                    alt=""
                    class="absolute inset-0 h-full w-full object-cover"
                />

                {{-- Layered overlays --}}
                {{-- Primary green --}}
                <div class="absolute inset-0 bg-[#0A8A4B]/70"></div>
                {{-- Blue gradient top-right for depth --}}
                <div class="absolute inset-0 bg-gradient-to-br from-[#006BB6]/20 via-transparent to-transparent"></div>
                {{-- Dark vignette edges --}}
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,transparent_40%,rgba(0,0,0,0.35)_100%)]"></div>

                {{-- Curved right edge divider --}}
                <svg class="absolute -right-1 top-0 h-full w-12 text-white" viewBox="0 0 48 800" preserveAspectRatio="none" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0,0 C48,100 48,700 0,800 L48,800 L48,0 Z" />
                </svg>

                {{-- Content --}}
                <div class="relative flex h-full flex-col justify-between p-10 xl:p-14">

                    {{-- Center (vertically centered) --}}
                    <div class="flex flex-1 flex-col justify-center">
                        <h1 class="font-display text-4xl font-bold leading-[1.1] tracking-tight text-white drop-shadow-lg xl:text-[2.8rem]">
                            JUPEB Result<br/>
                            <span class="text-[#C8A54B]">Management</span><br/>
                            System
                        </h1>
                        <p class="mt-5 max-w-md text-[0.95rem] leading-relaxed text-white/80">
                            Access your foundation school results, track your registration status, and download your official Statement of Result.
                        </p>

                        <div class="mt-8 flex flex-wrap items-center gap-x-7 gap-y-3 text-sm text-white/70">
                            <span class="flex items-center gap-2">
                                <i data-lucide="shield-check" class="h-4 w-4 text-accent-300"></i>
                                Secure Portal
                            </span>
                            <span class="flex items-center gap-2">
                                <i data-lucide="zap" class="h-4 w-4 text-accent-300"></i>
                                Instant Verification
                            </span>
                        </div>
                    </div>

                    {{-- Bottom-left back link --}}
                    <div>
                        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-sm font-medium text-white/70 transition-colors hover:text-white" wire:navigate>
                            <i data-lucide="arrow-left" class="h-4 w-4"></i>
                            Back to homepage
                        </a>
                    </div>
                </div>
            </div>

            {{-- ─── Right authentication panel ──────────────────── --}}
            <div class="flex w-full flex-col bg-white lg:w-[55%] xl:w-1/2">

                {{-- Mobile logo header --}}
                <div class="shrink-0 px-6 pt-5 lg:hidden">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5">
                        <img
                            src="{{ asset('paau-logo.png') }}"
                            alt="PAAU"
                            class="h-10 w-10 object-contain"
                        />
                        <span class="text-sm font-semibold text-slate-800">PAAU Foundation School</span>
                    </a>
                </div>

                {{-- Centered form area --}}
                <div class="flex flex-1 items-center justify-center overflow-y-auto px-6 py-10 sm:px-10">
                    <div class="w-full max-w-[440px]">

                        {{-- University branding (desktop) --}}
                        <div class="mb-8 hidden lg:block">
                            <a href="{{ route('home') }}" class="inline-flex items-center gap-3" wire:navigate>
                                <img
                                    src="{{ asset('paau-logo.png') }}"
                                    alt="Prince Abubakar Audu University logo"
                                    class="h-12 w-12 object-contain"
                                />
                                <span class="leading-tight">
                                    <span class="block text-sm font-bold text-[#0A8A4B]">Prince Abubakar Audu University</span>
                                    <span class="block text-xs text-slate-400">PAAU Foundation School</span>
                                </span>
                            </a>
                        </div>

                        {{-- Welcome heading --}}
                        <div class="mb-8">
                            <h2 class="font-display text-[1.65rem] font-bold tracking-tight text-slate-900">
                                Welcome back
                            </h2>
                            <p class="mt-1.5 text-sm text-slate-500">
                                Sign in to the admin dashboard
                            </p>
                        </div>

                        {{ $slot }}

                    </div>
                </div>
            </div>

        </div>

        @livewireScripts
    </body>
</html>
