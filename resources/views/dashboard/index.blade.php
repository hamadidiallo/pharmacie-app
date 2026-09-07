@extends('layout')

@section('titre', 'Tableau de bord — GESTA PHARM')

@section('topbar')
    <div class="flex items-center gap-3">
        <div>
            <h1 class="text-base font-bold text-slate-900 tracking-tight">Bonjour, {{ auth()->user()->firstname }} 👋</h1>
            <p class="text-xs text-slate-500 font-medium capitalize">{{ now()->translatedFormat('l j F Y') }}</p>
        </div>
    </div>
    <div class="ml-auto flex items-center gap-2.5">
        <a href="{{ route('ventes.create') }}" class="btn-primary">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                <path d="M12 5v14M5 12h14" />
            </svg>
            <span>Nouvelle vente</span>
        </a>
    </div>
@endsection

@section('content')

    @php
        $maxCa = max(array_column($serieCa, 'total')) ?: 1;
        $total7Jours = array_sum(array_column($serieCa, 'total'));
        $alertes = [
            [
                'filtre' => 'rupture',
                'titre' => 'Ruptures de stock',
                'produits' => $ruptures,
                'ton' => 'danger',
                'dot' => 'bg-red-500',
                'badge' => 'bg-red-50 text-red-700 ring-1 ring-red-600/20',
            ],
            [
                'filtre' => 'faible',
                'titre' => 'Stocks faibles (≤ ' . \App\Models\Medicament::SEUIL_ALERTE . ')',
                'produits' => $stockFaible,
                'ton' => 'warning',
                'dot' => 'bg-amber-500',
                'badge' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20',
            ],
            [
                'filtre' => 'expire',
                'titre' => 'Expirations proches (< ' . \App\Models\Medicament::FENETRE_EXPIRATION_JOURS . 'j)',
                'produits' => $expirations,
                'ton' => 'warning',
                'dot' => 'bg-orange-500',
                'badge' => 'bg-orange-50 text-orange-700 ring-1 ring-orange-600/20',
            ],
        ];
    @endphp

    {{-- 4 Stat Cards Modernes avec Accents Visuels --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

        {{-- Carte Ventes du Jour (Gradient Émeraude Lumineux) --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-600 via-emerald-700 to-teal-800 p-5 text-white shadow-lg shadow-emerald-700/20 transition-all duration-200 hover:-translate-y-0.5">
            <div class="absolute -right-4 -top-4 size-24 rounded-full bg-white/10 blur-xl"></div>
            
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-emerald-100">Ventes du jour</span>
                <div class="grid size-9 place-items-center rounded-xl bg-white/15 text-white backdrop-blur-xs">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                    </svg>
                </div>
            </div>

            <div class="num mt-3 text-3xl font-extrabold tracking-tight">
                {{ number_format($venteJour, 0, ',', ' ') }} <span class="text-lg font-bold text-emerald-200">FCFA</span>
            </div>

            <div class="mt-2 flex items-center gap-2">
                @if ($evolutionJour !== null)
                    <span @class([
                        'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold',
                        'bg-emerald-500/25 text-white' => $evolutionJour >= 0,
                        'bg-red-500/30 text-red-100' => $evolutionJour < 0,
                    ])>
                        {{ $evolutionJour >= 0 ? '▲ +' : '▼ ' }}{{ $evolutionJour }}%
                    </span>
                    <span class="text-xs text-emerald-200/80">vs hier</span>
                @else
                    <span class="text-xs text-emerald-200/80">Aujourd'hui</span>
                @endif
            </div>
        </div>

        {{-- Carte Cette Semaine --}}
        <div class="panel p-5 hover:border-slate-300">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Cette semaine</span>
                <div class="grid size-9 place-items-center rounded-xl bg-teal-50 text-teal-600">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <path d="M3 3v18h18" />
                        <path d="m7 15 4-4 3 3 5-6" />
                    </svg>
                </div>
            </div>

            <div class="num mt-3 text-3xl font-extrabold tracking-tight text-slate-900">
                {{ number_format($venteSemaine, 0, ',', ' ') }} <span class="text-lg font-bold text-slate-400">FCFA</span>
            </div>

            <div class="mt-2 text-xs font-medium text-slate-500">
                Recette cumulée sur 7 jours
            </div>
        </div>

        {{-- Carte Ce Mois --}}
        <div class="panel p-5 hover:border-slate-300">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Ce mois</span>
                <div class="grid size-9 place-items-center rounded-xl bg-indigo-50 text-indigo-600">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                        <line x1="16" y1="2" x2="16" y2="6" />
                        <line x1="8" y1="2" x2="8" y2="6" />
                        <line x1="3" y1="10" x2="21" y2="10" />
                    </svg>
                </div>
            </div>

            <div class="num mt-3 text-3xl font-extrabold tracking-tight text-slate-900">
                {{ number_format($venteMois, 0, ',', ' ') }} <span class="text-lg font-bold text-slate-400">FCFA</span>
            </div>

            <div class="mt-2 text-xs font-medium text-slate-500 capitalize">
                {{ now()->translatedFormat('F Y') }}
            </div>
        </div>

        {{-- Carte Catalogue Médicaments --}}
        <a href="{{ route('medicaments.index') }}" class="panel-interactive p-5 group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 group-hover:text-emerald-700">Catalogue</span>
                <div class="grid size-9 place-items-center rounded-xl bg-emerald-50 text-emerald-600 transition-colors group-hover:bg-emerald-600 group-hover:text-white">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <path d="M3.3 7 12 2l8.7 5v10L12 22l-8.7-5Z" />
                        <path d="M3.3 7 12 12l8.7-5M12 12v10" />
                    </svg>
                </div>
            </div>

            <div class="num mt-3 text-3xl font-extrabold tracking-tight text-slate-900">
                {{ number_format($totalMedicaments, 0, ',', ' ') }}
            </div>

            <div class="mt-2 flex items-center justify-between text-xs font-medium text-slate-500">
                <span>Références en stock</span>
                <span class="text-emerald-600 font-semibold group-hover:translate-x-0.5 transition-transform">Voir tout →</span>
            </div>
        </a>

    </div>

    {{-- Graphe Chiffre d'Affaires + Alertes Comptoir --}}
    <div class="grid gap-5 xl:grid-cols-[1.6fr_1fr]">

        {{-- Section Graphique CA 7 jours --}}
        <div class="panel p-6">
            <div class="flex flex-wrap items-center justify-between gap-3 pb-5 border-b border-slate-100">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Chiffre d'affaires (7 derniers jours)</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Total cumulé : <strong class="num text-slate-800">{{ number_format($total7Jours, 0, ',', ' ') }} FCFA</strong></p>
                </div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                    <span class="size-2 rounded-full bg-emerald-500"></span> Évolution quotidienne
                </span>
            </div>

            {{-- Barres Graphiques Élégantes --}}
            <div class="mt-6 flex h-48 items-end gap-3 sm:gap-4 px-2">
                @foreach ($serieCa as $point)
                    @php
                        $hauteur = max(6, (int) round(($point['total'] / $maxCa) * 100));
                        $estAujourdhui = $point['date'] === today()->toDateString();
                    @endphp
                    <div class="flex flex-1 flex-col items-center gap-2 group h-full justify-end">
                        
                        {{-- Montant affiché au survol --}}
                        <div class="num opacity-0 group-hover:opacity-100 transition-opacity duration-150 text-[11px] font-bold text-slate-700 whitespace-nowrap">
                            {{ number_format($point['total'], 0, ',', ' ') }}
                        </div>

                        {{-- Barre --}}
                        <div class="w-full max-w-[44px] rounded-t-xl transition-all duration-300 group-hover:scale-y-[1.02] origin-bottom {{ $estAujourdhui ? 'bg-gradient-to-t from-emerald-600 to-teal-500 shadow-md shadow-emerald-500/25' : 'bg-slate-200 hover:bg-slate-300' }}"
                            style="height: {{ $hauteur }}%"></div>

                        {{-- Jour --}}
                        <span @class([
                            'text-xs font-semibold capitalize',
                            'text-emerald-700 font-bold' => $estAujourdhui,
                            'text-slate-500' => !$estAujourdhui,
                        ])>
                            {{ $point['jour'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Section Alertes de Stock --}}
        <div class="panel p-6 flex flex-col">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h2 class="text-base font-bold text-slate-900">Alertes du comptoir</h2>
                <span class="rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700 ring-1 ring-amber-600/20">
                    Priorité stock
                </span>
            </div>

            <div class="mt-4 flex-1 space-y-4">
                @foreach ($alertes as $alerte)
                    <div class="rounded-2xl border border-slate-100 bg-slate-50/50 p-4 transition-all hover:bg-white hover:border-slate-200 hover:shadow-xs">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <span class="size-2.5 rounded-full {{ $alerte['dot'] }}"></span>
                                <h3 class="text-xs font-bold text-slate-800">{{ $alerte['titre'] }}</h3>
                            </div>
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-bold {{ $alerte['badge'] }}">
                                {{ $alerte['produits']->count() }}
                            </span>
                        </div>

                        @if ($alerte['produits']->isEmpty())
                            <p class="text-xs text-slate-400 italic">Aucun produit concerné.</p>
                        @else
                            <div class="flex flex-wrap gap-1.5 mt-2">
                                @foreach ($alerte['produits']->take(4) as $med)
                                    <span class="inline-flex items-center rounded-lg bg-white border border-slate-200 px-2 py-1 text-xs font-medium text-slate-700 shadow-2xs">
                                        {{ $med->nom }}
                                    </span>
                                @endforeach
                                @if ($alerte['produits']->count() > 4)
                                    <span class="inline-flex items-center rounded-lg bg-slate-200/70 px-2 py-1 text-xs font-bold text-slate-600">
                                        +{{ $alerte['produits']->count() - 4 }} autres
                                    </span>
                                @endif
                            </div>
                        @endif

                        <div class="mt-3 text-right">
                            <a href="{{ route('medicaments.index', ['filtre' => $alerte['filtre']]) }}"
                                class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 hover:underline">
                                Traiter les alertes →
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

@endsection
