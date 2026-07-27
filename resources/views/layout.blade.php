<!doctype html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>APP GESTAPHARM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite('resources/js/ventes/vente.js')
    <link rel="stylesheet" href="{{ asset('css/print.css') }}" media="print">
</head>
<style>
    .dashboard-card {

        transition: 0.3s ease;

        cursor: pointer;
    }

    .dashboard-card:hover {

        transform: translateY(-5px);

        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
    }
</style>

<body>
    {{-- <nav>
         @include('app.menu')
    </nav> --}}

    <main class="container">
        <x-component.alert />
        @yield('content')
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
    </script>
</body>

</html>
