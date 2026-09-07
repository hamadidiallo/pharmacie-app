@extends('layout')

@section('titre', "Détail de Caisse #{$session->id} — GESTA PHARM")

@section('topbar')
    <a href="{{ route('caisse.sessions.index') }}" class="btn-icon" aria-label="Retour aux sessions">
        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M15 18l-6-6 6-6" />
        </svg>
    </a>
    <div class="text-base font-bold">Session de Caisse #{{ $session->id }}</div>
@endsection

@section('content')

    <div class="space-y-6">

        {{-- En-tête de la Session --}}
        <div class="panel p-6 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="flex size-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20">
                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="4" width="20" height="16" rx="2" />
                        <path d="M6 8h.01M10 8h.01M14 8h.01M18 8h.01M8 12h8M6 16h12" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-lg font-bold text-slate-900">Session de Caisse #{{ $session->id }}</h1>
                        <span class="inline-flex items-center text-xs font-bold px-2.5 py-0.5 rounded-full {{ $session->statut->badgeClasses() }}">
                            {{ $session->statut->libelle() }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Caissier : <strong>{{ $session->user->firstname }} {{ $session->user->lastname }}</strong>
                        · Du <strong>{{ $session->date_ouverture->format('d/m/Y H:i') }}</strong>
                        @if ($session->date_fermeture)
                            au <strong>{{ $session->date_fermeture->format('d/m/Y H:i') }}</strong>
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                @if ($session->estOuverte())
                    <a href="{{ route('caisse.sessions.cloture', $session) }}" class="btn-primary">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect width="18" height="18" x="3" y="3" rx="2" />
                            <path d="m9 12 2 2 4-4" />
                        </svg>
                        Procéder à la Clôture (Z)
                    </a>
                @else
                    <a href="{{ route('caisse.sessions.rapport-z', $session) }}" class="btn-primary">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 6 2 18 2 18 9"/>
                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                            <rect width="12" height="8" x="6" y="14"/>
                        </svg>
                        Imprimer le Rapport Z
                    </a>
                @endif
            </div>
        </div>

        {{-- Cartes de synthèse financière --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="panel p-4">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 block">Fond d'Ouverture</span>
                <span class="num text-xl font-bold text-slate-900 mt-1 block">
                    {{ number_format($session->fond_caisse_ouverture, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">FCFA</span>
                </span>
            </div>

            <div class="panel p-4">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-emerald-600 block">Ventes Espèces</span>
                <span class="num text-xl font-bold text-emerald-700 mt-1 block">
                    +{{ number_format($session->total_especes_theorique, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">FCFA</span>
                </span>
            </div>

            <div class="panel p-4">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-sky-600 block">Ventes Mobile Money / Carte</span>
                <span class="num text-xl font-bold text-sky-700 mt-1 block">
                    {{ number_format($session->total_mobile_money + $session->total_carte, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">FCFA</span>
                </span>
            </div>

            <div class="panel p-4">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-amber-600 block">Dépenses / Sorties</span>
                <span class="num text-xl font-bold text-amber-700 mt-1 block">
                    -{{ number_format($session->total_sorties_especes, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">FCFA</span>
                </span>
            </div>
        </div>

        {{-- Bilan de Clôture & Écart --}}
        <div class="panel p-6">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">Bilan de Réconciliation des Espèces</h2>

            <div class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-2xl bg-slate-100 p-4">
                    <span class="text-xs text-slate-500 font-medium block">Solde Espèces Attendu (Théorique)</span>
                    <span class="num text-xl font-black text-slate-900 mt-1 block">
                        {{ number_format($session->soldeTheoriqueAttendu(), 0, ',', ' ') }} FCFA
                    </span>
                    <span class="text-[11px] text-slate-400 block mt-0.5">Fond + Espèces encaissées − Dépenses</span>
                </div>

                <div class="rounded-2xl bg-emerald-50 p-4 border border-emerald-200">
                    <span class="text-xs text-emerald-800 font-medium block">Espèces Réelles Comptées (Billetage)</span>
                    <span class="num text-xl font-black text-emerald-800 mt-1 block">
                        @if ($session->montant_reel_compte !== null)
                            {{ number_format($session->montant_reel_compte, 0, ',', ' ') }} FCFA
                        @else
                            <span class="text-slate-400 text-sm font-normal">Non clôturée</span>
                        @endif
                    </span>
                    <span class="text-[11px] text-emerald-600 block mt-0.5">Total physique vérifié au tiroir</span>
                </div>

                <div class="rounded-2xl p-4 border @if($session->ecart_caisse === null) bg-slate-50 border-slate-200 @elseif($session->ecart_caisse == 0) bg-emerald-50 border-emerald-200 text-emerald-800 @elseif($session->ecart_caisse > 0) bg-sky-50 border-sky-200 text-sky-800 @else bg-rose-50 border-rose-200 text-rose-800 @endif">
                    <span class="text-xs font-medium block">Écart de Caisse Certifié</span>
                    <span class="num text-xl font-black mt-1 block">
                        @if ($session->ecart_caisse !== null)
                            {{ $session->ecart_caisse > 0 ? '+' : '' }}{{ number_format($session->ecart_caisse, 0, ',', ' ') }} FCFA
                        @else
                            <span class="text-slate-400 text-sm font-normal">En attente</span>
                        @endif
                    </span>
                    <span class="text-[11px] block mt-0.5">
                        @if ($session->ecart_caisse === null)
                            Sera calculé à la clôture
                        @elseif ($session->ecart_caisse == 0)
                            ✓ Caisse exacte, aucun manquant
                        @elseif ($session->ecart_caisse > 0)
                            ▲ Excédent constaté
                        @else
                            ▼ Manquant / Déficit constaté
                        @endif
                    </span>
                </div>
            </div>

            @if ($session->observations)
                <div class="mt-4 p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs text-slate-600">
                    <strong class="font-bold text-slate-800">Observations de la session :</strong> {{ $session->observations }}
                </div>
            @endif
        </div>

        {{-- Billetage Physique Détaillé (si clôturée) --}}
        @if (!empty($session->billetage))
            <div class="panel p-6">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">Détail du Billetage Enregistré</h2>
                <div class="grid gap-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                    @foreach ($session->billetage as $valeur => $qte)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs">
                            <div>
                                <span class="font-bold text-slate-900 block">{{ number_format($valeur, 0, ',', ' ') }} FCFA</span>
                                <span class="text-slate-400 text-[10px]">{{ $coupures[$valeur] ?? '' }}</span>
                            </div>
                            <div class="text-right">
                                <span class="num font-bold text-slate-800">&times; {{ $qte }}</span>
                                <span class="block num text-[11px] font-bold text-emerald-700">= {{ number_format(((float)$valeur) * $qte, 0, ',', ' ') }} F</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Mouvements & Dépenses de la Session --}}
        <div class="panel overflow-hidden">
            <div class="panel-head">
                <h2 class="panel-title">Dépenses & Mouvements d'Espèces ({{ $session->mouvements->count() }})</h2>
            </div>
            @if ($session->mouvements->isEmpty())
                <div class="p-8 text-center text-xs text-slate-400">
                    Aucune dépense ni mouvement d'espèces enregistré pour cette session.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="table-data text-xs">
                        <thead>
                            <tr>
                                <th>HEURE</th>
                                <th>TYPE</th>
                                <th>MOTIF</th>
                                <th>BÉNÉFICIAIRE</th>
                                <th>AUTEUR</th>
                                <th class="text-right">MONTANT</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($session->mouvements as $mvt)
                                <tr>
                                    <td>{{ $mvt->created_at->format('H:i') }}</td>
                                    <td>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $mvt->type === 'sortie' ? 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20' : 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20' }}">
                                            {{ $mvt->type === 'sortie' ? 'Dépense / Sortie' : 'Apport' }}
                                        </span>
                                    </td>
                                    <td class="font-medium text-slate-800">{{ $mvt->motif }}</td>
                                    <td class="text-slate-500">{{ $mvt->beneficiaire ?? '—' }}</td>
                                    <td class="text-slate-500">{{ $mvt->user->firstname }}</td>
                                    <td class="num font-bold {{ $mvt->type === 'sortie' ? 'text-amber-700' : 'text-emerald-700' }}">
                                        {{ $mvt->type === 'sortie' ? '-' : '+' }}{{ number_format($mvt->montant, 0, ',', ' ') }} FCFA
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Ventes rattachées à la Session --}}
        <div class="panel overflow-hidden">
            <div class="panel-head">
                <h2 class="panel-title">Ventes Réalisées ({{ $session->ventes->count() }})</h2>
            </div>
            @if ($session->ventes->isEmpty())
                <div class="p-8 text-center text-xs text-slate-400">
                    Aucune vente enregistrée sur cette session.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="table-data text-xs">
                        <thead>
                            <tr>
                                <th>TICKET N°</th>
                                <th>HEURE</th>
                                <th>MODE PAIEMENT</th>
                                <th>CAISSIER</th>
                                <th class="text-right">MONTANT TOTAL</th>
                                <th class="text-right">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($session->ventes as $vente)
                                <tr>
                                    <td class="font-bold text-slate-900">#{{ $vente->id }}</td>
                                    <td>{{ $vente->date_vente->format('H:i') }}</td>
                                    <td>
                                        <span class="inline-flex items-center text-[10px] font-semibold text-slate-700 bg-slate-100 px-2 py-0.5 rounded">
                                            {{ $vente->libelleModePaiement() }}
                                        </span>
                                    </td>
                                    <td class="text-slate-600">{{ $vente->user?->firstname }}</td>
                                    <td class="num font-bold text-slate-900">
                                        {{ number_format($vente->total, 0, ',', ' ') }} FCFA
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('ventes.show', $vente) }}" class="btn-icon inline-grid" title="Voir le ticket">
                                            <svg class="size-3.5 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>

@endsection
