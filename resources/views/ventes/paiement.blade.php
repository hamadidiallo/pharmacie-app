@extends('layout')

@section('titre', 'Paiement — GESTA PHARM')

@section('topbar')
    <div class="flex items-center gap-3">
        <a href="{{ route('ventes.create') }}" class="btn-icon" aria-label="Retour au panier" title="Retour au panier">
            <svg class="size-4 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M15 18l-6-6 6-6" />
            </svg>
        </a>
        <div>
            <h1 class="text-base font-bold text-slate-900 tracking-tight">Règlement & Encaissement</h1>
            <p class="text-xs text-slate-500">
                Ticket en cours : {{ $panier->nombreArticles() }} {{ $panier->nombreArticles() > 1 ? 'articles' : 'article' }}
            </p>
        </div>
    </div>
@endsection

@section('content')

    @php
        $icones = [
            'especes' =>
                '<rect x="2" y="6" width="20" height="12" rx="2.5"/><circle cx="12" cy="12" r="2.5"/><path d="M6 12h.01M18 12h.01"/>',
            'mobile_money' => '<rect x="6" y="2" width="12" height="20" rx="2.5"/><path d="M11 18h2"/>',
            'carte' => '<rect x="2" y="5" width="20" height="14" rx="2.5"/><path d="M2 10h20"/>',
        ];
        $total = $panier->total();
        $exigeOrdonnance = $panier->contientMedicamentSousOrdonnance();
    @endphp

    <form method="post" action="{{ route('ventes.store') }}"
        class="grid gap-5 lg:grid-cols-[1fr_1.15fr]" data-total="{{ $total }}">

        @csrf

        {{-- Colonne Gauche : Récapitulatif Articles & Total Dynamique --}}
        <div class="panel flex flex-col p-6">

            <div class="mb-4 flex items-center justify-between border-b border-slate-100 pb-3">
                <h2 class="text-sm font-bold text-slate-900">Articles commandés</h2>
                <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600">
                    {{ $panier->nombreArticles() }}
                </span>
            </div>

            <div class="space-y-3 overflow-y-auto max-h-[360px] pr-1">
                @foreach ($panier->lignes() as $ligne)
                    <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/50 p-3">
                        <div class="min-w-0 flex-1 pr-3">
                            <div class="flex items-center gap-1.5 truncate text-sm font-bold text-slate-800">
                                <span>{{ $ligne->medicament->nom }}</span>
                                @if($ligne->medicament->tableau && $ligne->medicament->tableau->value !== 'Non listé')
                                    <span class="inline-flex rounded-full px-1.5 py-0.2 text-[9px] font-bold {{ $ligne->medicament->tableau->badgeClasses() }}">
                                        {{ $ligne->medicament->tableau->value }}
                                    </span>
                                @endif
                            </div>
                            <div class="num text-xs text-slate-500">
                                {{ $ligne->quantite }} × {{ number_format($ligne->prix(), 0, ',', ' ') }} FCFA
                            </div>
                        </div>
                        <div class="num text-sm font-black text-slate-900">
                            {{ number_format($ligne->sousTotal(), 0, ',', ' ') }} <span class="text-[11px] font-normal text-slate-400">FCFA</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6 rounded-2xl bg-gradient-to-br from-slate-900 via-[#0d1e19] to-emerald-950 p-5 text-white shadow-lg lg:mt-auto">
                <div class="mb-2 flex justify-between text-xs text-emerald-300/80">
                    <span>Total brut ordonnance</span>
                    <span class="num font-semibold text-white">{{ number_format($total, 0, ',', ' ') }} FCFA</span>
                </div>

                <div id="ligneAssuranceRecap" class="hidden mb-2 justify-between text-xs text-amber-300">
                    <span id="labelTauxAssurance">Prise en charge Assurance (0%) :</span>
                    <span id="montantAssuranceRecap" class="num font-bold text-amber-300">- 0 FCFA</span>
                </div>

                <div class="flex items-baseline justify-between border-t border-white/10 pt-3">
                    <div>
                        <span class="text-sm font-semibold text-slate-200 block">Net à payer par le client</span>
                        <span id="labelTicketModérateur" class="text-[10px] text-emerald-300/70">Paiement direct comptoir</span>
                    </div>
                    <span class="num text-3xl font-black text-emerald-400 tracking-tight">
                        <span id="totalNetPatient">{{ number_format($total, 0, ',', ' ') }}</span>
                        <span class="text-sm font-semibold text-emerald-200">FCFA</span>
                    </span>
                </div>
            </div>

        </div>

        {{-- Colonne Droite : Tiers Payant, Ordonnancier & Encaissement --}}
        <div class="panel flex flex-col p-6 space-y-6">

            {{-- 1. BLOC TIERS PAYANT / ASSURANCE --}}
            <div class="rounded-2xl border border-slate-200 p-4 bg-slate-50/50">
                <div class="flex items-center justify-between cursor-pointer" onclick="basculerAssurance()">
                    <div class="flex items-center gap-3">
                        <div class="size-8 rounded-xl bg-teal-100 text-teal-800 grid place-items-center">
                            <svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect width="18" height="18" x="3" y="3" rx="2"/><path d="M7 12h10"/><path d="M12 7v10"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-xs font-bold uppercase tracking-wider text-slate-700">Tiers Payant (Assurance / Mutuelle)</div>
                            <div class="text-[11px] text-slate-500">Prise en charge CANAM, INPS, Mutuelles privées</div>
                        </div>
                    </div>
                    <input type="checkbox" name="avec_assurance" id="checkAssurance" value="1"
                        @checked(old('avec_assurance'))
                        class="rounded size-5 text-emerald-600 focus:ring-emerald-500 cursor-pointer"
                        onchange="basculerAssurance(event)">
                </div>

                <div id="champsAssurance" class="{{ old('avec_assurance') ? '' : 'hidden' }} mt-4 pt-4 border-t border-slate-200 space-y-3">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Organisme Payeur *</label>
                        <select name="assurance_id" id="selectAssurance" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm">
                            <option value="" data-taux="0">-- Sélectionner l'assurance --</option>
                            @foreach ($assurances as $assur)
                                <option value="{{ $assur->id }}" data-taux="{{ $assur->taux_couverture_defaut }}"
                                    @selected(old('assurance_id') == $assur->id)>
                                    {{ $assur->nom }} ({{ $assur->code }} - Taux défaut {{ (int)$assur->taux_couverture_defaut }}%)
                                </option>
                            @endforeach
                        </select>
                        @error('assurance_id') <p class="field-error mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">N° Matricule / Assuré *</label>
                            <input type="text" name="matricule_assure" id="champMatricule" value="{{ old('matricule_assure') }}"
                                placeholder="ex: 123456789-AMO"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-mono">
                            @error('matricule_assure') <p class="field-error mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Nom de l'assuré</label>
                            <input type="text" name="nom_assure" id="champNomAssure" value="{{ old('nom_assure') }}"
                                placeholder="Nom et prénom sur la carte"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3 items-center">
                        <div class="col-span-1">
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Taux Prise en Charge (%)</label>
                            <div class="relative flex items-center">
                                <input type="number" name="taux_couverture" id="champTauxCouverture" min="0" max="100" step="1"
                                    value="{{ old('taux_couverture', 70) }}"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm pr-7 font-bold">
                                <span class="absolute right-2.5 text-xs text-slate-400 font-bold">%</span>
                            </div>
                        </div>
                        <div class="col-span-2 rounded-xl bg-teal-50 p-2.5 border border-teal-100 text-xs">
                            <div class="flex justify-between text-teal-800 font-medium">
                                <span>Part Mutuelle :</span>
                                <span id="apercuPartAssurance" class="num font-bold">0 FCFA</span>
                            </div>
                            <div class="flex justify-between text-teal-900 font-bold border-t border-teal-200/60 mt-1 pt-1">
                                <span>Ticket modérateur :</span>
                                <span id="apercuPartPatient" class="num font-black">0 FCFA</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. BLOC ORDONNANCIER RÉGLEMENTAIRE --}}
            <div @class([
                'rounded-2xl border p-4',
                'border-purple-300 bg-purple-50/40 ring-2 ring-purple-500/20' => $exigeOrdonnance,
                'border-slate-200 bg-slate-50/50' => !$exigeOrdonnance,
            ])>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div @class([
                            'size-8 rounded-xl grid place-items-center',
                            'bg-purple-600 text-white' => $exigeOrdonnance,
                            'bg-purple-100 text-purple-800' => !$exigeOrdonnance,
                        ])>
                            <svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/><path d="M8 7h6M8 11h8"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-xs font-bold uppercase tracking-wider text-slate-800 flex items-center gap-2">
                                <span>Ordonnancier Réglementaire</span>
                                @if($exigeOrdonnance)
                                    <span class="rounded-full bg-purple-600 text-white px-2 py-0.2 text-[9px] font-bold">Obligatoire</span>
                                @endif
                            </div>
                            <div class="text-[11px] text-slate-500">
                                {{ $exigeOrdonnance ? 'Ce panier contient des médicaments soumis à prescription (Stupéfiants / Listes)' : 'Enregistrement de la prescription médicale (Optionnel)' }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-200/80 space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">
                                Médecin Prescripteur {{ $exigeOrdonnance ? '*' : '' }}
                            </label>
                            <input type="text" name="nom_prescripteur" value="{{ old('nom_prescripteur') }}"
                                @required($exigeOrdonnance)
                                placeholder="ex: Dr Moussa Traoré"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm">
                            @error('nom_prescripteur') <p class="field-error mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Spécialité médicale</label>
                            <input type="text" name="specialite_prescripteur" value="{{ old('specialite_prescripteur') }}"
                                placeholder="Généraliste, Pédiatre, Cardiologue..."
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div class="col-span-2">
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">
                                Patient Bénéficiaire {{ $exigeOrdonnance ? '*' : '' }}
                            </label>
                            <input type="text" name="nom_patient" value="{{ old('nom_patient') }}"
                                @required($exigeOrdonnance)
                                placeholder="Nom et prénom du patient"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm">
                            @error('nom_patient') <p class="field-error mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Âge</label>
                            <input type="number" name="age_patient" min="0" max="130" value="{{ old('age_patient') }}"
                                placeholder="ans"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Date prescription</label>
                            <input type="date" name="date_prescription" value="{{ old('date_prescription', now()->toDateString()) }}"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Posologie / Mentions</label>
                            <input type="text" name="posologie" value="{{ old('posologie') }}"
                                placeholder="ex: 1 cp matin et soir pdt 5 jours"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm">
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. ENCAISSEMENT & RÈGLEMENT --}}
            <div>
                <fieldset class="mb-4">
                    <legend class="mb-3 block text-xs font-bold uppercase tracking-wider text-slate-500">
                        Mode de règlement
                    </legend>

                    @error('mode_paiement')
                        <p class="field-error mb-3">{{ $message }}</p>
                    @enderror

                    <div class="grid grid-cols-3 gap-3">
                        @foreach ($modes as $cle => $libelle)
                            <label class="group relative flex cursor-pointer flex-col items-center gap-2.5 rounded-2xl border border-slate-200 bg-white p-3 transition-all hover:border-emerald-300 hover:shadow-xs has-checked:border-emerald-600 has-checked:bg-emerald-50/50 has-checked:ring-2 has-checked:ring-emerald-600/20">
                                <input type="radio" name="mode_paiement" value="{{ $cle }}" class="sr-only"
                                    data-mode @checked(old('mode_paiement', 'especes') === $cle)>
                                <div class="grid size-9 place-items-center rounded-xl bg-slate-100 text-slate-600 group-has-checked:bg-emerald-600 group-has-checked:text-white transition-colors">
                                    <svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        {!! $icones[$cle] !!}
                                    </svg>
                                </div>
                                <span class="text-center text-xs font-bold text-slate-700 group-has-checked:text-emerald-900">{{ $libelle }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                {{-- Espèces : montant reçu et monnaie --}}
                <div data-bloc-especes class="space-y-4">

                    <div>
                        <label for="montant_recu" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-500">
                            Montant reçu du client (Espèces)
                        </label>

                        <div class="relative flex h-14 items-center rounded-2xl border-2 border-emerald-600 bg-emerald-50/30 px-4 focus-within:ring-4 focus-within:ring-emerald-500/20 transition-all">
                            <input type="number" id="montant_recu" name="montant_recu" min="0" step="1"
                                value="{{ old('montant_recu', (int) $total) }}"
                                class="num w-full border-0 bg-transparent text-2xl font-black text-slate-900 focus:outline-none focus:ring-0">
                            <span class="ml-2 shrink-0 rounded-lg bg-emerald-600/10 px-2 py-1 text-xs font-bold text-emerald-800">
                                FCFA
                            </span>
                        </div>

                        @error('montant_recu')
                            <p class="field-error mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="zoneSuggestions">
                        <span class="mb-2 block text-xs font-semibold text-slate-500">Suggestions rapides :</span>
                        <div id="listeSuggestions" class="grid grid-cols-4 gap-2">
                            {{-- Généré dynamiquement en JS selon le net patient --}}
                        </div>
                    </div>

                    <div class="flex items-center justify-between rounded-2xl border border-emerald-100 bg-emerald-50/60 p-4">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-emerald-800 block">Monnaie à rendre</span>
                            <span class="text-[11px] text-emerald-600">Calcul en temps réel</span>
                        </div>
                        <span id="monnaie" class="num text-2xl font-black text-emerald-700">0 FCFA</span>
                    </div>

                </div>
            </div>

            <button type="submit" class="btn-primary mt-4 h-13 w-full rounded-2xl text-base font-bold shadow-lg shadow-emerald-700/20">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 6 9 17l-5-5" />
                </svg>
                Encaisser & Imprimer le ticket
            </button>

        </div>

    </form>

@endsection

@push('scripts')
    <script>
        const formulaire = document.querySelector('form[data-total]');
        const totalBrut = Number(formulaire.dataset.total);
        const champMontant = document.getElementById('montant_recu');
        const affichageMonnaie = document.getElementById('monnaie');
        const blocEspeces = document.querySelector('[data-bloc-especes]');
        const boutonValider = formulaire.querySelector('button[type="submit"]');

        const checkAssurance = document.getElementById('checkAssurance');
        const champsAssurance = document.getElementById('champsAssurance');
        const selectAssurance = document.getElementById('selectAssurance');
        const champTaux = document.getElementById('champTauxCouverture');
        const apercuPartAssurance = document.getElementById('apercuPartAssurance');
        const apercuPartPatient = document.getElementById('apercuPartPatient');
        const ligneAssuranceRecap = document.getElementById('ligneAssuranceRecap');
        const montantAssuranceRecap = document.getElementById('montantAssuranceRecap');
        const labelTauxAssurance = document.getElementById('labelTauxAssurance');
        const totalNetPatientEl = document.getElementById('totalNetPatient');
        const listeSuggestions = document.getElementById('listeSuggestions');

        const formatFcfa = (montant) => new Intl.NumberFormat('fr-FR').format(Math.round(montant));

        function calculerNetPatient() {
            if (!checkAssurance.checked || !selectAssurance.value) {
                return { brut: totalBrut, assurance: 0, patient: totalBrut, taux: 0 };
            }

            const taux = Number(champTaux.value || 0);
            const partAssurance = Math.round(totalBrut * (taux / 100));
            const partPatient = totalBrut - partAssurance;

            return { brut: totalBrut, assurance: partAssurance, patient: partPatient, taux: taux };
        }

        function rafraichirCalculs() {
            const calcul = calculerNetPatient();

            if (checkAssurance.checked && selectAssurance.value) {
                ligneAssuranceRecap.classList.remove('hidden');
                ligneAssuranceRecap.classList.add('flex');
                labelTauxAssurance.textContent = `Prise en charge Assurance (${calcul.taux}%) :`;
                montantAssuranceRecap.textContent = `- ${formatFcfa(calcul.assurance)} FCFA`;
                apercuPartAssurance.textContent = `${formatFcfa(calcul.assurance)} FCFA`;
                apercuPartPatient.textContent = `${formatFcfa(calcul.patient)} FCFA`;
            } else {
                ligneAssuranceRecap.classList.add('hidden');
                ligneAssuranceRecap.classList.remove('flex');
                apercuPartAssurance.textContent = '0 FCFA';
                apercuPartPatient.textContent = `${formatFcfa(totalBrut)} FCFA`;
            }

            totalNetPatientEl.textContent = formatFcfa(calcul.patient);

            // Mettre à jour les suggestions rapides de billets pour le montant dû
            genererSuggestions(calcul.patient);
            rafraichirMonnaie();
        }

        function genererSuggestions(net) {
            listeSuggestions.innerHTML = '';
            if (net <= 0) return;

            const valeurs = [
                net,
                Math.ceil(net / 500) * 500,
                Math.ceil(net / 1000) * 1000,
                Math.ceil(net / 5000) * 5000,
                Math.ceil(net / 10000) * 10000
            ];

            const uniques = [...new Set(valeurs)].sort((a, b) => a - b).slice(0, 4);

            uniques.forEach(montant => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'num rounded-xl border border-slate-200 bg-white py-2 text-center text-xs font-bold text-slate-700 shadow-xs transition-all hover:border-emerald-400 hover:bg-emerald-50 hover:text-emerald-700 active:scale-95';
                btn.textContent = formatFcfa(montant);
                btn.addEventListener('click', () => {
                    champMontant.value = montant;
                    rafraichirMonnaie();
                });
                listeSuggestions.appendChild(btn);
            });
        }

        function basculerAssurance(event) {
            if (event && event.target === checkAssurance) {
                // cliqué directement sur le checkbox
            } else if (!event) {
                checkAssurance.checked = !checkAssurance.checked;
            }

            if (checkAssurance.checked) {
                champsAssurance.classList.remove('hidden');
            } else {
                champsAssurance.classList.add('hidden');
            }

            rafraichirCalculs();
        }

        selectAssurance.addEventListener('change', () => {
            const opt = selectAssurance.options[selectAssurance.selectedIndex];
            const tauxDefaut = opt.dataset.taux || 70;
            champTaux.value = Math.round(Number(tauxDefaut));
            rafraichirCalculs();
        });

        champTaux.addEventListener('input', rafraichirCalculs);

        function rafraichirMonnaie() {
            const especes = formulaire.querySelector('[data-mode]:checked').value === 'especes';
            blocEspeces.hidden = !especes;

            const calcul = calculerNetPatient();
            const duPatient = calcul.patient;

            if (!especes) {
                boutonValider.disabled = false;
                return;
            }

            const recu = Number(champMontant.value || 0);
            const monnaie = recu - duPatient;

            affichageMonnaie.textContent = formatFcfa(Math.max(0, monnaie)) + ' FCFA';
            affichageMonnaie.classList.toggle('text-rose-600', monnaie < 0);
            affichageMonnaie.classList.toggle('text-emerald-700', monnaie >= 0);

            boutonValider.disabled = monnaie < 0;
        }

        formulaire.querySelectorAll('[data-mode]').forEach(
            radio => radio.addEventListener('change', rafraichirMonnaie)
        );

        champMontant.addEventListener('input', rafraichirMonnaie);

        // Initialisation au chargement
        rafraichirCalculs();
        champMontant.value = Math.round(calculerNetPatient().patient);
        rafraichirMonnaie();
    </script>
@endpush
