@extends('layout')

@section('titre', 'Statistiques — GESTA PHARM')

@section('topbar')
    <div class="text-base font-bold">Statistiques</div>
    <nav class="ml-auto flex gap-1.5 text-[13px]">
        @foreach (['semaine' => 'Semaine', 'mois' => 'Mois', 'trimestre' => 'Trimestre'] as $cle => $libelle)
            <a href="{{ route('dashboard.statistiques', ['periode' => $cle]) }}"
                @class([
                    'rounded-lg px-3.5 py-1.5 transition-colors',
                    'bg-brand-500 font-semibold text-white' => $periode === $cle,
                    'border border-line text-muted hover:bg-surface' => $periode !== $cle,
                ])>{{ $libelle }}</a>
        @endforeach
    </nav>
@endsection

@section('content')

    @php
        $maxQuantite = $topMedicaments->max('total_quantite') ?: 1;
        $maxCa = max(array_column($serieCa, 'total')) ?: 1;
    @endphp

    <div class="mb-4 grid gap-4 xl:grid-cols-[1.4fr_1fr]">

        {{-- Top 5 --}}
        <div class="panel p-[18px]">

            <div class="text-sm font-bold">Top 5 des produits les plus vendus</div>
            <div class="mb-4 text-xs capitalize text-muted">
                {{ $libellePeriode }} · quantité &amp; montant généré
            </div>

            <div class="flex flex-col gap-[15px]">
                @forelse ($topMedicaments as $medicament)
                    <div>
                        <div class="mb-1.5 flex justify-between gap-3 text-[13px]">
                            <span class="truncate font-semibold">{{ $medicament->nom }}</span>
                            <span class="num shrink-0 font-semibold text-brand-600">
                                {{ number_format($medicament->total_quantite, 0, ',', ' ') }} ·
                                {{ number_format($medicament->total_montant, 0, ',', ' ') }} FCFA
                            </span>
                        </div>
                        <div class="h-[9px] overflow-hidden rounded-pill bg-hairline">
                            <div class="h-full bg-gradient-to-r from-leaf to-brand-600"
                                style="width: {{ round(($medicament->total_quantite / $maxQuantite) * 100) }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="py-10 text-center text-[13px] text-muted">
                        Aucune vente sur cette période.
                    </p>
                @endforelse
            </div>

        </div>

        {{-- Synthèse --}}
        <div class="flex flex-col rounded-card bg-brand-900 p-[18px] text-sidebar-text">

            <div class="text-sm font-bold text-white">Synthèse</div>
            <div class="mb-[18px] text-xs capitalize text-sidebar-muted">{{ $libellePeriode }}</div>

            <div class="mb-6 rounded-xl bg-black/15 p-5 text-center">
                <div class="num text-[32px] font-bold text-mint">
                    {{ number_format($caPeriode, 0, ',', ' ') }}
                </div>
                <div class="text-[11px] text-sidebar-muted">FCFA encaissés</div>
            </div>

            <dl class="flex flex-col gap-3 text-[13px]">
                <div class="flex items-center justify-between border-b border-white/10 pb-3">
                    <dt>Tickets émis</dt>
                    <dd class="num text-white">{{ number_format($nbVentes, 0, ',', ' ') }}</dd>
                </div>
                <div class="flex items-center justify-between border-b border-white/10 pb-3">
                    <dt>Panier moyen</dt>
                    <dd class="num text-white">
                        {{ $nbVentes > 0 ? number_format($caPeriode / $nbVentes, 0, ',', ' ') : 0 }} FCFA
                    </dd>
                </div>
                <div class="flex items-center justify-between">
                    <dt>Produits à surveiller</dt>
                    <dd class="num text-white">{{ $surveillance->count() }}</dd>
                </div>
            </dl>

            <div class="mt-auto pt-6">
                <div class="mb-2 text-[11px] uppercase tracking-widest text-sidebar-label">CA · 7 derniers jours</div>
                <div class="flex h-16 items-end gap-1.5">
                    @foreach ($serieCa as $point)
                        <div class="flex-1 rounded-t bg-mint/70"
                            style="height: {{ $point['total'] > 0 ? max(4, round(($point['total'] / $maxCa) * 60)) : 2 }}px"
                            title="{{ $point['jour'] }} · {{ number_format($point['total'], 0, ',', ' ') }} FCFA">
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

    </div>

    {{-- Surveillance du stock --}}
    <div class="panel overflow-hidden">

        <div class="border-b border-hairline px-[18px] py-3.5 text-sm font-bold">
            Surveillance du stock — action requise
        </div>

        <div class="overflow-x-auto">
            <table class="table-data">

                <thead>
                    <tr>
                        <th>MÉDICAMENT</th>
                        <th>STOCK</th>
                        <th>EXPIRATION</th>
                        <th class="text-right">STATUT</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($surveillance as $medicament)
                        @php $expireBientot = $medicament->expireBientot(); @endphp
                        <tr>
                            <td class="font-semibold">{{ $medicament->nom }}</td>
                            <td class="num">{{ $medicament->stock }}</td>
                            <td @class(['num', 'text-danger-fg' => $expireBientot, 'text-slate-ink' => !$expireBientot])>
                                {{ $medicament->date_expiration->format('m / Y') }}
                            </td>
                            <td class="text-right">
                                {{-- le produit est là pour son stock, sinon pour sa date --}}
                                @if ($medicament->statut_stock === \App\Enums\StatutStock::Ok)
                                    <span class="pill-warn">Expire &lt; {{ \App\Models\Medicament::FENETRE_EXPIRATION_JOURS }}j</span>
                                @else
                                    <x-pastille-stock :medicament="$medicament" format="libelle" />
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-10 text-center text-muted">
                                Aucun produit ne demande d'action.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

    </div>

@endsection
