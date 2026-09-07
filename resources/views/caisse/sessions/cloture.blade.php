@extends('layout')

@section('titre', "Clôture de Caisse & Billetage — Session #{$session->id}")

@section('topbar')
    <a href="{{ route('caisse.sessions.index') }}" class="btn-icon" aria-label="Retour aux sessions">
        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M15 18l-6-6 6-6" />
        </svg>
    </a>
    <div class="text-base font-bold">Clôture de Caisse & Billetage — Session #{{ $session->id }}</div>
@endsection

@section('content')

    <form action="{{ route('caisse.sessions.cloturer', $session) }}" method="POST" id="formCloture">
        @csrf

        <div class="grid gap-6 lg:grid-cols-[1.4fr_1fr]">

            {{-- Colonne 1 : Saisie interactive du Billetage --}}
            <div class="space-y-4">
                <div class="panel p-6">
                    <div class="border-b border-slate-100 pb-4 mb-4">
                        <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                            <svg class="size-5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="6" width="20" height="12" rx="2" />
                                <circle cx="12" cy="12" r="2" />
                                <path d="M6 12h.01M18 12h.01" />
                            </svg>
                            Comptage Physique des Espèces (Billetage)
                        </h2>
                        <p class="text-xs text-slate-500 mt-1">
                            Comptez et saisissez le nombre exact de billets et de pièces présents physiquement dans votre tiroir-caisse.
                        </p>
                    </div>

                    <div class="border border-slate-200 rounded-xl overflow-hidden">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                                <tr>
                                    <th class="p-3">COUPURE / VALEUR</th>
                                    <th class="p-3 w-32">NOMBRE</th>
                                    <th class="p-3 text-right">SOUS-TOTAL</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($coupures as $valeur => $libelle)
                                    <tr class="hover:bg-slate-50/70 transition-colors">
                                        <td class="p-3">
                                            <div class="font-bold text-slate-800">{{ $libelle }}</div>
                                            <div class="text-[10px] text-slate-400 font-mono">{{ number_format($valeur, 0, ',', ' ') }} FCFA</div>
                                        </td>
                                        <td class="p-3">
                                            <input type="number" name="billetage[{{ $valeur }}]"
                                                data-coupure="{{ $valeur }}" min="0" step="1" placeholder="0"
                                                class="input-coupure field-input h-9 text-xs font-bold text-center">
                                        </td>
                                        <td class="p-3 text-right">
                                            <span id="sous-total-{{ $valeur }}" class="num font-bold text-slate-900">0</span>
                                            <span class="text-[10px] text-slate-400">F</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Observations & Justificatifs éventuels --}}
                <div class="panel p-5">
                    <label class="field-label font-bold text-slate-800">Observations de clôture / Justification d'écart éventuel</label>
                    <textarea name="observations" rows="3" placeholder="Renseignez tout commentaire utile pour le pharmacien titulaire..."
                        class="field-input h-auto py-2.5 text-xs"></textarea>
                </div>
            </div>

            {{-- Colonne 2 : Récapitulatif en Direct & Contrôle de l'Écart --}}
            <div class="space-y-4">

                <div class="panel p-6 sticky top-24 space-y-5">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-3">
                        Contrôle de Clôture & Écart
                    </h3>

                    {{-- Détail des flux théoriques --}}
                    <div class="space-y-2.5 text-xs">
                        <div class="flex items-center justify-between text-slate-600">
                            <span>Fond de caisse d'ouverture :</span>
                            <span class="num font-bold text-slate-900">{{ number_format($session->fond_caisse_ouverture, 0, ',', ' ') }} FCFA</span>
                        </div>
                        <div class="flex items-center justify-between text-emerald-700">
                            <span>+ Ventes en espèces :</span>
                            <span class="num font-bold">+{{ number_format($session->total_especes_theorique, 0, ',', ' ') }} FCFA</span>
                        </div>
                        <div class="flex items-center justify-between text-amber-700">
                            <span>− Dépenses / Sorties de caisse :</span>
                            <span class="num font-bold">-{{ number_format($session->total_sorties_especes, 0, ',', ' ') }} FCFA</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-500 pt-1 border-t border-slate-100">
                            <span>Ventes Mobile Money (séparées) :</span>
                            <span class="num font-semibold">{{ number_format($session->total_mobile_money, 0, ',', ' ') }} FCFA</span>
                        </div>
                    </div>

                    {{-- Solde théorique attendu --}}
                    <div class="rounded-2xl bg-slate-100 p-4 border border-slate-200">
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 block">Solde Espèces Attendu</span>
                        <span class="num text-2xl font-black text-slate-900 mt-1 block">
                            <span id="soldeTheorique">{{ (int) $session->soldeTheoriqueAttendu() }}</span> <span class="text-xs font-bold text-slate-400">FCFA</span>
                        </span>
                    </div>

                    {{-- Total compté physique --}}
                    <div class="rounded-2xl bg-emerald-50 p-4 border border-emerald-200">
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-emerald-700 block">Total Physique Compté</span>
                        <span class="num text-2xl font-black text-emerald-800 mt-1 block">
                            <span id="totalPhysique">0</span> <span class="text-xs font-bold text-emerald-600">FCFA</span>
                        </span>
                    </div>

                    {{-- Badge d'Écart de caisse dynamique --}}
                    <div id="boiteEcart" class="rounded-2xl p-4 text-center transition-all bg-slate-50 border border-slate-200">
                        <span class="text-[11px] font-semibold uppercase tracking-wider block text-slate-500">Écart de Caisse</span>
                        <div id="ecartMontant" class="num text-xl font-bold mt-1 text-slate-600">
                            0 FCFA
                        </div>
                        <p id="ecartMessage" class="text-xs mt-1 text-slate-500">Saisissez les coupures pour calculer l'écart.</p>
                    </div>

                    <button type="submit" class="btn-primary w-full h-12 text-sm font-bold shadow-md">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <rect width="18" height="18" x="3" y="3" rx="2" />
                            <path d="m9 12 2 2 4-4" />
                        </svg>
                        Confirmer la Clôture & Générer le Z
                    </button>
                </div>

            </div>

        </div>

    </form>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const inputs = document.querySelectorAll('.input-coupure');
        const soldeTheoriqueEl = document.getElementById('soldeTheorique');
        const totalPhysiqueEl = document.getElementById('totalPhysique');
        const boiteEcart = document.getElementById('boiteEcart');
        const ecartMontant = document.getElementById('ecartMontant');
        const ecartMessage = document.getElementById('ecartMessage');

        const soldeTheorique = parseFloat(soldeTheoriqueEl.textContent.trim()) || 0;
        const formatFcfa = (n) => new Intl.NumberFormat('fr-FR').format(n);

        // Affiche le solde formaté
        soldeTheoriqueEl.textContent = formatFcfa(soldeTheorique);

        function recalculerBilletage() {
            let total = 0;

            inputs.forEach(input => {
                const coupure = parseFloat(input.dataset.coupure) || 0;
                const qte = parseInt(input.value) || 0;
                const sousTotal = coupure * qte;
                total += sousTotal;

                const sousTotalEl = document.getElementById('sous-total-' + input.dataset.coupure);
                if (sousTotalEl) {
                    sousTotalEl.textContent = formatFcfa(sousTotal);
                }
            });

            totalPhysiqueEl.textContent = formatFcfa(total);

            const ecart = total - soldeTheorique;

            if (total === 0) {
                boiteEcart.className = 'rounded-2xl p-4 text-center transition-all bg-slate-50 border border-slate-200';
                ecartMontant.className = 'num text-xl font-bold mt-1 text-slate-600';
                ecartMontant.textContent = '0 FCFA';
                ecartMessage.textContent = 'Saisissez les coupures pour calculer l\'écart.';
            } else if (ecart === 0) {
                boiteEcart.className = 'rounded-2xl p-4 text-center transition-all bg-emerald-50 border border-emerald-300 text-emerald-800';
                ecartMontant.className = 'num text-xl font-bold mt-1 text-emerald-700';
                ecartMontant.textContent = '0 FCFA';
                ecartMessage.textContent = '✓ Caisse parfaitement équilibrée (Aucun écart)';
            } else if (ecart > 0) {
                boiteEcart.className = 'rounded-2xl p-4 text-center transition-all bg-sky-50 border border-sky-300 text-sky-800';
                ecartMontant.className = 'num text-xl font-bold mt-1 text-sky-700';
                ecartMontant.textContent = '+' + formatFcfa(ecart) + ' FCFA';
                ecartMessage.textContent = '▲ Excédent de caisse constaté';
            } else {
                boiteEcart.className = 'rounded-2xl p-4 text-center transition-all bg-rose-50 border border-rose-300 text-rose-800';
                ecartMontant.className = 'num text-xl font-bold mt-1 text-rose-700';
                ecartMontant.textContent = formatFcfa(ecart) + ' FCFA';
                ecartMessage.textContent = '▼ Déficit / Manquant de caisse constaté';
            }
        }

        inputs.forEach(input => {
            input.addEventListener('input', recalculerBilletage);
        });

        recalculerBilletage();
    });
</script>
@endpush
