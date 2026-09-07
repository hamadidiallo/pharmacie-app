@extends('layout')

@section('titre', "Commande {$commande->reference} — GESTA PHARM")

@section('topbar')
    <a href="{{ route('commandes.index') }}" class="btn-icon" aria-label="Retour aux commandes">
        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M15 18l-6-6 6-6" />
        </svg>
    </a>
    <div class="text-base font-bold">Bon de Commande {{ $commande->reference }}</div>
@endsection

@section('content')

    <div class="space-y-6">

        {{-- En-tête & Barre d'Actions --}}
        <div class="panel p-6 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="flex size-12 items-center justify-center rounded-2xl bg-sky-50 text-sky-700 ring-1 ring-sky-600/20">
                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2.5">
                        <h1 class="text-lg font-bold text-slate-900">{{ $commande->reference }}</h1>
                        <span class="inline-flex items-center text-xs font-bold px-2.5 py-0.5 rounded-full {{ $commande->statut->badgeClasses() }}">
                            {{ $commande->statut->libelle() }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Grossiste : <strong class="text-slate-800">{{ $commande->fournisseur->nom }}</strong>
                        · Émise le {{ $commande->date_commande->format('d/m/Y') }} par {{ $commande->user->firstname }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                {{-- Bouton impression bon de commande --}}
                <a href="{{ route('commandes.bon-commande', $commande) }}" class="btn-ghost text-xs">
                    <svg class="size-4 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 6 2 18 2 18 9"/>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                        <rect width="12" height="8" x="6" y="14"/>
                    </svg>
                    Imprimer le Bon
                </a>

                {{-- Action envoyer si brouillon --}}
                @if ($commande->estModifiable())
                    <form action="{{ route('commandes.envoyer', $commande) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-primary text-xs shadow-md">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                            </svg>
                            Marquer comme envoyée au grossiste
                        </button>
                    </form>
                @endif

                {{-- Action réceptionner BL si envoyée ou partielle --}}
                @if ($commande->peutEtreRecue())
                    <a href="{{ route('commandes.reception', $commande) }}" class="btn-primary bg-emerald-600 hover:bg-emerald-500 text-xs shadow-md">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" />
                            <path d="m3.3 7 8.7 5 8.7-5" /><path d="M12 22V12" />
                        </svg>
                        Réceptionner le Bon de Livraison (BL)
                    </a>
                @endif
            </div>
        </div>

        {{-- Barre de progression du cycle d'achat --}}
        <div class="panel p-5">
            <div class="grid grid-cols-4 gap-2 text-center text-xs">
                <div class="space-y-1">
                    <span class="block size-2.5 mx-auto rounded-full bg-emerald-500 ring-4 ring-emerald-100"></span>
                    <span class="font-bold text-slate-800">1. Brouillon</span>
                    <span class="block text-[10px] text-slate-400">Préparé</span>
                </div>
                <div class="space-y-1">
                    <span @class([
                        'block size-2.5 mx-auto rounded-full',
                        'bg-emerald-500 ring-4 ring-emerald-100' => $commande->statut !== \App\Enums\StatutCommandeFournisseur::Brouillon,
                        'bg-slate-300' => $commande->statut === \App\Enums\StatutCommandeFournisseur::Brouillon,
                    ])></span>
                    <span class="font-bold text-slate-800">2. Envoyée</span>
                    <span class="block text-[10px] text-slate-400">Transmise au grossiste</span>
                </div>
                <div class="space-y-1">
                    <span @class([
                        'block size-2.5 mx-auto rounded-full',
                        'bg-emerald-500 ring-4 ring-emerald-100' => in_array($commande->statut, [\App\Enums\StatutCommandeFournisseur::PartiellementRecue, \App\Enums\StatutCommandeFournisseur::Recue]),
                        'bg-slate-300' => !in_array($commande->statut, [\App\Enums\StatutCommandeFournisseur::PartiellementRecue, \App\Enums\StatutCommandeFournisseur::Recue]),
                    ])></span>
                    <span class="font-bold text-slate-800">3. Arrivage BL</span>
                    <span class="block text-[10px] text-slate-400">Contrôle physique</span>
                </div>
                <div class="space-y-1">
                    <span @class([
                        'block size-2.5 mx-auto rounded-full',
                        'bg-emerald-500 ring-4 ring-emerald-100' => $commande->statut === \App\Enums\StatutCommandeFournisseur::Recue,
                        'bg-slate-300' => $commande->statut !== \App\Enums\StatutCommandeFournisseur::Recue,
                    ])></span>
                    <span class="font-bold text-slate-800">4. Réceptionnée</span>
                    <span class="block text-[10px] text-slate-400">Lots & stock alloués</span>
                </div>
            </div>
        </div>

        {{-- Détails du Fournisseur & Paramètres --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="panel p-4">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 block">Fournisseur</span>
                <span class="font-bold text-slate-900 mt-1 block">{{ $commande->fournisseur->nom }}</span>
                <span class="text-xs text-slate-500 block">{{ $commande->fournisseur->telephone ?? 'Pas de tél' }} · {{ $commande->fournisseur->ville }}</span>
            </div>

            <div class="panel p-4">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 block">Date de Livraison</span>
                <span class="num font-bold text-slate-900 mt-1 block">
                    {{ $commande->date_livraison_prevue?->format('d/m/Y') ?? 'Non précisée' }}
                </span>
                <span class="text-xs text-slate-500 block">Délai habituel ~{{ $commande->fournisseur->delai_livraison_jours }} jours</span>
            </div>

            <div class="panel p-4">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 block">N° Bon de Livraison (BL)</span>
                <span class="font-mono font-bold text-slate-900 mt-1 block">
                    {{ $commande->numero_bl ?? 'En attente' }}
                </span>
                <span class="text-xs text-slate-500 block">
                    {{ $commande->date_reception ? 'Reçu le ' . $commande->date_reception->format('d/m/Y') : 'Livraison en cours' }}
                </span>
            </div>

            <div class="panel p-4">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-emerald-700 block">Total Prévisionnel</span>
                <span class="num text-xl font-bold text-emerald-800 mt-1 block">
                    {{ number_format($commande->total_estime, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">FCFA</span>
                </span>
                @if ($commande->total_facture)
                    <span class="text-xs font-semibold text-slate-600 block">Facturé : {{ number_format($commande->total_facture, 0, ',', ' ') }} F</span>
                @endif
            </div>
        </div>

        {{-- Tableau des Lignes de Commande --}}
        <div class="panel overflow-hidden">
            <div class="panel-head">
                <h2 class="panel-title">Articles Commandés ({{ $commande->lignes->count() }})</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="table-data text-xs">
                    <thead>
                        <tr>
                            <th>MÉDICAMENT & DCI</th>
                            <th>QUANTITÉ COMMANDÉE</th>
                            <th>QUANTITÉ REÇUE</th>
                            <th>RESTE À LIVRER</th>
                            <th>PRIX ACHAT UNITAIRE</th>
                            <th>LOT REÇU & EXPIRATION</th>
                            <th class="text-right">SOUS-TOTAL ESTIMÉ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($commande->lignes as $ligne)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3">
                                    <div class="font-bold text-slate-900">{{ $ligne->medicament->nom }}</div>
                                    @if ($ligne->medicament->dci)
                                        <div class="text-[11px] text-emerald-700 font-medium">DCI: {{ $ligne->medicament->dci }}</div>
                                    @endif
                                </td>

                                <td class="num font-bold text-slate-800">
                                    {{ $ligne->quantite_commandee }} boîtes
                                </td>

                                <td class="num">
                                    <span @class([
                                        'font-bold',
                                        'text-emerald-700' => $ligne->quantite_recue >= $ligne->quantite_commandee,
                                        'text-amber-700' => $ligne->quantite_recue > 0 && $ligne->quantite_recue < $ligne->quantite_commandee,
                                        'text-slate-400' => $ligne->quantite_recue == 0,
                                    ])>
                                        {{ $ligne->quantite_recue }} boîtes
                                    </span>
                                </td>

                                <td class="num">
                                    @if ($ligne->resteALivrer() > 0)
                                        <span class="font-bold text-rose-600">{{ $ligne->resteALivrer() }}</span>
                                    @else
                                        <span class="text-emerald-600 font-bold">✓ Soldé</span>
                                    @endif
                                </td>

                                <td class="num">
                                    <div>{{ number_format($ligne->prix_achat_unitaire_estime, 0, ',', ' ') }} F</div>
                                    @if ($ligne->prix_achat_unitaire_facture)
                                        <div class="text-[10px] text-emerald-700 font-bold">Facturé: {{ number_format($ligne->prix_achat_unitaire_facture, 0, ',', ' ') }} F</div>
                                    @endif
                                </td>

                                <td>
                                    @if ($ligne->numero_lot_recu)
                                        <div class="font-mono font-bold text-slate-800">{{ $ligne->numero_lot_recu }}</div>
                                        <div class="text-[10px] text-slate-500">Exp: {{ $ligne->date_expiration_recue?->format('d/m/Y') }}</div>
                                    @else
                                        <span class="text-slate-400 text-[11px]">—</span>
                                    @endif
                                </td>

                                <td class="num font-bold text-slate-900 text-right">
                                    {{ number_format($ligne->sousTotalEstime(), 0, ',', ' ') }} FCFA
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

@endsection
