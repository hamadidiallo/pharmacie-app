@extends('layout')

@section('titre', 'Suggestion de Commande — GESTA PHARM')

@section('topbar')
    <a href="{{ route('commandes.index') }}" class="btn-icon" aria-label="Retour aux commandes">
        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M15 18l-6-6 6-6" />
        </svg>
    </a>
    <div class="text-base font-bold">Assistant Réassort & Suggestion Automatique</div>
@endsection

@section('content')

    <div class="space-y-6">

        {{-- Bandeau d'information Assistant --}}
        <div class="panel p-6 bg-gradient-to-r from-emerald-50/60 via-teal-50/40 to-sky-50/40 border-emerald-200">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="flex size-12 items-center justify-center rounded-2xl bg-emerald-600 text-white shadow-md shadow-emerald-600/25">
                        <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3Z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-base font-bold text-slate-900">Analyse Automatique des Stocks & Ruptures</h1>
                        <p class="text-xs text-slate-600 mt-0.5">
                            Le système a détecté <strong class="text-slate-900">{{ $besoins->count() }}</strong> médicament(s) sous leur seuil de sécurité ou en rupture.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="text-right">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Estimation Achats</span>
                        <span class="num text-xl font-black text-emerald-800">{{ number_format($totalEstime, 0, ',', ' ') }} FCFA</span>
                    </div>
                </div>
            </div>
        </div>

        @if ($besoins->isEmpty())
            <div class="panel p-12 text-center text-slate-500">
                <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 mb-3">
                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 6 9 17l-5-5"/>
                    </svg>
                </div>
                <h2 class="text-base font-bold text-slate-900">Vos stocks sont optimaux !</h2>
                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                    Aucun médicament n'est actuellement en rupture ou sous son seuil de sécurité.
                </p>
                <div class="mt-4">
                    <a href="{{ route('commandes.index') }}" class="btn-ghost text-xs">Retour aux commandes</a>
                </div>
            </div>
        @else

            <form action="{{ route('commandes.generer-automatique') }}" method="POST">
                @csrf

                {{-- Choix du Grossiste Répartiteur --}}
                <div class="panel p-5 mb-4 flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3 flex-1 min-w-[280px]">
                        <label for="fournisseur_id" class="text-xs font-bold text-slate-800 whitespace-nowrap">
                            Grossiste destinataire * :
                        </label>
                        <select id="fournisseur_id" name="fournisseur_id" required class="field-input h-10 text-xs py-1.5 flex-1 max-w-md">
                            @foreach ($fournisseurs as $fourn)
                                <option value="{{ $fourn->id }}">
                                    {{ $fourn->nom }} (Délai moyen : ~{{ $fourn->delai_livraison_jours }} jours · {{ $fourn->conditions_paiement }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="btn-primary text-xs shadow-md">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="m5 12 5 5L20 7"/>
                        </svg>
                        Créer le Bon de Commande Suggéré
                    </button>
                </div>

                {{-- Tableau des suggestions --}}
                <div class="panel overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="table-data text-xs">
                            <thead>
                                <tr>
                                    <th class="w-10 text-center">
                                        <input type="checkbox" id="checkAll" checked class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                    </th>
                                    <th>MÉDICAMENT & DCI</th>
                                    <th>STOCK ACTUEL</th>
                                    <th>SEUIL SÉCURITÉ</th>
                                    <th>QUANTITÉ SUGGÉRÉE</th>
                                    <th>PRIX ACHAT ESTIMÉ</th>
                                    <th class="text-right">SOUS-TOTAL ESTIMÉ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($besoins as $item)
                                    @php
                                        $med = $item['medicament'];
                                        $enRupture = $med->stock <= 0;
                                    @endphp
                                    <tr class="hover:bg-slate-50/70 transition-colors">
                                        <td class="text-center">
                                            <input type="checkbox" name="medicaments[]" value="{{ $med->id }}" checked
                                                class="check-item rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                        </td>
                                        <td class="py-3">
                                            <div class="font-bold text-slate-900">{{ $med->nom }}</div>
                                            @if ($med->dci)
                                                <div class="text-[11px] text-emerald-700 font-medium">DCI: {{ $med->dci }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <span @class([
                                                'inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold',
                                                'bg-rose-50 text-rose-700 ring-1 ring-rose-600/20' => $enRupture,
                                                'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20' => !$enRupture,
                                            ])>
                                                {{ $med->stock }} boîte(s)
                                            </span>
                                        </td>
                                        <td class="text-slate-500 font-medium">
                                            {{ $item['stock_securite'] }} boîtes
                                        </td>
                                        <td>
                                            <span class="font-bold text-slate-900 text-sm num">+{{ $item['quantite_suggeree'] }}</span>
                                        </td>
                                        <td class="num text-slate-700">
                                            {{ number_format($item['prix_achat_estime'], 0, ',', ' ') }} F
                                        </td>
                                        <td class="num font-bold text-slate-900 text-right">
                                            {{ number_format($item['sous_total_estime'], 0, ',', ' ') }} FCFA
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </form>

        @endif

    </div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const checkAll = document.getElementById('checkAll');
        const items = document.querySelectorAll('.check-item');

        if (checkAll) {
            checkAll.addEventListener('change', () => {
                items.forEach(cb => cb.checked = checkAll.checked);
            });
        }
    });
</script>
@endpush
