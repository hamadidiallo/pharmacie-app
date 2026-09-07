@extends('layout')

@section('titre', 'Ticket TCK-' . $vente->id . ' — GESTA PHARM')

@section('topbar')
    <div class="flex items-center gap-3">
        <a href="{{ route('ventes.index') }}" class="btn-icon" aria-label="Retour aux ventes" title="Retour aux ventes">
            <svg class="size-4 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M15 18l-6-6 6-6" />
            </svg>
        </a>
        <div>
            <h1 class="text-base font-bold text-slate-900 tracking-tight">Reçu de caisse TCK-{{ $vente->id }}</h1>
            <p class="text-xs text-slate-500">Détail et impression du ticket de vente</p>
        </div>
    </div>
    <div class="ml-auto flex items-center gap-2">
        <button type="button" onclick="window.print()" class="btn-primary inline-flex items-center gap-2">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                <path d="M6 14h12v8H6z"/>
            </svg>
            <span>Imprimer le ticket</span>
        </button>
        <a href="{{ route('ventes.pdf', $vente) }}" class="btn-ghost inline-flex items-center gap-2">
            <svg class="size-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/>
            </svg>
            <span>PDF</span>
        </a>
    </div>
@endsection

@section('content')

    <div class="flex flex-col items-center py-4">

        {{-- Reçu thermique 80 mm --}}
        <div class="ticket-edge no-print w-[84mm] max-w-full opacity-60" aria-hidden="true"></div>

        <article id="ticket"
            class="ticket bg-white px-7 py-6 font-mono text-[12px] leading-relaxed text-slate-900 shadow-xl ring-1 ring-slate-200/80 rounded-b-none w-[84mm] max-w-full">

            <header class="text-center">
                <div class="mx-auto mb-2 grid size-9 place-items-center rounded-xl bg-emerald-700 text-white font-sans font-black text-sm">
                    GP
                </div>
                <h2 class="text-sm font-bold tracking-widest uppercase">Pharmacie GestaPharm</h2>
                <p class="text-slate-500">KATI, MALI</p>
                <p class="text-slate-500">Tél : +223 78 14 43 59</p>
            </header>

            <div class="my-3 border-t border-dashed border-slate-300"></div>

            <dl class="space-y-1 text-slate-700">
                <div class="flex justify-between">
                    <dt>Ticket N° :</dt>
                    <dd class="font-bold text-slate-900">TCK-{{ $vente->id }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt>Date :</dt>
                    <dd>{{ $vente->date_vente->format('d/m/Y') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt>Heure :</dt>
                    <dd>{{ $vente->created_at->format('H:i:s') }}</dd>
                </div>
            </dl>

            <div class="my-3 border-t border-dashed border-slate-300"></div>

            <table class="w-full tabular-nums">
                <thead>
                    <tr class="border-b border-dashed border-slate-300 text-left text-slate-500">
                        <th class="pb-1.5 font-semibold">Désignation</th>
                        <th class="pb-1.5 text-right font-semibold">Qté</th>
                        <th class="pb-1.5 text-right font-semibold">P.U.</th>
                        <th class="pb-1.5 text-right font-semibold">Total</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-dashed divide-slate-100">
                    @foreach ($vente->medicaments as $medicament)
                        <tr>
                            <td class="py-1.5 pr-2 font-medium text-slate-900">{{ $medicament->nom }}</td>
                            <td class="py-1.5 text-right text-slate-600">{{ $medicament->pivot->quantite }}</td>
                            <td class="py-1.5 text-right text-slate-600">
                                {{ number_format($medicament->pivot->prix, 0, ',', ' ') }}
                            </td>
                            <td class="py-1.5 text-right font-bold text-slate-900">
                                {{ number_format($medicament->pivot->sous_total, 0, ',', ' ') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="my-3 border-t-2 border-dashed border-slate-400"></div>

            <p class="flex items-baseline justify-between text-base font-black text-slate-900">
                <span>TOTAL BRUT</span>
                <span>{{ number_format($vente->total, 0, ',', ' ') }} FCFA</span>
            </p>

            @if($vente->estPriseEnCharge())
                <div class="mt-2 space-y-1 bg-amber-50/80 p-2 rounded border border-dashed border-amber-200 text-xs">
                    <div class="flex justify-between text-amber-900">
                        <span>Prise en charge {{ $vente->assurance?->code ?? 'Assur.' }} ({{ (int)$vente->taux_couverture }}%) :</span>
                        <span class="font-bold">- {{ number_format($vente->part_assurance, 0, ',', ' ') }} FCFA</span>
                    </div>
                    <div class="flex justify-between text-slate-900 font-black text-sm border-t border-amber-200/60 pt-1">
                        <span>NET PAYÉ (Assuré) :</span>
                        <span class="text-emerald-700">{{ number_format($vente->part_patient, 0, ',', ' ') }} FCFA</span>
                    </div>
                    <div class="text-[10px] text-slate-600 font-mono mt-0.5">
                        Matr: {{ $vente->matricule_assure }} {{ $vente->nom_assure ? '• ' . $vente->nom_assure : '' }}
                    </div>
                </div>
            @endif

            @if($vente->ordonnancierLignes->isNotEmpty())
                <div class="mt-2 bg-purple-50/70 p-2 rounded border border-dashed border-purple-200 text-[10px] text-purple-900 space-y-0.5">
                    <div class="font-bold">Prescription Réglementaire :</div>
                    <div>Dr : {{ $vente->ordonnancierLignes->first()->nom_prescripteur }}</div>
                    <div>Patient : {{ $vente->ordonnancierLignes->first()->nom_patient }}</div>
                    <div class="font-mono text-[9px] text-purple-700">N° : {{ $vente->ordonnancierLignes->pluck('numero_ordonnancier')->join(', ') }}</div>
                </div>
            @endif

            <dl class="mt-3 space-y-1 text-slate-700 bg-slate-50 p-2.5 rounded-lg">
                <div class="flex justify-between">
                    <dt>Mode de paiement :</dt>
                    <dd class="font-bold text-slate-900">{{ $vente->libelleModePaiement() }}</dd>
                </div>
                @if ($vente->montant_recu !== null)
                    <div class="flex justify-between">
                        <dt>Montant reçu :</dt>
                        <dd>{{ number_format($vente->montant_recu, 0, ',', ' ') }} FCFA</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt>Monnaie rendue :</dt>
                        <dd class="font-bold text-emerald-700">{{ number_format($vente->monnaie_rendue, 0, ',', ' ') }} FCFA</dd>
                    </div>
                @endif
            </dl>

            <div class="my-4 border-t border-dashed border-slate-300"></div>

            <p class="text-center text-xs font-semibold text-slate-600">
                Merci pour votre visite !<br>
                <span class="text-[10px] text-slate-400 font-normal">Les médicaments ne sont ni repris ni échangés.</span>
            </p>

        </article>

        <div class="ticket-edge ticket-edge-bottom no-print w-[84mm] max-w-full opacity-60" aria-hidden="true"></div>

        <div class="no-print mt-6 flex flex-wrap justify-center gap-3">
            <a href="{{ route('ventes.create') }}" class="btn-primary inline-flex items-center gap-2">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                <span>Nouvelle vente</span>
            </a>
            <a href="{{ route('ventes.index') }}" class="btn-ghost">
                Consulter l'historique
            </a>
        </div>

    </div>

@endsection
