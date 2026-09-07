<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rapport Z de Caisse — Session #{{ $session->id }} — GESTA PHARM</title>
    {{ Vite::fonts() }}
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; font-size: 11px !important; }
            .rapport-ticket { border: none !important; box-shadow: none !important; margin: 0 auto !important; max-width: 80mm !important; }
        }
    </style>
</head>
<body class="bg-slate-100 py-8 text-slate-900 font-sans antialiased">

    {{-- Barre d'actions écran (Masquée à l'impression) --}}
    <div class="no-print max-w-xl mx-auto mb-6 flex items-center justify-between gap-3 px-4">
        <a href="{{ route('caisse.sessions.show', $session) }}" class="btn-ghost text-xs">
            &larr; Retour à la session
        </a>
        <div class="flex items-center gap-2">
            <button type="button" onclick="window.print()" class="btn-primary text-xs shadow-md">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6 9 6 2 18 2 18 9"/>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                    <rect width="12" height="8" x="6" y="14"/>
                </svg>
                Imprimer le Rapport Z
            </button>
        </div>
    </div>

    {{-- Corps du Rapport Z (Format Reçu 80 mm / Officiel Officine) --}}
    <div class="rapport-ticket max-w-md mx-auto bg-white p-6 sm:p-8 rounded-2xl shadow-md border border-slate-200">

        {{-- En-tête de l'Officine --}}
        <div class="text-center border-b-2 border-dashed border-slate-300 pb-4 mb-4">
            <div class="inline-flex size-10 items-center justify-center rounded-xl bg-emerald-600 text-white font-bold text-lg mb-1">
                +
            </div>
            <h1 class="font-black text-base uppercase tracking-tight text-slate-900">GESTA PHARM</h1>
            <p class="text-xs text-slate-600 font-medium">Pharmacie d'Officine & Soins Médicaux</p>
            <p class="text-[11px] text-slate-500">Kati · Mali · Tél: +223 20 22 00 00</p>
            <div class="mt-2 inline-block bg-slate-900 text-white text-[11px] font-bold px-3 py-0.5 rounded-full uppercase tracking-wider">
                RAPPORT Z JOURNALIER
            </div>
        </div>

        {{-- Métadonnées de Session --}}
        <div class="text-xs space-y-1 border-b border-dashed border-slate-200 pb-3 mb-3 text-slate-600">
            <div class="flex justify-between">
                <span>Session N° :</span>
                <strong class="text-slate-900 font-mono">#{{ $session->id }}</strong>
            </div>
            <div class="flex justify-between">
                <span>Caissier :</span>
                <strong class="text-slate-900">{{ $session->user->firstname }} {{ $session->user->lastname }}</strong>
            </div>
            <div class="flex justify-between">
                <span>Ouverture :</span>
                <span class="num">{{ $session->date_ouverture->format('d/m/Y H:i') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Clôture :</span>
                <span class="num">{{ $session->date_fermeture?->format('d/m/Y H:i') ?? 'En cours' }}</span>
            </div>
            <div class="flex justify-between">
                <span>Nombre de ventes :</span>
                <strong class="num text-slate-900">{{ $session->ventes->count() }} tickets</strong>
            </div>
        </div>

        {{-- Ventilation des Ventes (Chiffre d'Affaires) --}}
        <div class="border-b border-dashed border-slate-200 pb-3 mb-3 text-xs">
            <h2 class="font-bold uppercase tracking-wider text-slate-400 text-[10px] mb-2">Chiffre d'Affaires & Encaissements</h2>
            <div class="space-y-1 text-slate-700">
                <div class="flex justify-between">
                    <span>Espèces (Cash) :</span>
                    <strong class="num text-slate-900">{{ number_format($session->total_especes_theorique, 0, ',', ' ') }} F</strong>
                </div>
                <div class="flex justify-between">
                    <span>Mobile Money (Orange / Wave) :</span>
                    <strong class="num text-slate-900">{{ number_format($session->total_mobile_money, 0, ',', ' ') }} F</strong>
                </div>
                <div class="flex justify-between">
                    <span>Carte bancaire :</span>
                    <strong class="num text-slate-900">{{ number_format($session->total_carte, 0, ',', ' ') }} F</strong>
                </div>
                <div class="flex justify-between font-bold text-sm text-emerald-800 pt-1.5 border-t border-slate-100">
                    <span>TOTAL C.A. DU JOUR :</span>
                    <span class="num">{{ number_format($session->totalChiffreAffaires(), 0, ',', ' ') }} FCFA</span>
                </div>
            </div>
        </div>

        {{-- Mouvements d'Espèces & Dépenses --}}
        <div class="border-b border-dashed border-slate-200 pb-3 mb-3 text-xs">
            <h2 class="font-bold uppercase tracking-wider text-slate-400 text-[10px] mb-2">Flux du Tiroir-Caisse (Liquide)</h2>
            <div class="space-y-1 text-slate-700">
                <div class="flex justify-between">
                    <span>Fond de caisse d'ouverture :</span>
                    <strong class="num">{{ number_format($session->fond_caisse_ouverture, 0, ',', ' ') }} F</strong>
                </div>
                <div class="flex justify-between text-emerald-700">
                    <span>+ Ventes espèces reçues :</span>
                    <strong class="num">+{{ number_format($session->total_especes_theorique, 0, ',', ' ') }} F</strong>
                </div>
                <div class="flex justify-between text-amber-700">
                    <span>− Dépenses / Sorties officine :</span>
                    <strong class="num">-{{ number_format($session->total_sorties_especes, 0, ',', ' ') }} F</strong>
                </div>
                <div class="flex justify-between font-bold text-slate-900 pt-1 border-t border-slate-100">
                    <span>SOLDE THÉORIQUE ATTENDU :</span>
                    <span class="num">{{ number_format($session->soldeTheoriqueAttendu(), 0, ',', ' ') }} FCFA</span>
                </div>
            </div>
        </div>

        {{-- Billetage Physique & Écart --}}
        <div class="border-b-2 border-dashed border-slate-300 pb-3 mb-4 text-xs">
            <h2 class="font-bold uppercase tracking-wider text-slate-400 text-[10px] mb-2">Comptage Physique & Écart</h2>
            <div class="space-y-1.5">
                <div class="flex justify-between font-bold text-sm text-slate-900">
                    <span>TOTAL RÉEL COMPTÉ :</span>
                    <span class="num text-emerald-800">{{ number_format($session->montant_reel_compte ?? 0, 0, ',', ' ') }} FCFA</span>
                </div>

                @php
                    $ecart = (float) ($session->ecart_caisse ?? 0);
                @endphp
                <div class="flex justify-between items-center p-2 rounded-lg font-bold text-xs @if($ecart == 0) bg-emerald-50 text-emerald-800 @elseif($ecart > 0) bg-sky-50 text-sky-800 @else bg-rose-50 text-rose-800 @endif">
                    <span>ÉCART DE CAISSE :</span>
                    <span class="num text-sm">
                        {{ $ecart > 0 ? '+' : '' }}{{ number_format($ecart, 0, ',', ' ') }} FCFA
                        @if($ecart == 0) (Conforme) @elseif($ecart > 0) (Excédent) @else (Déficit) @endif
                    </span>
                </div>
            </div>

            {{-- Inventaire rapide des coupures --}}
            @if (!empty($session->billetage))
                <div class="mt-3 pt-2 border-t border-slate-100">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Détail Billetage :</span>
                    <div class="grid grid-cols-2 gap-x-2 gap-y-0.5 text-[10px] text-slate-600 font-mono">
                        @foreach ($session->billetage as $valeur => $qte)
                            <div class="flex justify-between">
                                <span>{{ number_format($valeur, 0, ',', ' ') }} &times; {{ $qte }}</span>
                                <span class="font-bold">{{ number_format(((float)$valeur) * $qte, 0, ',', ' ') }} F</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Signatures réglementaires --}}
        <div class="pt-2 text-xs">
            <div class="grid grid-cols-2 gap-4 text-center mt-6">
                <div>
                    <span class="text-[10px] text-slate-400 uppercase tracking-wider block mb-8">Visa Caissier</span>
                    <div class="border-t border-slate-400 pt-1 text-[11px] font-medium text-slate-700">
                        {{ $session->user->firstname }} {{ $session->user->lastname }}
                    </div>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 uppercase tracking-wider block mb-8">Visa Pharmacien Titulaire</span>
                    <div class="border-t border-slate-400 pt-1 text-[11px] font-medium text-slate-700">
                        Dr. Pharmacien
                    </div>
                </div>
            </div>

            <p class="text-center text-[10px] text-slate-400 mt-6 pt-3 border-t border-slate-100">
                Document comptable certifié généré par GESTA PHARM le {{ now()->format('d/m/Y à H:i:s') }}.
            </p>
        </div>

    </div>

</body>
</html>
