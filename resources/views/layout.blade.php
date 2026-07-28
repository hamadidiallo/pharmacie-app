<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titre', 'GESTA PHARM')</title>
    {{ Vite::fonts() }}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-canvas font-sans text-ink antialiased">

    <div class="flex h-screen overflow-hidden">

        @include('app.menu')

        <div class="flex min-w-0 flex-1 flex-col">

            <header class="flex h-topbar shrink-0 items-center gap-4 border-b border-line bg-white px-4 sm:px-6">

                <button type="button" data-menu-toggle aria-expanded="false" aria-controls="sidebar"
                    class="btn-icon lg:hidden" aria-label="Ouvrir le menu">
                    <svg class="size-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round">
                        <path d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                @hasSection('topbar')
                    @yield('topbar')
                @else
                    <div class="text-base font-bold">@yield('titre-court', 'GESTA PHARM')</div>
                @endif

            </header>

            <div class="flex-1 overflow-auto p-4 sm:p-6">
                <x-component.alert />
                @yield('content')
            </div>

        </div>

    </div>

    @stack('scripts')
</body>

</html>
