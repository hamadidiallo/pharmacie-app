<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bon de Commande {{ $commande->reference }} — GESTA PHARM</title>
    {{ Vite::fonts() }}
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; font-size: 12px !important; }
            .bon-doc { border: none !important; box-shadow: none !important; padding: 0 !important; max-width: 100% !important; }
        }
    </style>
</head>
<body class="bg-slate-100 py-8 text-slate-900 font-sans antialiased">

    {{-- Actions écran --}}
    <div class="no-print max-w-3xl mx-auto mb-6 flex items-center justify-between gap-3 px-4">
        <a href="{{ route('commandes.show', $commande) }}" class="btn-ghost text-xs">
            &larr; Retour à la commande
        </a>
        <button type="button" onclick="window.print()" class="btn-primary text-xs shadow-md">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="6 9 6 2 18 2 18 9"/>
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                <rect width="12" height="8" x="6" y="14"/>
            </svg>
            Imprimer / Exporter en PDF
        </button>
    </div>

    {{-- Bon de Commande Officiel A4 --}}
    <div class="bon-doc max-w-3xl mx-auto bg-white p-10 rounded-2xl shadow-md border border-slate-200">

        {{-- Entête & Émetteur --}}
        <div class="flex items-start justify-between border-b-2 border-slate-900 pb-6 mb-6">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="size-8 rounded-lg bg-emerald-600 text-white font-bold grid place-items-center text-base">+</span>
                    <span class="text-xl font-black tracking-tight text-slate-900">GESTA PHARM</span>
                </div>
                <p class="text-xs text-slate-600 font-medium">Pharmacie d'Officine & Soins Médicaux</p>
                <p class="text-xs text-slate-500">BP 124 · Kati · République du Mali</p>
                <p class="text-xs text-slate-500">Tél: +223 20 22 00 00 · Email: contact@gestapharm.ml</p>
            </div>

            <div class="text-right">
                <div class="inline-block bg-slate-900 text-white font-bold text-sm px-3.5 py-1 rounded-md uppercase tracking-wider mb-2">
                    BON DE COMMANDE
                </div>
                <div class="text-xs space-y-0.5 text-slate-600">
                    <div>Réf : <strong class="text-slate-900 font-mono">{{ $commande->reference }}</strong></div>
                    <div>Date : <strong class="text-slate-900 num">{{ $commande->date_commande->format('d/m/Y') }}</strong></div>
                    <div>Livraison souhaitée : <strong class="text-slate-900 num">{{ $commande->date_livraison_prevue?->format('d/m/Y') ?? 'Au plus tôt' }}</strong></div>
                </div>
            </div>
        </div>

        {{-- Destinataire / Grossiste --}}
        <div class="grid grid-cols-2 gap-6 mb-8 text-xs">
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Émetteur / Pharmacie :</span>
                <strong class="text-slate-900 text-sm block">PHARMACIE GESTA PHARM</strong>
                <p class="text-slate-600 mt-0.5">Responsable : Dr. Pharmacien Titulaire</p>
                <p class="text-slate-500">Kati, Mali</p>
            </div>

            <div class="p-4 rounded-xl bg-emerald-50/60 border border-emerald-200">
                <span class="text-[10px] uppercase font-bold text-emerald-700 block mb-1">Grossiste Répartiteur :</span>
                <strong class="text-slate-900 text-sm block">{{ $commande->fournisseur->nom }}</strong>
                <p class="text-slate-600 mt-0.5">{{ $commande->fournisseur->adresse ?? 'Service Commercial & Commandes' }}</p>
                <p class="text-slate-500">{{ $commande->fournisseur->ville }} · Tél : {{ $commande->fournisseur->telephone ?? 'Non précisé' }}</p>
                <p class="text-[11px] text-emerald-800 font-medium mt-1">Conditions : {{ $commande->fournisseur->conditions_paiement }}</p>
            </div>
        </div>

        {{-- Tableau des Articles --}}
        <div class="border border-slate-200 rounded-xl overflow-hidden mb-6">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200 uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="p-3">DÉSIGNATION DU PRODUIT & FORME</th>
                        <th class="p-3 text-center w-24">QUANTITÉ</th>
                        <th class="p-3 text-right w-36">PRIX UNITAIRE HT</th>
                        <th class="p-3 text-right w-36">TOTAL HT (FCFA)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($commande->lignes as $ligne)
                        <tr>
                            <td class="p-3">
                                <div class="font-bold text-slate-900">{{ $ligne->medicament->nom }}</div>
                                @if ($ligne->medicament->dci)
                                    <div class="text-[11px] text-slate-500">DCI : {{ $ligne->medicament->dci }}</div>
                                @endif
                                @if ($ligne->medicament->code_barre)
                                    <div class="text-[10px] font-mono text-slate-400">CIP: {{ $ligne->medicament->code_barre }}</div>
                                @endif
                            </td>
                            <td class="p-3 text-center font-bold text-slate-900 num">
                                {{ $ligne->quantite_commandee }}
                            </td>
                            <td class="p-3 text-right num text-slate-700">
                                {{ number_format($ligne->prix_achat_unitaire_estime, 0, ',', ' ') }} F
                            </td>
                            <td class="p-3 text-right num font-bold text-slate-900">
                                {{ number_format($ligne->sousTotalEstime(), 0, ',', ' ') }} FCFA
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-50 border-t-2 border-slate-200 font-bold text-xs">
                    <tr>
                        <td colspan="3" class="p-3 text-right uppercase tracking-wider text-slate-600">
                            Montant Total Prévisionnel HT :
                        </td>
                        <td class="p-3 text-right num font-black text-sm text-slate-900">
                            {{ number_format($commande->total_estime, 0, ',', ' ') }} FCFA
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if ($commande->notes)
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-600 mb-6">
                <strong class="font-bold text-slate-800">Observations / Consignes de livraison :</strong> {{ $commande->notes }}
            </div>
        @endif

        {{-- Signatures & Cachet --}}
        <div class="grid grid-cols-2 gap-8 pt-4 border-t border-slate-200 text-xs">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Émis par :</span>
                <p class="font-semibold text-slate-800">{{ $commande->user->firstname }} {{ $commande->user->lastname }}</p>
                <p class="text-[11px] text-slate-400">Agent Officine GESTA PHARM</p>
            </div>

            <div class="text-right">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Visa & Cachet du Pharmacien :</span>
                <div class="mt-8 border-t border-slate-300 inline-block w-48 pt-1 text-center font-bold text-slate-800">
                    Dr. Pharmacien Titulaire
                </div>
            </div>
        </div>

    </div>

</body>
</html>
