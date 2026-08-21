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
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-500/15 text-emerald-400">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-6 w-6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-bold text-white">PAAU Foundation</p>
                        <p class="truncate text-[11px] text-slate-400">JUPEB Result System</p>
                    </div>
                </div>

                <!-- Nav -->
                <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
                    <x-admin.nav-link href="{{ route('admin.dashboard') }}" :active="request()->routeIs('admin.dashboard')" icon="dashboard">
                        Dashboard
                    </x-admin.nav-link>

                    <x-admin.nav-link href="{{ route('admin.students.index') }}" :active="request()->routeIs('admin.students.*') && ! request()->routeIs('admin.students.trash')" icon="users">
                        Students
                    </x-admin.nav-link>

                    <x-admin.nav-link href="{{ route('admin.students.trash') }}" :active="request()->routeIs('admin.students.trash')" icon="trash">
                        Student Trash
                    </x-admin.nav-link>

                    <x-admin.nav-link href="{{ route('admin.subjects.index') }}" :active="request()->routeIs('admin.subjects.*')" icon="layers">
                        Subjects
                    </x-admin.nav-link>

                    <x-admin.nav-link href="{{ route('admin.results.index') }}" :active="request()->routeIs('admin.results.*')" icon="clipboard">
                        Results
                    </x-admin.nav-link>

                    <x-admin.nav-link href="{{ route('admin.import-export.create') }}" :active="request()->routeIs('admin.import-export.*') || request()->routeIs('admin.import.*') || request()->routeIs('admin.export') || request()->routeIs('admin.results.import') || request()->routeIs('admin.results.export')" icon="exchange">
                        Import &amp; Export
                    </x-admin.nav-link>

                    <x-admin.nav-link href="{{ route('verify') }}" icon="shield" target="_blank">
                        Verification
                    </x-admin.nav-link>

                    <x-admin.nav-link href="{{ route('admin.tokens.index') }}" :active="request()->routeIs('admin.tokens.*')" icon="key">
                        Verification Tokens
                    </x-admin.nav-link>

                    <x-admin.nav-link href="{{ route('admin.settings') }}" :active="request()->routeIs('admin.settings')" icon="settings">
                        Settings
                    </x-admin.nav-link>

                    <x-admin.nav-link href="{{ route('admin.profile') }}" :active="request()->routeIs('admin.profile')" icon="user-circle">
                        My Profile
                    </x-admin.nav-link>

                    @can('users.manage')
                        <x-admin.nav-link href="{{ route('admin.users.index') }}" :active="request()->routeIs('admin.users.*')" icon="user-circle">
                            Users
                        </x-admin.nav-link>
                    @endcan

                    @if (auth()->user()->hasRole('super_admin'))
                        <x-admin.nav-link href="{{ route('admin.migrations') }}" :active="request()->routeIs('admin.migrations')" icon="database">
                            Migrations
                        </x-admin.nav-link>
                    @endif

                    <div class="my-3 border-t border-white/10"></div>

                    <x-admin.nav-link href="{{ route('home') }}" icon="external-link" target="_blank">
                        Public Site &nearr;
                    </x-admin.nav-link>

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
                    <livewire:layout.user-menu />
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

        <script>
            window.__confirm = function(options) {
                window.dispatchEvent(new CustomEvent('open-confirm', { detail: options }));
            };
        </script>

        <x-admin.confirm-modal />

        {{-- Toast notification --}}
        <div x-data="toast()" x-on:flash-message.window="show($event.detail)" x-cloak
            class="fixed bottom-6 right-6 z-50 flex max-w-sm items-center gap-3 rounded-xl border px-5 py-3.5 shadow-lg transition-all duration-300"
            :class="[visible ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0 pointer-events-none',
                type === 'error' ? 'border-red-200 bg-red-50' : 'border-emerald-200 bg-emerald-50']">
            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full"
                :class="type === 'error' ? 'bg-red-100 text-red-600' : 'bg-emerald-100 text-emerald-600'">
                <svg x-show="type !== 'error'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <svg x-show="type === 'error'" x-cloak xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
            </span>
            <span class="text-sm font-medium text-slate-800" x-text="message"></span>
            <button x-on:click="visible = false" class="ml-2 shrink-0 rounded p-0.5 text-slate-400 hover:text-slate-700">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <script>
            function toast() {
                return {
                    visible: false,
                    message: '',
                    type: 'success',
                    timeout: null,
                    init() {
                        if (window.__pageFlash) {
                            this.show(window.__pageFlash);
                            window.__pageFlash = null;
                        }
                    },
                    show(detail) {
                        clearTimeout(this.timeout);
                        this.message = typeof detail === 'string' ? detail : detail.message;
                        this.type = (typeof detail === 'object' && detail?.type) || 'success';
                        this.visible = true;
                        this.timeout = setTimeout(() => { this.visible = false; }, 5000);
                    },
                };
            }
        </script>

        @livewireScripts
    </body>
</html>
