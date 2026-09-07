@php
    $utilisateur = auth()->user();
    $initiales = mb_strtoupper(mb_substr($utilisateur->firstname, 0, 1) . mb_substr($utilisateur->lastname, 0, 1));
    $alertes = $alertesStock ?? ['faible' => 0, 'rupture' => 0, 'expire' => 0];

    $estSectionMedicaments = request()->is('medicaments*') || request()->is('stock*') || request()->is('rupture*') || request()->is('expire*');
    $estSectionVentes = request()->is('ventes*') || request()->is('caisse*');
    $estSectionAppro = request()->is('commandes*') || request()->is('fournisseurs*');
    $estSectionTiersPayant = request()->is('assurances*') || request()->is('bordereaux*');
    $estSectionOrdonnancier = request()->is('ordonnancier*');
    $estSectionAdmin = request()->is('utilisateurs*');
@endphp

{{-- Voile de fond pour mobile --}}
<div data-menu-backdrop class="fixed inset-0 z-40 hidden bg-slate-950/60 backdrop-blur-xs lg:hidden"></div>

<aside id="sidebar"
    class="fixed inset-y-0 left-0 z-50 flex w-[275px] shrink-0 -translate-x-full flex-col border-r border-emerald-950/40 bg-gradient-to-b from-[#081511] via-[#0b1b16] to-[#07130f] p-4 text-slate-200 shadow-2xl transition-transform duration-200 lg:static lg:translate-x-0">

    {{-- En-tête Logo Officine --}}
    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-2 pb-5 pt-1 group">
        <div class="relative flex size-10.5 items-center justify-center rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-400 shadow-lg shadow-emerald-500/25 transition-transform duration-200 group-hover:scale-105">
            <svg class="size-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round">
                <path d="M12 5v14M5 12h14" />
            </svg>
        </div>
        <div class="leading-tight">
            <span class="block font-bold tracking-tight text-white text-[16px]">GESTA PHARM</span>
            <span class="block text-[11px] font-medium text-emerald-400/90">Officine Médicale · Kati</span>
        </div>
    </a>

    {{-- Navigation Principale Scrollable --}}
    <div class="flex-1 overflow-y-auto pr-1 space-y-4 text-xs">

        {{-- 1. PILOTAGE & TABLEAU DE BORD --}}
        <div>
            <p class="px-3 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-emerald-500/60">Pilotage</p>
            <nav class="space-y-1">
                @php $estDashboard = request()->routeIs('dashboard'); @endphp
                <a href="{{ route('dashboard') }}" @class([
                    'nav-link-active' => $estDashboard,
                    'nav-link' => !$estDashboard,
                ])>
                    <svg class="size-4.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/>
                        <rect x="3" y="14" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/>
                    </svg>
                    <span>Tableau de bord</span>
                </a>

                @can('acceder-statistiques')
                    @php $estStats = request()->routeIs('dashboard.statistiques'); @endphp
                    <a href="{{ route('dashboard.statistiques') }}" @class([
                        'nav-link-active' => $estStats,
                        'nav-link' => !$estStats,
                    ])>
                        <svg class="size-4.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/>
                        </svg>
                        <span>Statistiques & CA</span>
                    </a>
                @endcan
            </nav>
        </div>

        {{-- 2. OFFICINE & PHARMACIE (DROPDOWN / ACCORDÉON) --}}
        <div>
            <p class="px-3 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-emerald-500/60">Gestion Médicale</p>

            <details class="group/nav" @if($estSectionMedicaments) open @endif>
                <summary class="nav-dropdown-trigger">
                    <svg class="size-4.5 shrink-0 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="m10.5 20.5 10-10a4.95 4.95 0 1 0-7-7l-10 10a4.95 4.95 0 1 0 7 7Z"/><path d="m8.5 8.5 7 7"/>
                    </svg>
                    <span class="flex-1 font-medium">Médicaments & Lots</span>

                    @if (($alertes['rupture'] ?? 0) > 0)
                        <span class="size-2 rounded-full bg-rose-500 mr-1 animate-ping" title="Rupture de stock"></span>
                    @endif

                    <svg class="size-3.5 text-slate-400 transition-transform duration-200 group-open/nav:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="m6 9 6 6 6-6"/>
                    </svg>
                </summary>

                <div class="ml-4.5 mt-1 space-y-0.5 border-l border-emerald-800/40 pl-3">
                    {{-- Tous les produits & Lots FEFO --}}
                    @php $estCatalogue = request()->routeIs('medicaments.index') && !request('filtre'); @endphp
                    <a href="{{ route('medicaments.index') }}" @class([
                        'nav-sub-link-active' => $estCatalogue,
                        'nav-sub-link' => !$estCatalogue,
                    ])>
                        <span>Catalogue & Lots FEFO</span>
                    </a>

                    {{-- Nouveau médicament --}}
                    @can('gerer-medicaments')
                        @php $estCreate = request()->routeIs('medicaments.create'); @endphp
                        <a href="{{ route('medicaments.create') }}" @class([
                            'nav-sub-link-active' => $estCreate,
                            'nav-sub-link' => !$estCreate,
                        ])>
                            <span class="flex items-center gap-1.5">
                                <span class="text-emerald-400 font-bold">+</span> Nouveau / Arrivage
                            </span>
                        </a>
                    @endcan

                    {{-- Filtres d'alerte --}}
                    @php $estFaible = request('filtre') === 'faible'; @endphp
                    <a href="{{ route('medicaments.index', ['filtre' => 'faible']) }}" @class([
                        'nav-sub-link-active' => $estFaible,
                        'nav-sub-link' => !$estFaible,
                    ])>
                        <span>Stock faible</span>
                        @if (($alertes['faible'] ?? 0) > 0)
                            <span class="rounded-full bg-amber-500/20 px-1.5 py-0.2 text-[10px] font-bold text-amber-300">
                                {{ $alertes['faible'] }}
                            </span>
                        @endif
                    </a>

                    @php $estRupture = request('filtre') === 'rupture'; @endphp
                    <a href="{{ route('medicaments.index', ['filtre' => 'rupture']) }}" @class([
                        'nav-sub-link-active' => $estRupture,
                        'nav-sub-link' => !$estRupture,
                    ])>
                        <span>Ruptures</span>
                        @if (($alertes['rupture'] ?? 0) > 0)
                            <span class="rounded-full bg-rose-500/25 px-1.5 py-0.2 text-[10px] font-bold text-rose-300">
                                {{ $alertes['rupture'] }}
                            </span>
                        @endif
                    </a>

                    @php $estExpire = request('filtre') === 'expire'; @endphp
                    <a href="{{ route('medicaments.index', ['filtre' => 'expire']) }}" @class([
                        'nav-sub-link-active' => $estExpire,
                        'nav-sub-link' => !$estExpire,
                    ])>
                        <span>Péremptions proches</span>
                        @if (($alertes['expire'] ?? 0) > 0)
                            <span class="rounded-full bg-amber-500/20 px-1.5 py-0.2 text-[10px] font-bold text-amber-300">
                                {{ $alertes['expire'] }}
                            </span>
                        @endif
                    </a>
                </div>
            </details>
        </div>

        {{-- 3. CAISSE & ENCAISSEMENT (DROPDOWN / ACCORDÉON) --}}
        <div>
            <p class="px-3 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-emerald-500/60">Comptoir</p>

            <details class="group/nav" @if($estSectionVentes) open @endif>
                <summary class="nav-dropdown-trigger">
                    <svg class="size-4.5 shrink-0 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/>
                        <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
                    </svg>
                    <span class="flex-1 font-medium">Caisse & Ventes</span>
                    <svg class="size-3.5 text-slate-400 transition-transform duration-200 group-open/nav:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="m6 9 6 6 6-6"/>
                    </svg>
                </summary>

                <div class="ml-4.5 mt-1 space-y-0.5 border-l border-emerald-800/40 pl-3">
                    @php $estVenteCreate = request()->routeIs('ventes.create') || request()->routeIs('ventes.paiement'); @endphp
                    <a href="{{ route('ventes.create') }}" @class([
                        'nav-sub-link-active' => $estVenteCreate,
                        'nav-sub-link' => !$estVenteCreate,
                    ])>
                        <span class="flex items-center gap-1.5">
                            <span class="size-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            Comptoir / Douchette
                        </span>
                    </a>

                    @can('gerer-caisse')
                        @php $estCaisseSessions = request()->routeIs('caisse.sessions.*'); @endphp
                        <a href="{{ route('caisse.sessions.index') }}" @class([
                            'nav-sub-link-active' => $estCaisseSessions,
                            'nav-sub-link' => !$estCaisseSessions,
                        ])>
                            <span>Sessions & Clôtures (Z)</span>
                        </a>
                    @endcan

                    @php $estVenteIndex = request()->routeIs('ventes.index') || request()->routeIs('ventes.show'); @endphp
                    <a href="{{ route('ventes.index') }}" @class([
                        'nav-sub-link-active' => $estVenteIndex,
                        'nav-sub-link' => !$estVenteIndex,
                    ])>
                        <span>Historique des ventes</span>
                    </a>
                </div>
            </details>
        </div>

        {{-- 4. APPROVISIONNEMENTS & FOURNISSEURS (DROPDOWN) --}}
        @can('gerer-medicaments')
            <div>
                <p class="px-3 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-emerald-500/60">Approvisionnements</p>

                <details class="group/nav" @if($estSectionAppro) open @endif>
                    <summary class="nav-dropdown-trigger">
                        <svg class="size-4.5 shrink-0 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>
                        </svg>
                        <span class="flex-1 font-medium">Achats & Grossistes</span>
                        <svg class="size-3.5 text-slate-400 transition-transform duration-200 group-open/nav:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="m6 9 6 6 6-6"/>
                        </svg>
                    </summary>

                    <div class="ml-4.5 mt-1 space-y-0.5 border-l border-emerald-800/40 pl-3">
                        @php $estCmds = request()->routeIs('commandes.index') || request()->routeIs('commandes.show'); @endphp
                        <a href="{{ route('commandes.index') }}" @class([
                            'nav-sub-link-active' => $estCmds,
                            'nav-sub-link' => !$estCmds,
                        ])>
                            <span>Bons de commande</span>
                        </a>

                        @php $estSuggerer = request()->routeIs('commandes.suggerer'); @endphp
                        <a href="{{ route('commandes.suggerer') }}" @class([
                            'nav-sub-link-active' => $estSuggerer,
                            'nav-sub-link' => !$estSuggerer,
                        ])>
                            <span class="flex items-center gap-1.5 text-emerald-400 font-semibold">
                                <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3Z"/>
                                </svg>
                                Commande suggérée
                            </span>
                        </a>

                        @php $estFourn = request()->routeIs('fournisseurs.*'); @endphp
                        <a href="{{ route('fournisseurs.index') }}" @class([
                            'nav-sub-link-active' => $estFourn,
                            'nav-sub-link' => !$estFourn,
                        ])>
                            <span>Répertoire Fournisseurs</span>
                        </a>
                    </div>
                </details>
            </div>
        @endcan

        {{-- 5. TIERS PAYANT & ASSURANCES (DROPDOWN) --}}
        @can('gerer-medicaments')
            <div>
                <p class="px-3 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-emerald-500/60">Tiers Payant</p>

                <details class="group/nav" @if($estSectionTiersPayant) open @endif>
                    <summary class="nav-dropdown-trigger">
                        <svg class="size-4.5 shrink-0 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect width="18" height="18" x="3" y="3" rx="2"/><path d="M7 12h10"/><path d="M12 7v10"/>
                        </svg>
                        <span class="flex-1 font-medium">Assurances & Mutuelles</span>
                        <svg class="size-3.5 text-slate-400 transition-transform duration-200 group-open/nav:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="m6 9 6 6 6-6"/>
                        </svg>
                    </summary>

                    <div class="ml-4.5 mt-1 space-y-0.5 border-l border-emerald-800/40 pl-3">
                        @php $estBordereaux = request()->routeIs('bordereaux.index') || request()->routeIs('bordereaux.show'); @endphp
                        <a href="{{ route('bordereaux.index') }}" @class([
                            'nav-sub-link-active' => $estBordereaux,
                            'nav-sub-link' => !$estBordereaux,
                        ])>
                            <span>Bordereaux de facturation</span>
                        </a>

                        @php $estNouveauBordereau = request()->routeIs('bordereaux.create'); @endphp
                        <a href="{{ route('bordereaux.create') }}" @class([
                            'nav-sub-link-active' => $estNouveauBordereau,
                            'nav-sub-link' => !$estNouveauBordereau,
                        ])>
                            <span class="flex items-center gap-1.5 text-emerald-400 font-semibold">
                                <span class="text-emerald-400 font-bold">+</span> Émettre bordereau
                            </span>
                        </a>

                        @php $estAssurances = request()->routeIs('assurances.*'); @endphp
                        <a href="{{ route('assurances.index') }}" @class([
                            'nav-sub-link-active' => $estAssurances,
                            'nav-sub-link' => !$estAssurances,
                        ])>
                            <span>Organismes payeurs</span>
                        </a>
                    </div>
                </details>
            </div>
        @endcan

        {{-- 6. ORDONNANCIER RÉGLEMENTAIRE & STUPÉFIANTS --}}
        <div>
            <p class="px-3 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-emerald-500/60">Conformité Légale</p>

            <details class="group/nav" @if($estSectionOrdonnancier) open @endif>
                <summary class="nav-dropdown-trigger">
                    <svg class="size-4.5 shrink-0 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/><path d="M8 7h6M8 11h8"/>
                    </svg>
                    <span class="flex-1 font-medium">Ordonnancier & Registre</span>
                    <svg class="size-3.5 text-slate-400 transition-transform duration-200 group-open/nav:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="m6 9 6 6 6-6"/>
                    </svg>
                </summary>

                <div class="ml-4.5 mt-1 space-y-0.5 border-l border-emerald-800/40 pl-3">
                    @php $estOrdIndex = request()->routeIs('ordonnancier.*') && !request('tableau'); @endphp
                    <a href="{{ route('ordonnancier.index') }}" @class([
                        'nav-sub-link-active' => $estOrdIndex,
                        'nav-sub-link' => !$estOrdIndex,
                    ])>
                        <span>Registre des ordonnances</span>
                    </a>

                    @php $estStup = request('tableau') === 'Stupéfiant'; @endphp
                    <a href="{{ route('ordonnancier.index', ['tableau' => 'Stupéfiant']) }}" @class([
                        'nav-sub-link-active' => $estStup,
                        'nav-sub-link' => !$estStup,
                    ])>
                        <span class="text-rose-300 font-semibold">Stupéfiants (Tab. B)</span>
                    </a>

                    @php $estL1 = request('tableau') === 'Liste I'; @endphp
                    <a href="{{ route('ordonnancier.index', ['tableau' => 'Liste I']) }}" @class([
                        'nav-sub-link-active' => $estL1,
                        'nav-sub-link' => !$estL1,
                    ])>
                        <span class="text-amber-300">Liste I (Tab. A)</span>
                    </a>
                </div>
            </details>
        </div>

        {{-- 7. ADMINISTRATION & SÉCURITÉ (DROPDOWN SI ADMIN) --}}
        @can('admin')
            <div>
                <p class="px-3 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-emerald-500/60">Système</p>

                <details class="group/nav" @if($estSectionAdmin) open @endif>
                    <summary class="nav-dropdown-trigger">
                        <svg class="size-4.5 shrink-0 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/>
                        </svg>
                        <span class="flex-1 font-medium">Administration</span>
                        <svg class="size-3.5 text-slate-400 transition-transform duration-200 group-open/nav:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="m6 9 6 6 6-6"/>
                        </svg>
                    </summary>

                    <div class="ml-4.5 mt-1 space-y-0.5 border-l border-emerald-800/40 pl-3">
                        @php $estUsers = request()->routeIs('users.*'); @endphp
                        <a href="{{ route('users.index') }}" @class([
                            'nav-sub-link-active' => $estUsers,
                            'nav-sub-link' => !$estUsers,
                        ])>
                            <span>Utilisateurs & Permissions</span>
                        </a>
                    </div>
                </details>
            </div>
        @endcan

    </div>

    {{-- Profil Utilisateur & Déconnexion Bas de Page --}}
    <div class="mt-auto pt-3 border-t border-emerald-950/40">
        <div class="flex items-center gap-2.5 rounded-2xl border border-white/5 bg-white/5 p-2.5 shadow-inner">

            <div class="relative">
                <span class="inline-flex size-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-xs font-bold text-white shadow-sm">
                    {{ $initiales }}
                </span>
                <span class="absolute -bottom-0.5 -right-0.5 size-2.5 rounded-full border-2 border-[#0b1b16] bg-emerald-400"></span>
            </div>

            <div class="min-w-0 flex-1 leading-tight">
                <span class="block truncate text-xs font-semibold text-white">
                    {{ $utilisateur->firstname }} {{ $utilisateur->lastname }}
                </span>
                <span class="mt-0.5 inline-block rounded-full bg-emerald-500/15 px-2 py-0.5 text-[9.5px] font-semibold text-emerald-300">
                    {{ $utilisateur->role?->libelle() ?? 'Utilisateur' }}
                </span>
            </div>

            <form action="{{ route('auth.logout') }}" method="post">
                @csrf
                @method('delete')
                <button class="grid size-8 place-items-center rounded-xl text-slate-400 transition-colors hover:bg-red-500/20 hover:text-red-300"
                    aria-label="Se déconnecter" title="Se déconnecter">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                        <path d="m16 17 5-5-5-5M21 12H9" />
                    </svg>
                </button>
            </form>

        </div>
    </div>

</aside>
