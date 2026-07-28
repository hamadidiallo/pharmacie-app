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

<body class="bg-white font-sans text-ink antialiased">

    <div class="flex min-h-screen flex-col lg:flex-row">

        {{-- Panneau de marque --}}
        <div class="relative flex flex-col justify-between overflow-hidden bg-brand-900 p-10 text-white lg:w-[46%] lg:p-14">

            <div class="flex items-center gap-3">
                <span class="inline-flex size-11 items-center justify-center rounded-xl bg-leaf">
                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2"
                        stroke-linecap="round">
                        <path d="M12 6v12M6 12h12" />
                    </svg>
                </span>
                <span>
                    <span class="block whitespace-nowrap text-xl font-bold tracking-wide">GESTA PHARM</span>
                    <span class="block text-xs text-sidebar-muted">Gestion de pharmacie</span>
                </span>
            </div>

            <div class="mt-14 lg:mt-auto">

                <h2 class="mb-4 text-3xl font-bold leading-tight tracking-tight">
                    La gestion de votre officine,<br>claire et fiable.
                </h2>

                <p class="mb-7 max-w-[420px] text-[15px] leading-relaxed text-[#A9C7BD]">
                    Ventes, stock, expirations et statistiques réunis dans une interface
                    pensée pour le comptoir.
                </p>

                <dl class="flex flex-wrap gap-7">
                    @foreach ([[$references ?? 0, 'références'], [$caMois ?? 0, 'FCFA ce mois'], ['80mm', 'ticket imprimé']] as [$valeur, $libelle])
                        <div>
                            <dd class="num text-2xl font-bold">
                                {{ is_numeric($valeur) ? number_format($valeur, 0, ',', ' ') : $valeur }}
                            </dd>
                            <dt class="text-xs text-sidebar-muted">{{ $libelle }}</dt>
                        </div>
                    @endforeach
                </dl>

            </div>

        </div>

        {{-- Formulaire --}}
        <div class="flex flex-1 items-center justify-center p-8 sm:p-14">
            <div class="w-full max-w-[370px]">
                @yield('content')
            </div>
        </div>

    </div>

    @stack('scripts')
</body>

</html>
