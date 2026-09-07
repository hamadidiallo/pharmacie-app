@extends('layout')

@section('titre', 'Nouveau Bon de Commande — GESTA PHARM')

@section('topbar')
    <a href="{{ route('commandes.index') }}" class="btn-icon" aria-label="Retour aux commandes">
        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M15 18l-6-6 6-6" />
        </svg>
    </a>
    <div class="text-base font-bold">Nouveau Bon de Commande Fournisseur</div>
@endsection

@section('content')

    <form action="{{ route('commandes.store') }}" method="POST" id="formCommande" class="space-y-6">
        @csrf

        {{-- Entête de la Commande --}}
        <div class="panel p-6 space-y-4">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-2">
                1. Fournisseur & Paramètres d'Acheminement
            </h2>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label for="fournisseur_id" class="field-label">Grossiste / Fournisseur *</label>
                    <select id="fournisseur_id" name="fournisseur_id" required class="field-input text-xs">
                        <option value="">Sélectionner un grossiste...</option>
                        @foreach ($fournisseurs as $fourn)
                            <option value="{{ $fourn->id }}" @selected(old('fournisseur_id') == $fourn->id)>
                                {{ $fourn->nom }} ({{ $fourn->ville }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="date_commande" class="field-label">Date d'émission du Bon *</label>
                    <input type="date" id="date_commande" name="date_commande" required value="{{ old('date_commande', date('Y-m-d')) }}"
                        class="field-input text-xs">
                </div>

                <div>
                    <label for="date_livraison_prevue" class="field-label">Date livraison souhaitée</label>
                    <input type="date" id="date_livraison_prevue" name="date_livraison_prevue" value="{{ old('date_livraison_prevue', now()->addDays(2)->format('Y-m-d')) }}"
                        class="field-input text-xs">
                </div>
            </div>

            <div>
                <label for="notes" class="field-label">Instructions ou remarques de commande</label>
                <input type="text" id="notes" name="notes" placeholder="Ex: Livraison matinée avant 11h, commande urgente..."
                    value="{{ old('notes') }}" class="field-input text-xs">
            </div>
        </div>

        {{-- Lignes d'articles commandés --}}
        <div class="panel p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">
                    2. Médicaments & Quantités Commandées
                </h2>
                <button type="button" id="btnAjouterLigne" class="btn-ghost text-xs h-8">
                    <svg class="size-3.5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M12 5v14M5 12h14" />
                    </svg>
                    Ajouter une ligne
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs" id="tableLignes">
                    <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                        <tr>
                            <th class="p-2.5">MÉDICAMENT *</th>
                            <th class="p-2.5 w-32">QUANTITÉ *</th>
                            <th class="p-2.5 w-40">PRIX ACHAT ESTIMÉ (FCFA) *</th>
                            <th class="p-2.5 text-right w-36">SOUS-TOTAL</th>
                            <th class="p-2.5 w-10 text-right"></th>
                        </tr>
                    </thead>
                    <tbody id="lignesContainer" class="divide-y divide-slate-100">
                        {{-- Ligne 1 par défaut --}}
                        <tr class="ligne-commande">
                            <td class="p-2.5">
                                <select name="lignes[0][medicament_id]" required class="select-med field-input h-9 text-xs">
                                    <option value="">Choisir un médicament...</option>
                                    @foreach ($medicaments as $med)
                                        <option value="{{ $med->id }}" data-prix="{{ round($med->prix * 0.7) }}">
                                            {{ $med->nom }} (Stock: {{ $med->stock }})
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="p-2.5">
                                <input type="number" name="lignes[0][quantite_commandee]" required min="1" value="20"
                                    class="input-qte field-input h-9 text-xs font-bold text-center">
                            </td>
                            <td class="p-2.5">
                                <input type="number" name="lignes[0][prix_achat_unitaire_estime]" required min="0" value="1000"
                                    class="input-prix field-input h-9 text-xs font-bold text-right">
                            </td>
                            <td class="p-2.5 text-right font-bold text-slate-900 num sous-total-cell">
                                20 000 F
                            </td>
                            <td class="p-2.5 text-right">
                                <button type="button" class="btn-supprimer-ligne text-slate-400 hover:text-rose-600 p-1">
                                    &times;
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Total de la commande --}}
            <div class="flex items-baseline justify-end gap-3 pt-3 border-t border-slate-100">
                <span class="text-xs uppercase font-bold text-slate-500">Total Prévisionnel :</span>
                <span class="num text-2xl font-black text-emerald-800">
                    <span id="totalCommande">20 000</span> <span class="text-sm font-bold text-emerald-600">FCFA</span>
                </span>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('commandes.index') }}" class="btn-ghost">Annuler</a>
            <button type="submit" class="btn-primary text-sm shadow-md">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                    <polyline points="17 21 17 13 7 13 7 21"/>
                    <polyline points="7 3 7 8 15 8"/>
                </svg>
                Enregistrer le Bon de Commande
            </button>
        </div>
    </form>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        let indexLigne = 1;
        const container = document.getElementById('lignesContainer');
        const btnAjouter = document.getElementById('btnAjouterLigne');
        const totalEl = document.getElementById('totalCommande');
        const formatFcfa = (n) => new Intl.NumberFormat('fr-FR').format(n);

        const optionsMedicaments = `{!! addslashes(
            collect($medicaments)->map(fn($m) => "<option value='{$m->id}' data-prix='" . round($m->prix * 0.7) . "'>{$m->nom} (Stock: {$m->stock})</option>")->join('')
        ) !!}`;

        function recalculerTotaux() {
            let total = 0;
            const lignes = document.querySelectorAll('.ligne-commande');

            lignes.forEach(ligne => {
                const qte = parseInt(ligne.querySelector('.input-qte')?.value) || 0;
                const prix = parseFloat(ligne.querySelector('.input-prix')?.value) || 0;
                const st = qte * prix;
                total += st;

                const cellSt = ligne.querySelector('.sous-total-cell');
                if (cellSt) {
                    cellSt.textContent = formatFcfa(st) + ' F';
                }
            });

            if (totalEl) {
                totalEl.textContent = formatFcfa(total);
            }
        }

        btnAjouter.addEventListener('click', () => {
            const tr = document.createElement('tr');
            tr.className = 'ligne-commande';
            tr.innerHTML = `
                <td class="p-2.5">
                    <select name="lignes[${indexLigne}][medicament_id]" required class="select-med field-input h-9 text-xs">
                        <option value="">Choisir un médicament...</option>
                        ${optionsMedicaments}
                    </select>
                </td>
                <td class="p-2.5">
                    <input type="number" name="lignes[${indexLigne}][quantite_commandee]" required min="1" value="20"
                        class="input-qte field-input h-9 text-xs font-bold text-center">
                </td>
                <td class="p-2.5">
                    <input type="number" name="lignes[${indexLigne}][prix_achat_unitaire_estime]" required min="0" value="1000"
                        class="input-prix field-input h-9 text-xs font-bold text-right">
                </td>
                <td class="p-2.5 text-right font-bold text-slate-900 num sous-total-cell">
                    20 000 F
                </td>
                <td class="p-2.5 text-right">
                    <button type="button" class="btn-supprimer-ligne text-slate-400 hover:text-rose-600 p-1">
                        &times;
                    </button>
                </td>
            `;

            container.appendChild(tr);
            indexLigne++;
            attacherEvenements(tr);
            recalculerTotaux();
        });

        function attacherEvenements(ligne) {
            const selectMed = ligne.querySelector('.select-med');
            const inputPrix = ligne.querySelector('.input-prix');
            const inputQte = ligne.querySelector('.input-qte');
            const btnSuppr = ligne.querySelector('.btn-supprimer-ligne');

            if (selectMed) {
                selectMed.addEventListener('change', () => {
                    const opt = selectMed.options[selectMed.selectedIndex];
                    const prixEstime = opt.dataset.prix;
                    if (prixEstime && inputPrix) {
                        inputPrix.value = prixEstime;
                    }
                    recalculerTotaux();
                });
            }

            if (inputPrix) inputPrix.addEventListener('input', recalculerTotaux);
            if (inputQte) inputQte.addEventListener('input', recalculerTotaux);

            if (btnSuppr) {
                btnSuppr.addEventListener('click', () => {
                    if (document.querySelectorAll('.ligne-commande').length > 1) {
                        ligne.remove();
                        recalculerTotaux();
                    }
                });
            }
        }

        document.querySelectorAll('.ligne-commande').forEach(attacherEvenements);
        recalculerTotaux();
    });
</script>
@endpush
