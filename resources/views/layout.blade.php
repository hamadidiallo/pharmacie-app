<!doctype html>
<html lang="fr" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titre', 'GESTA PHARM — Officine Médicale')</title>
    {{ Vite::fonts() }}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full bg-slate-50 font-sans text-slate-900 antialiased selection:bg-emerald-500 selection:text-white">

    <div class="flex h-full overflow-hidden">

        @include('app.menu')

        <div class="flex min-w-0 flex-1 flex-col overflow-hidden bg-slate-50">

            {{-- Topbar Glassmorphism Premium --}}
            <header class="sticky top-0 z-20 flex h-[68px] shrink-0 items-center justify-between gap-4 border-b border-slate-200/80 bg-white/85 px-4 sm:px-8 backdrop-blur-md">

                <div class="flex items-center gap-3 min-w-0 flex-1">
                    <button type="button" data-menu-toggle aria-expanded="false" aria-controls="sidebar"
                        class="btn-icon lg:hidden" aria-label="Ouvrir le menu">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round">
                            <path d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    @hasSection('topbar')
                        @yield('topbar')
                    @else
                        <div class="text-base font-bold text-slate-800 tracking-tight">@yield('titre-court', 'GESTA PHARM')</div>
                    @endif
                </div>

                {{-- Status Pills / Quick info --}}
                <div class="hidden md:flex items-center gap-2.5">
                    <div class="flex items-center gap-2 rounded-full border border-slate-200/70 bg-white px-3 py-1 text-xs font-medium text-slate-600 shadow-xs">
                        <span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Officine en ligne</span>
                    </div>
                </div>

            </header>

            {{-- Main Content Area --}}
            <main class="flex-1 overflow-y-auto p-4 sm:p-8">
                <div class="mx-auto max-w-7xl space-y-6">
                    <x-component.alert />
                    @yield('content')
                </div>
            </main>

        </div>

    </div>

    @stack('scripts')
</body>

</html>
