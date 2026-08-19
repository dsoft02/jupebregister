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

                    <x-admin.nav-link href="{{ route('admin.students.index') }}" :active="request()->routeIs('admin.students.*')" icon="users">
                        Students
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

                    <x-admin.nav-link href="{{ route('verify') }}" icon="shield">
                        Verification
                    </x-admin.nav-link>

                    <x-admin.nav-link href="{{ route('admin.settings') }}" :active="request()->routeIs('admin.settings')" icon="settings">
                        Settings
                    </x-admin.nav-link>

                    @can('users.manage')
                        <x-admin.nav-link href="{{ route('admin.users.index') }}" :active="request()->routeIs('admin.users.*')" icon="user-circle">
                            Users
                        </x-admin.nav-link>
                    @endcan

                    <div class="my-3 border-t border-white/10"></div>

                    <x-admin.nav-link href="{{ route('home') }}" icon="external-link" target="_blank">
                        Public Site &nearr;
                    </x-admin.nav-link>
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

        @livewireScripts
    </body>
</html>
