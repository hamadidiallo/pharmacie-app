@extends('layout')

@section('titre', "Réception BL — Commande {$commande->reference}")

@section('topbar')
    <a href="{{ route('commandes.show', $commande) }}" class="btn-icon" aria-label="Retour à la commande">
        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M15 18l-6-6 6-6" />
        </svg>
    </a>
    <div class="text-base font-bold">Réception Marchandises & Saisie du Bon de Livraison (BL)</div>
@endsection

@section('content')

    <form action="{{ route('commandes.enregistrer-reception', $commande) }}" method="POST" class="space-y-6">
        @csrf

        {{-- Entête du Bon de Livraison --}}
        <div class="panel p-6 space-y-4">
            <div class="border-b border-slate-100 pb-3 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h1 class="text-base font-bold text-slate-900">Arrivage Fournisseur : {{ $commande->fournisseur->nom }}</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Rattaché au Bon de Commande {{ $commande->reference }}</p>
                </div>
                <span class="inline-flex items-center text-xs font-bold px-2.5 py-0.5 rounded-full {{ $commande->statut->badgeClasses() }}">
                    {{ $commande->statut->libelle() }}
                </span>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="numero_bl" class="field-label font-bold text-slate-800">Numéro du Bon de Livraison (BL) du grossiste *</label>
                    <input type="text" id="numero_bl" name="numero_bl" required placeholder="Ex: BL-LAB-2026-0982"
                        value="{{ old('numero_bl', $commande->numero_bl) }}" class="field-input font-mono uppercase font-bold text-sm">
                    <p class="mt-1 text-[11px] text-slate-400">Mentionné sur le bordereau papier remis par le livreur.</p>
                </div>

                <div>
                    <label for="total_facture" class="field-label font-bold text-slate-800">Montant Total Facturé TTC (FCFA)</label>
                    <input type="number" id="total_facture" name="total_facture" step="100" min="0"
                        placeholder="{{ number_format($commande->total_estime, 0, ',', ' ') }}"
                        value="{{ old('total_facture', $commande->total_estime) }}" class="field-input font-bold text-sm">
                    <p class="mt-1 text-[11px] text-slate-400">Permet de valider la facture d'achat par rapport à l'estimation.</p>
                </div>
            </div>
        </div>

        {{-- Saisie des Lots & Contrôle des Lignes --}}
        <div class="panel p-6 space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">
                    Contrôle des Boîtes & Traçabilité des Nouveaux Lots (FEFO)
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Pour chaque référence reçue, renseignez la quantité livrée, le numéro de lot et la date d'expiration imprimés sur les boîtes.
                </p>
            </div>

            <div class="space-y-4">
                @foreach ($commande->lignes as $index => $ligne)
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                        <input type="hidden" name="lignes[{{ $index }}][ligne_id]" value="{{ $ligne->id }}">

                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-200/80 pb-2.5">
                            <div>
                                <span class="font-bold text-sm text-slate-900">{{ $ligne->medicament->nom }}</span>
                                @if ($ligne->medicament->dci)
                                    <span class="text-xs text-emerald-700 font-medium ml-2">DCI: {{ $ligne->medicament->dci }}</span>
                                @endif
                            </div>
                            <div class="text-xs text-slate-500">
                                Commandé : <strong class="text-slate-800">{{ $ligne->quantite_commandee }}</strong>
                                · Déjà reçu : <strong class="text-slate-800">{{ $ligne->quantite_recue }}</strong>
                                · Reste à livrer : <strong class="text-rose-600">{{ $ligne->resteALivrer() }}</strong>
                            </div>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-4 text-xs">
                            <div>
                                <label class="field-label">Quantité livrée ce jour *</label>
                                <input type="number" name="lignes[{{ $index }}][quantite_recue]" required min="0" max="{{ $ligne->resteALivrer() }}"
                                    value="{{ $ligne->resteALivrer() }}" class="field-input h-9 text-xs font-bold text-center">
                            </div>

                            <div>
                                <label class="field-label">N° de Lot fabricant *</label>
                                <input type="text" name="lignes[{{ $index }}][numero_lot]" placeholder="Ex: LOT-2026-X45"
                                    class="field-input h-9 text-xs font-mono uppercase font-bold">
                            </div>

                            <div>
                                <label class="field-label">Date de péremption *</label>
                                <input type="date" name="lignes[{{ $index }}][date_expiration]"
                                    value="{{ now()->addMonths(18)->format('Y-m-d') }}"
                                    class="field-input h-9 text-xs font-semibold">
                            </div>

                            <div>
                                <label class="field-label">Prix achat unitaire facturé</label>
                                <input type="number" name="lignes[{{ $index }}][prix_achat_facture]" min="0"
                                    value="{{ (int) $ligne->prix_achat_unitaire_estime }}" class="field-input h-9 text-xs font-semibold text-right">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('commandes.show', $commande) }}" class="btn-ghost">Annuler</a>
            <button type="submit" class="btn-primary text-sm shadow-md bg-emerald-600 hover:bg-emerald-500">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                Valider la Réception & Allouer les Lots au Stock
            </button>
        </div>

    </form>

@endsection
