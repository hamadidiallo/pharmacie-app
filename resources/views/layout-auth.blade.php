<!doctype html>
<html lang="fr" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titre', 'GESTA PHARM')</title>
    {{ Vite::fonts() }}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full bg-slate-50 font-sans text-slate-900 antialiased selection:bg-emerald-500 selection:text-white">

    <div class="flex min-h-screen flex-col lg:flex-row">

        {{-- Panneau de marque gauche --}}
        <div class="relative flex flex-col justify-between overflow-hidden bg-gradient-to-br from-[#091512] via-[#0d1e19] to-[#0a1714] p-8 text-white sm:p-12 lg:w-[48%] lg:p-16">

            {{-- Halo lumineux --}}
            <div class="pointer-events-none absolute -left-20 -top-20 size-96 rounded-full bg-emerald-500/15 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-20 -right-20 size-96 rounded-full bg-teal-500/10 blur-3xl"></div>

            {{-- Logo & Marque --}}
            <div class="relative flex items-center gap-3.5">
                <div class="flex size-12 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-400 to-emerald-600 shadow-lg shadow-emerald-500/25 ring-1 ring-white/20">
                    <svg class="size-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                        <path d="M12 5v14M5 12h14" />
                    </svg>
                </div>
                <div>
                    <span class="block text-xl font-black tracking-tight text-white">GESTA <span class="text-emerald-400">PHARM</span></span>
                    <span class="block text-xs font-medium text-emerald-200/70">Système Officinal Haute Précision</span>
                </div>
            </div>

            {{-- Message central --}}
            <div class="relative my-12 lg:my-auto">
                <div class="inline-flex items-center gap-2 rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-300 ring-1 ring-emerald-500/30 mb-6 backdrop-blur-xs">
                    <span class="size-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    Solution de gestion pharmaceutique
                </div>

                <h2 class="mb-4 text-3xl sm:text-4xl font-black leading-tight tracking-tight text-white">
                    L'excellence officinale,<br>
                    <span class="bg-gradient-to-r from-emerald-300 via-teal-200 to-emerald-100 bg-clip-text text-transparent">au service de vos patients.</span>
                </h2>

                <p class="mb-8 max-w-[440px] text-sm leading-relaxed text-slate-300">
                    Encaissement ultra-rapide, gestion prédictive des stocks, alertes d'expiration automatiques et traçabilité complète des transactions.
                </p>

                <div class="grid grid-cols-3 gap-4 border-t border-white/10 pt-6">
                    <div class="rounded-xl bg-white/5 p-3.5 ring-1 ring-white/10 backdrop-blur-xs">
                        <div class="num text-xl font-black text-emerald-400">100%</div>
                        <div class="text-[11px] font-medium text-slate-300 mt-0.5">Fiabilité stock</div>
                    </div>
                    <div class="rounded-xl bg-white/5 p-3.5 ring-1 ring-white/10 backdrop-blur-xs">
                        <div class="num text-xl font-black text-emerald-400">80 mm</div>
                        <div class="text-[11px] font-medium text-slate-300 mt-0.5">Tickets thermiques</div>
                    </div>
                    <div class="rounded-xl bg-white/5 p-3.5 ring-1 ring-white/10 backdrop-blur-xs">
                        <div class="num text-xl font-black text-emerald-400">Multi</div>
                        <div class="text-[11px] font-medium text-slate-300 mt-0.5">Rôles & Accès</div>
                    </div>
                </div>
            </div>

            {{-- Footer officiel --}}
            <div class="relative text-xs text-slate-400">
                &copy; {{ date('Y') }} GESTA PHARM • Tous droits réservés
            </div>

        </div>

        {{-- Formulaire droite --}}
        <div class="flex flex-1 items-center justify-center p-6 sm:p-12 lg:p-16">
            <div class="w-full max-w-[420px]">
                @yield('content')
            </div>
        </div>

    </div>

    @stack('scripts')
</body>

</html>
