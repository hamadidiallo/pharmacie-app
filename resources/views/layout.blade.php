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

<body class="min-h-screen bg-paper font-sans antialiased">

    @include('app.menu')

    <main class="mx-auto w-full max-w-6xl px-4 py-8 sm:px-6">
        <x-component.alert />
        @yield('content')
    </main>

    @stack('scripts')
</body>

</html>
