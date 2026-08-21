<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title.' — ' : '' }}{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="h-full font-sans antialiased">
        <div class="min-h-full">
            <!-- Sidebar -->
            <aside class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col border-r border-white/10 bg-slate-900">
                <!-- Brand -->
                <div class="flex h-16 shrink-0 items-center gap-3 border-b border-white/10 px-5">
                    <img src="{{ asset('paau-logo.png') }}" alt="" class="h-10 w-10 rounded-xl bg-white object-contain p-1">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-bold text-white">PAAU Foundation</p>
                        <p class="truncate text-[11px] text-slate-400">Student Portal</p>
                    </div>
                </div>

                <!-- Nav -->
                <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
                    <x-admin.nav-link href="{{ route('student.profile') }}" :active="request()->routeIs('student.profile')" icon="user-circle">
                        My Profile
                    </x-admin.nav-link>

                    <x-admin.nav-link href="{{ route('student.statement') }}" :active="request()->routeIs('student.statement')" icon="clipboard">
                        Statement of Result
                    </x-admin.nav-link>

                    <div class="my-3 border-t border-white/10"></div>

                    <a href="{{ route('home') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-400 transition hover:bg-white/5 hover:text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5 shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                        <span class="truncate">Public Site &nearr;</span>
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="group flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-400 transition hover:bg-red-500/10 hover:text-red-300">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5 shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                            </svg>
                            <span class="truncate">Log Out</span>
                        </button>
                    </form>
                </nav>

                <!-- User -->
                <div class="border-t border-white/10 p-4">
                    <livewire:layout.student-menu />
                </div>
            </aside>

            <!-- Main -->
            <div class="pl-64">
                <main class="min-h-screen bg-[#F8FAFC]">
                    <div class="px-8 py-8">
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>

        {{-- Toast notification --}}
        <div x-data="toast()" x-on:flash-message.window="show($event.detail.message)" x-cloak
            class="fixed bottom-6 right-6 z-50 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-3.5 shadow-lg transition-all duration-300"
            :class="visible ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0 pointer-events-none'">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5 shrink-0 text-emerald-600">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-sm font-medium text-emerald-800" x-text="message"></span>
            <button x-on:click="visible = false" class="ml-2 shrink-0 rounded p-0.5 text-emerald-600 hover:text-emerald-800">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <script>
            function toast() {
                return {
                    visible: false,
                    message: '',
                    timeout: null,
                    show(msg) {
                        clearTimeout(this.timeout);
                        this.message = msg;
                        this.visible = true;
                        this.timeout = setTimeout(() => { this.visible = false; }, 4000);
                    },
                };
            }
        </script>

        @livewireScripts
    </body>
</html>
