@extends('layout')

@section('titre', 'Nouveau Bordereau de Facturation — GESTA PHARM')

@section('topbar')
    <div class="flex items-center gap-3">
        <a href="{{ route('bordereaux.index') }}" class="btn-icon" aria-label="Retour aux bordereaux" title="Retour aux bordereaux">
            <svg class="size-4 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M15 18l-6-6 6-6" />
            </svg>
        </a>
        <div>
            <h1 class="text-base font-bold text-slate-900 dark:text-white tracking-tight">Générer un Bordereau Tiers Payant</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">Regroupement des factures et créances à transmettre à une assurance</p>
        </div>
    </div>
@endsection

@section('content')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Formulaire de sélection --}}
        <div class="panel p-6 lg:col-span-1 h-fit">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                <span class="size-6 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-400 text-xs font-bold grid place-items-center">1</span>
                <span>Critères de facturation</span>
            </h2>

            <form method="get" action="{{ route('bordereaux.create') }}" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Organisme / Mutuelle *</label>
                    <select name="assurance_id" required onchange="this.form.submit()"
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-white">
                        <option value="">-- Sélectionner un organisme --</option>
                        @foreach($assurances as $assur)
                            <option value="{{ $assur->id }}" @selected($assuranceId == $assur->id)>
                                {{ $assur->nom }} ({{ $assur->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Date début</label>
                        <input type="date" name="date_debut" value="{{ $dateDebut }}" onchange="this.form.submit()"
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-1.5 text-xs text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Date fin</label>
                        <input type="date" name="date_fin" value="{{ $dateFin }}" onchange="this.form.submit()"
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-1.5 text-xs text-slate-900 dark:text-white">
                    </div>
                </div>

                <button type="submit" class="btn-ghost w-full text-xs font-bold justify-center">
                    Actualiser la liste
                </button>
            </form>

            @if($ventesEligibles->isNotEmpty())
                <div class="mt-6 pt-5 border-t border-slate-100 dark:border-slate-800 space-y-3">
                    <div class="rounded-xl bg-slate-50 dark:bg-slate-800/60 p-3.5 space-y-2">
                        <div class="flex justify-between text-xs text-slate-500">
                            <span>Dossiers trouvés :</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $ventesEligibles->count() }}</span>
                        </div>
                        <div class="flex justify-between text-xs text-slate-500">
                            <span>Montant brut total :</span>
                            <span class="num font-semibold text-slate-800 dark:text-slate-200">{{ number_format($ventesEligibles->sum('total'), 0, ',', ' ') }} FCFA</span>
                        </div>
                        <div class="flex justify-between text-xs text-slate-500">
                            <span>Ticket modérateur encaissé :</span>
                            <span class="num font-semibold text-slate-800 dark:text-slate-200">{{ number_format($ventesEligibles->sum('part_patient'), 0, ',', ' ') }} FCFA</span>
                        </div>
                        <div class="flex justify-between text-sm font-black border-t border-slate-200 dark:border-slate-700 pt-2 text-emerald-700 dark:text-emerald-400">
                            <span>Part Assurance réclamée :</span>
                            <span class="num text-base">{{ number_format($ventesEligibles->sum('part_assurance'), 0, ',', ' ') }} FCFA</span>
                        </div>
                    </div>

                    <form method="post" action="{{ route('bordereaux.store') }}">
                        @csrf
                        <input type="hidden" name="assurance_id" value="{{ $assuranceId }}">
                        <input type="hidden" name="periode_debut" value="{{ $dateDebut }}">
                        <input type="hidden" name="periode_fin" value="{{ $dateFin }}">

                        <div class="mb-3">
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Notes / Mentions pour le bordereau</label>
                            <textarea name="notes" rows="2" placeholder="ex: Facturation mensuelle CANAM - Secteur Kati"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-xs"></textarea>
                        </div>

                        <button type="submit" class="btn-primary w-full justify-center h-11 text-sm font-bold shadow-lg shadow-emerald-700/20">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 6 9 17l-5-5"/>
                            </svg>
                            <span>Générer le bordereau officiel</span>
                        </button>
                    </form>
                </div>
            @endif
        </div>

        {{-- Aperçu des dossiers éligibles --}}
        <div class="panel p-6 lg:col-span-2">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 mb-4">
                <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="size-6 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-400 text-xs font-bold grid place-items-center">2</span>
                    <span>Dossiers de prise en charge éligibles</span>
                </h2>
                @if($ventesEligibles->isNotEmpty())
                    <span class="rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 px-2.5 py-0.5 text-xs font-bold">
                        {{ $ventesEligibles->count() }} factures prêtes
                    </span>
                @endif
            </div>

            @if(!$assuranceId)
                <div class="py-16 text-center text-slate-400">
                    <svg class="size-12 mx-auto mb-3 opacity-40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect width="18" height="18" x="3" y="3" rx="2"/><path d="M7 12h10"/><path d="M12 7v10"/>
                    </svg>
                    <p class="font-medium text-slate-600 dark:text-slate-300">Veuillez sélectionner un organisme d'assurance</p>
                    <p class="text-xs text-slate-400 mt-1">Le système affichera toutes les ventes comptoir assurées en attente de facturation</p>
                </div>
            @elseif($ventesEligibles->isEmpty())
                <div class="py-16 text-center text-slate-400">
                    <svg class="size-12 mx-auto mb-3 text-amber-500 opacity-60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/>
                    </svg>
                    <p class="font-bold text-slate-700 dark:text-slate-300">Aucun dossier en attente trouvé</p>
                    <p class="text-xs text-slate-500 mt-1">Toutes les prises en charge de cette période ont déjà été intégrées dans un bordereau ou aucune vente assurée n'a été enregistrée.</p>
                </div>
            @else
                <div class="table-container max-h-[500px] overflow-y-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40 font-bold uppercase text-slate-500">
                                <th class="py-2.5 px-3">Ticket / Date</th>
                                <th class="py-2.5 px-3">Bénéficiaire / N° Assuré</th>
                                <th class="py-2.5 px-3">Produits délivrés</th>
                                <th class="py-2.5 px-3 text-right">Total Brut</th>
                                <th class="py-2.5 px-3 text-center">Taux</th>
                                <th class="py-2.5 px-3 text-right font-black text-emerald-700 dark:text-emerald-400">Part Assurance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($ventesEligibles as $v)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
                                    <td class="py-2.5 px-3">
                                        <div class="font-mono font-bold text-slate-900 dark:text-white">TCK-{{ $v->id }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $v->date_vente->format('d/m/Y H:i') }}</div>
                                    </td>
                                    <td class="py-2.5 px-3">
                                        <div class="font-bold text-slate-800 dark:text-slate-200">{{ $v->nom_assure ?? 'N/A' }}</div>
                                        <div class="text-[11px] font-mono text-slate-500">Matr: {{ $v->matricule_assure }}</div>
                                    </td>
                                    <td class="py-2.5 px-3 text-slate-600 dark:text-slate-400">
                                        {{ $v->medicaments->pluck('nom')->take(2)->join(', ') }}
                                        @if($v->medicaments->count() > 2)
                                            <span class="text-slate-400 font-semibold">+{{ $v->medicaments->count() - 2 }}</span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-3 text-right num text-slate-700 dark:text-slate-300">
                                        {{ number_format($v->total, 0, ',', ' ') }}
                                    </td>
                                    <td class="py-2.5 px-3 text-center">
                                        <span class="rounded bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-400 px-1.5 py-0.5 font-bold text-[10px]">
                                            {{ (int)$v->taux_couverture }}%
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 text-right num font-black text-emerald-600 dark:text-emerald-400">
                                        {{ number_format($v->part_assurance, 0, ',', ' ') }} FCFA
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
