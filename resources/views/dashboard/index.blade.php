@extends('layout')

@section('titre', 'Tableau de bord — GESTA PHARM')

@section('topbar')
    <div>
        <div class="text-[15px] font-bold">Bonjour, {{ auth()->user()->firstname }}</div>
        <div class="text-xs text-muted">{{ now()->translatedFormat('l j F Y') }}</div>
    </div>
    <a href="{{ route('ventes.create') }}" class="btn-primary ml-auto">
        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
            stroke-linecap="round">
            <path d="M12 5v14M5 12h14" />
        </svg>
        <span class="hidden sm:inline">Nouvelle vente</span>
    </a>
@endsection

@section('content')

    @php
        $maxCa = max(array_column($serieCa, 'total')) ?: 1;
        $alertes = [
            [
                'route' => 'dashboard.ruptureStock',
                'titre' => 'Rupture de stock',
                'produits' => $ruptures,
                'ton' => 'danger',
                'icone' => '<circle cx="12" cy="12" r="9"/><path d="m5.6 5.6 12.8 12.8"/>',
            ],
            [
                'route' => 'dashboard.stockFaible',
                'titre' => 'Stock faible (≤ 5)',
                'produits' => $stockFaible,
                'ton' => 'warning',
                'icone' =>
                    '<path d="M10.3 3.2 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.2a2 2 0 0 0-3.4 0Z"/><path d="M12 9v4M12 17h.01"/>',
            ],
            [
                'route' => 'dashboard.expire',
                'titre' => 'Expiration < 30 j',
                'produits' => $expirations,
                'ton' => 'warning',
                'icone' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
            ],
        ];
    @endphp

    {{-- KPI --}}
    <div class="mb-4 grid gap-3.5 sm:grid-cols-2 xl:grid-cols-4">

        <div class="rounded-xl bg-brand-500 p-4 text-white">
            <div class="flex items-start justify-between">
                <span class="text-xs text-[#C7EEE1]">VENTES DU JOUR</span>
                <svg class="size-[18px]" viewBox="0 0 24 24" fill="none" stroke="#8FD9C4" stroke-width="1.9"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                </svg>
            </div>
            <div class="num mt-2.5 text-[27px] font-semibold">{{ number_format($venteJour, 0, ',', ' ') }}</div>
            <div class="mt-0.5 text-xs text-[#C7EEE1]">
                FCFA
                @if ($evolutionJour !== null)
                    · {{ $evolutionJour >= 0 ? '▲' : '▼' }} {{ abs($evolutionJour) }}% vs hier
                @endif
            </div>
        </div>

        @foreach ([['CETTE SEMAINE', $venteSemaine, '7 jours'], ['CE MOIS', $venteMois, now()->translatedFormat('F')]] as [$libelle, $montant, $note])
            <div class="panel p-4">
                <div class="flex items-start justify-between">
                    <span class="text-xs text-muted">{{ $libelle }}</span>
                    <svg class="size-[18px]" viewBox="0 0 24 24" fill="none" stroke="#0F8A6B" stroke-width="1.9"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 3v18h18" />
                        <path d="m7 15 4-4 3 3 5-6" />
                    </svg>
                </div>
                <div class="num mt-2.5 text-[27px] font-semibold">{{ number_format($montant, 0, ',', ' ') }}</div>
                <div class="mt-0.5 text-xs text-muted">FCFA · {{ $note }}</div>
            </div>
        @endforeach

        <a href="{{ route('medicaments.index') }}" class="panel p-4 transition-colors hover:bg-surface">
            <div class="flex items-start justify-between">
                <span class="text-xs text-muted">MÉDICAMENTS</span>
                <svg class="size-[18px]" viewBox="0 0 24 24" fill="none" stroke="#0F8A6B" stroke-width="1.9"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3.3 7 12 2l8.7 5v10L12 22l-8.7-5Z" />
                    <path d="M3.3 7 12 12l8.7-5M12 12v10" />
                </svg>
            </div>
            <div class="num mt-2.5 text-[27px] font-semibold">{{ number_format($totalMedicaments, 0, ',', ' ') }}</div>
            <div class="mt-0.5 text-xs text-muted">références en stock</div>
        </a>

    </div>

    {{-- Graphe + alertes --}}
    <div class="grid gap-3.5 xl:grid-cols-[1.55fr_1fr]">

        <div class="panel p-[18px]">

            <div class="mb-5 flex items-center justify-between">
                <div>
                    <div class="text-sm font-bold">Chiffre d'affaires</div>
                    <div class="text-xs text-muted">7 derniers jours · FCFA</div>
                </div>
                <span class="rounded-md bg-brand-50 px-3 py-1.5 text-xs font-semibold text-brand-600">Semaine</span>
            </div>

            <div class="flex h-48 items-end gap-2 pt-2 sm:gap-4">
                @foreach ($serieCa as $point)
                    @php
                        $hauteur = $point['total'] > 0 ? max(6, round(($point['total'] / $maxCa) * 170)) : 2;
                        $aujourdhui = $loop->last;
                    @endphp
                    <div class="flex flex-1 flex-col items-center gap-2">
                        <div class="w-full max-w-[44px] rounded-t-md {{ $aujourdhui ? 'bg-brand-600' : 'bg-gradient-to-b from-mint to-brand-500' }}"
                            style="height: {{ $hauteur }}px"
                            title="{{ number_format($point['total'], 0, ',', ' ') }} FCFA">
                        </div>
                        <span @class([
                            'text-[11px] capitalize',
                            'font-semibold text-ink' => $aujourdhui,
                            'text-faint' => !$aujourdhui,
                        ])>{{ $point['jour'] }}</span>
                    </div>
                @endforeach
            </div>

        </div>

        <div class="panel p-[18px]">

            <div class="mb-3.5 text-sm font-bold">Alertes de stock</div>

            <div class="flex flex-col gap-2.5">
                @foreach ($alertes as $alerte)
                    @php
                        $nombre = $alerte['produits']->count();
                        $noms = $alerte['produits']->take(2)->pluck('nom')->implode(' · ');
                        $reste = $nombre - min($nombre, 2);
                    @endphp
                    <a href="{{ route($alerte['route']) }}"
                        @class([
                            'flex gap-3 rounded-lg border p-3 transition-colors',
                            'border-danger-border bg-danger-bg hover:bg-danger-border/60' =>
                                $alerte['ton'] === 'danger' && $nombre > 0,
                            'border-warning-border bg-warning-bg hover:bg-warning-border/60' =>
                                $alerte['ton'] === 'warning' && $nombre > 0,
                            'border-line bg-surface hover:bg-canvas' => $nombre === 0,
                        ])>

                        <svg @class([
                            'mt-0.5 size-[18px] shrink-0',
                            'text-danger-fg' => $alerte['ton'] === 'danger' && $nombre > 0,
                            'text-warning-fg' => $alerte['ton'] === 'warning' && $nombre > 0,
                            'text-faint' => $nombre === 0,
                        ]) viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            {!! $alerte['icone'] !!}
                        </svg>

                        <div class="min-w-0 flex-1">
                            <div @class([
                                'text-[13px] font-semibold',
                                'text-danger-text' => $alerte['ton'] === 'danger' && $nombre > 0,
                                'text-warning-text' => $alerte['ton'] === 'warning' && $nombre > 0,
                                'text-ink' => $nombre === 0,
                            ])>{{ $alerte['titre'] }}</div>
                            <div class="truncate text-xs text-muted">
                                {{ $nombre > 0 ? $noms . ($reste > 0 ? ' · +' . $reste : '') : 'Rien à signaler' }}
                            </div>
                        </div>

                        <span @class([
                            'num text-[13px] font-bold',
                            'text-danger-fg' => $alerte['ton'] === 'danger' && $nombre > 0,
                            'text-warning-fg' => $alerte['ton'] === 'warning' && $nombre > 0,
                            'text-faint' => $nombre === 0,
                        ])>{{ $nombre }}</span>

                    </a>
                @endforeach
            </div>

        </div>

    </div>

@endsection
