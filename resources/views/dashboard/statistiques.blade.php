@extends('layout')

@section('titre', 'Statistiques — GESTA PHARM')

@section('topbar')
    <div class="flex items-center gap-3">
        <div class="flex size-9 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 ring-1 ring-emerald-500/20">
            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 3v18h18" />
                <path d="m19 9-5 5-4-4-3 3" />
            </svg>
        </div>
        <div>
            <h1 class="text-base font-bold text-slate-900 tracking-tight">Analyses & Statistiques</h1>
            <p class="text-xs text-slate-500">Performances des ventes et suivi du stock</p>
        </div>
    </div>

    <nav class="ml-auto flex items-center rounded-xl bg-slate-100 p-1 text-xs">
        @foreach (['semaine' => 'Semaine', 'mois' => 'Mois', 'trimestre' => 'Trimestre'] as $cle => $libelle)
            <a href="{{ route('dashboard.statistiques', ['periode' => $cle]) }}"
                @class([
                    'rounded-lg px-3.5 py-1.5 font-semibold transition-all',
                    'bg-white text-emerald-700 shadow-xs ring-1 ring-slate-200/80' => $periode === $cle,
                    'text-slate-600 hover:text-slate-900' => $periode !== $cle,
                ])>{{ $libelle }}</a>
        @endforeach
    </nav>
@endsection

@section('content')

    @php
        $maxQuantite = $topMedicaments->max('total_quantite') ?: 1;
        $maxCa = max(array_column($serieCa, 'total')) ?: 1;
    @endphp

    <div class="mb-6 grid gap-5 xl:grid-cols-[1.35fr_1fr]">

        {{-- Top 5 Produits --}}
        <div class="panel p-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Top 5 des produits les plus vendus</h2>
                    <p class="text-xs capitalize text-slate-500 mt-0.5">
                        Période : {{ $libellePeriode }} · Volume de ventes & chiffre d'affaires
                    </p>
                </div>
                <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-bold text-emerald-700 ring-1 ring-emerald-600/20">
                    Palmarès
                </span>
            </div>

            <div class="space-y-4">
                @forelse ($topMedicaments as $index => $medicament)
                    @php
                        $pourcentage = round(($medicament->total_quantite / $maxQuantite) * 100);
                        $badgeColors = [
                            0 => 'bg-amber-100 text-amber-800 ring-amber-500/30',
                            1 => 'bg-slate-200 text-slate-800 ring-slate-400/30',
                            2 => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                        ];
                    @endphp
                    <div class="rounded-xl border border-slate-100 bg-slate-50/40 p-3.5 transition-all hover:bg-slate-50 hover:border-slate-200">
                        <div class="mb-2 flex items-center justify-between gap-3 text-xs">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="grid size-5 shrink-0 place-items-center rounded-full text-[10px] font-black ring-1 {{ $badgeColors[$index] ?? 'bg-slate-100 text-slate-600 ring-slate-200' }}">
                                    {{ $index + 1 }}
                                </span>
                                <span class="truncate font-bold text-slate-900 text-sm">{{ $medicament->nom }}</span>
                            </div>
                            <div class="num shrink-0 text-right">
                                <span class="font-bold text-slate-800">{{ number_format($medicament->total_quantite, 0, ',', ' ') }} vendus</span>
                                <span class="text-slate-400 mx-1">•</span>
                                <span class="font-bold text-emerald-700">{{ number_format($medicament->total_montant, 0, ',', ' ') }} FCFA</span>
                            </div>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-slate-200/80">
                            <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-400 transition-all duration-500"
                                style="width: {{ $pourcentage }}%"></div>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-slate-400">
                        <svg class="size-8 mx-auto mb-2 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="m15 9-6 6M9 9l6 6"/>
                        </svg>
                        <p class="text-xs font-semibold text-slate-600">Aucune vente enregistrée sur cette période</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Synthèse CA & Métriques --}}
        <div class="flex flex-col rounded-2xl bg-gradient-to-br from-slate-950 via-[#0d1e19] to-emerald-950 p-6 text-white shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-sm font-bold text-white tracking-wide">Synthèse financière</h2>
                    <p class="text-xs capitalize text-emerald-300/80 mt-0.5">{{ $libellePeriode }}</p>
                </div>
                <span class="rounded-full bg-emerald-500/20 px-2.5 py-0.5 text-xs font-semibold text-emerald-300 ring-1 ring-emerald-500/30">
                    Live KPI
                </span>
            </div>

            <div class="mb-5 rounded-2xl bg-white/5 p-5 text-center ring-1 ring-white/10 backdrop-blur-xs">
                <div class="text-[11px] font-bold uppercase tracking-widest text-emerald-300/70 mb-1">Total Encaissé</div>
                <div class="num text-3xl font-black text-emerald-400 tracking-tight">
                    {{ number_format($caPeriode, 0, ',', ' ') }}
                </div>
                <div class="text-xs font-medium text-slate-300 mt-1">FCFA nets perçus</div>
            </div>

            <dl class="space-y-3 text-xs mb-6">
                <div class="flex items-center justify-between border-b border-white/10 pb-2.5">
                    <dt class="text-slate-300">Volume de tickets émis</dt>
                    <dd class="num text-sm font-bold text-white">{{ number_format($nbVentes, 0, ',', ' ') }}</dd>
                </div>
                <div class="flex items-center justify-between border-b border-white/10 pb-2.5">
                    <dt class="text-slate-300">Panier moyen</dt>
                    <dd class="num text-sm font-bold text-emerald-300">
                        {{ $nbVentes > 0 ? number_format($caPeriode / $nbVentes, 0, ',', ' ') : 0 }} FCFA
                    </dd>
                </div>
                <div class="flex items-center justify-between">
                    <dt class="text-slate-300">Produits nécessitant une action</dt>
                    <dd class="num text-sm font-bold text-amber-400">{{ $surveillance->count() }} alertes</dd>
                </div>
            </dl>

            <div class="mt-auto pt-4 border-t border-white/10">
                <div class="mb-3 flex items-center justify-between text-[11px]">
                    <span class="font-bold uppercase tracking-wider text-slate-400">Tendance · 7 derniers jours</span>
                    <span class="text-emerald-400 font-semibold">Max: {{ number_format($maxCa, 0, ',', ' ') }} F</span>
                </div>
                <div class="flex h-16 items-end gap-2 px-1">
                    @foreach ($serieCa as $point)
                        @php
                            $hauteur = $point['total'] > 0 ? max(6, round(($point['total'] / $maxCa) * 56)) : 3;
                        @endphp
                        <div class="group relative flex-1 flex flex-col items-center">
                            <div class="w-full rounded-t-md bg-emerald-500/70 hover:bg-emerald-400 transition-all cursor-pointer shadow-xs"
                                style="height: {{ $hauteur }}px"
                                title="{{ $point['jour'] }} · {{ number_format($point['total'], 0, ',', ' ') }} FCFA">
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

    {{-- Surveillance du stock --}}
    <div class="panel overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/50 px-5 py-3.5">
            <div class="flex items-center gap-2">
                <span class="size-2 rounded-full bg-amber-500"></span>
                <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-600">
                    Surveillance du stock — Actions requises
                </h2>
            </div>
            <span class="rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700 ring-1 ring-amber-600/20">
                {{ $surveillance->count() }} produit(s) concerné(s)
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="table-data">
                <thead>
                    <tr>
                        <th>MÉDICAMENT</th>
                        <th>UNITÉS EN STOCK</th>
                        <th>DATE D'EXPIRATION</th>
                        <th class="text-right">STATUT D'ALERTE</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($surveillance as $medicament)
                        @php $expireBientot = $medicament->expireBientot(); @endphp
                        <tr>
                            <td class="font-bold text-slate-900">{{ $medicament->nom }}</td>
                            <td class="num font-semibold text-slate-700">{{ $medicament->stock }}</td>
                            <td @class(['num font-medium', 'text-rose-600 font-bold' => $expireBientot, 'text-slate-600' => !$expireBientot])>
                                {{ $medicament->date_expiration->format('m / Y') }}
                            </td>
                            <td class="text-right">
                                @if ($medicament->statut_stock === \App\Enums\StatutStock::Ok)
                                    <span class="pill-warn">Expire &lt; {{ \App\Models\Medicament::FENETRE_EXPIRATION_JOURS }}j</span>
                                @else
                                    <x-pastille-stock :medicament="$medicament" format="libelle" />
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-12 text-center text-slate-500">
                                <div class="mx-auto mb-2 grid size-9 place-items-center rounded-full bg-emerald-50 text-emerald-600">
                                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="20 6 9 17 4 12"/>
                                    </svg>
                                </div>
                                <span class="text-xs font-medium text-slate-600">Parfait ! Aucun produit ne demande d'action immédiate.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection
