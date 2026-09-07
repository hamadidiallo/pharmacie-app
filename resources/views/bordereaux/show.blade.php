@extends('layout')

@section('titre', 'Bordereau ' . $bordereau->reference . ' — GESTA PHARM')

@section('topbar')
    <div class="flex items-center gap-3">
        <a href="{{ route('bordereaux.index') }}" class="btn-icon" aria-label="Retour aux bordereaux" title="Retour aux bordereaux">
            <svg class="size-4 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M15 18l-6-6 6-6" />
            </svg>
        </a>
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-base font-bold text-slate-900 dark:text-white tracking-tight">Bordereau {{ $bordereau->reference }}</h1>
                <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[11px] font-bold {{ $bordereau->statut->badgeClasses() }}">
                    {{ $bordereau->statut->label() }}
                </span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                Organisme : <strong class="text-slate-700 dark:text-slate-200">{{ $bordereau->assurance->nom }}</strong> •
                Période du {{ $bordereau->periode_debut->format('d/m/Y') }} au {{ $bordereau->periode_fin->format('d/m/Y') }}
            </p>
        </div>
    </div>
    <div class="ml-auto flex items-center gap-2">
        <a href="{{ route('bordereaux.imprimer', $bordereau) }}" target="_blank" class="btn-ghost inline-flex items-center gap-2">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/>
            </svg>
            <span>Imprimer A4</span>
        </a>

        @if($bordereau->statut->value === 'Brouillon')
            <form action="{{ route('bordereaux.transmettre', $bordereau) }}" method="post" onsubmit="return confirm('Confirmer la transmission du bordereau à l\'organisme ?')">
                @csrf
                <button type="submit" class="btn-primary inline-flex items-center gap-2">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                    </svg>
                    <span>Marquer Transmis</span>
                </button>
            </form>
        @elseif($bordereau->statut->value === 'Transmis')
            <button type="button" onclick="document.getElementById('modalReglement').classList.remove('hidden')" class="btn-primary inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 6 9 17l-5-5"/>
                </svg>
                <span>Enregistrer le Règlement</span>
            </button>
        @endif
    </div>
@endsection

@section('content')

    {{-- Synthèse --}}
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
        <div class="panel p-5 bg-gradient-to-br from-slate-900 to-emerald-950 text-white shadow-md">
            <div class="text-xs text-emerald-300 font-semibold mb-1">Montant Total Réclamé</div>
            <div class="num text-2xl font-black text-emerald-400">
                {{ number_format($bordereau->montant_total, 0, ',', ' ') }} <span class="text-xs font-normal text-emerald-200">FCFA</span>
            </div>
            <p class="mt-2 text-xs text-slate-300">À la charge de {{ $bordereau->assurance->code }}</p>
        </div>

        <div class="panel p-5">
            <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1">Nombre de Dossiers</div>
            <div class="num text-2xl font-black text-slate-900 dark:text-white">
                {{ $bordereau->nombre_dossiers }} <span class="text-xs font-normal text-slate-400">ordonnances / tickets</span>
            </div>
            <p class="mt-2 text-xs text-slate-400">Couverture moy. : {{ (int)$bordereau->assurance->taux_couverture_defaut }}%</p>
        </div>

        <div class="panel p-5">
            <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1">Date d'émission</div>
            <div class="text-base font-bold text-slate-900 dark:text-white">
                {{ $bordereau->created_at->format('d/m/Y à H:i') }}
            </div>
            <p class="mt-2 text-xs text-slate-400">Par {{ $bordereau->user?->name ?? 'Officine' }}</p>
        </div>

        <div class="panel p-5">
            <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1">Statut Règlement</div>
            @if($bordereau->date_reglement)
                <div class="text-base font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg>
                    <span>Réglé le {{ $bordereau->date_reglement->format('d/m/Y') }}</span>
                </div>
                <p class="mt-2 text-xs text-slate-400">Réf: {{ $bordereau->reference_reglement ?? 'N/A' }} ({{ $bordereau->mode_reglement }})</p>
            @elseif($bordereau->date_transmission)
                <div class="text-base font-bold text-amber-600 dark:text-amber-400">
                    Transmis le {{ $bordereau->date_transmission->format('d/m/Y') }}
                </div>
                <p class="mt-2 text-xs text-slate-400">En attente de paiement</p>
            @else
                <div class="text-base font-bold text-slate-600 dark:text-slate-400">
                    Brouillon non transmis
                </div>
                <p class="mt-2 text-xs text-slate-400">Contrôle avant dépôt</p>
            @endif
        </div>
    </div>

    {{-- Détail des dossiers rattachés --}}
    <div class="panel overflow-hidden">
        <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white">Liste des prises en charge incluses</h2>
            <span class="text-xs text-slate-500">{{ $bordereau->ventes->count() }} dossiers enregistrés</span>
        </div>

        <div class="table-container">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-800/50 text-xs font-bold uppercase text-slate-500 dark:text-slate-400">
                        <th class="py-3 px-4">Ticket N°</th>
                        <th class="py-3 px-4">Date de délivrance</th>
                        <th class="py-3 px-4">Bénéficiaire / Matricule</th>
                        <th class="py-3 px-4">Médicaments délivrés</th>
                        <th class="py-3 px-4 text-right">Montant Brut</th>
                        <th class="py-3 px-4 text-right">Ticket modérateur (Patient)</th>
                        <th class="py-3 px-4 text-right font-black text-emerald-700 dark:text-emerald-400">Part Mutuelle (Réclamée)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach ($bordereau->ventes as $v)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
                            <td class="py-3 px-4 font-mono font-bold text-slate-900 dark:text-white">
                                <a href="{{ route('ventes.show', $v) }}" target="_blank" class="text-emerald-600 dark:text-emerald-400 hover:underline">
                                    TCK-{{ $v->id }}
                                </a>
                            </td>
                            <td class="py-3 px-4 text-xs text-slate-600 dark:text-slate-400">
                                {{ $v->date_vente->format('d/m/Y H:i') }}
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-800 dark:text-slate-200">{{ $v->nom_assure ?? 'Patient assuré' }}</div>
                                <div class="font-mono text-xs text-slate-500">N° {{ $v->matricule_assure }}</div>
                            </td>
                            <td class="py-3 px-4 text-xs text-slate-600 dark:text-slate-400">
                                @foreach($v->medicaments as $med)
                                    <span class="inline-block bg-slate-100 dark:bg-slate-800 rounded px-1.5 py-0.5 mr-1 mb-1">
                                        {{ $med->nom }} (x{{ $med->pivot->quantite }})
                                    </span>
                                @endforeach
                            </td>
                            <td class="py-3 px-4 text-right num font-semibold text-slate-700 dark:text-slate-300">
                                {{ number_format($v->total, 0, ',', ' ') }} FCFA
                            </td>
                            <td class="py-3 px-4 text-right num text-slate-500">
                                {{ number_format($v->part_patient, 0, ',', ' ') }} FCFA
                            </td>
                            <td class="py-3 px-4 text-right num font-black text-emerald-600 dark:text-emerald-400">
                                {{ number_format($v->part_assurance, 0, ',', ' ') }} FCFA
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 font-black">
                        <td colspan="4" class="py-3 px-4 text-right uppercase text-xs">Total Général :</td>
                        <td class="py-3 px-4 text-right num">{{ number_format($bordereau->ventes->sum('total'), 0, ',', ' ') }} FCFA</td>
                        <td class="py-3 px-4 text-right num text-slate-500">{{ number_format($bordereau->ventes->sum('part_patient'), 0, ',', ' ') }} FCFA</td>
                        <td class="py-3 px-4 text-right num text-base text-emerald-600 dark:text-emerald-400">{{ number_format($bordereau->montant_total, 0, ',', ' ') }} FCFA</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- MODALE DE RÈGLEMENT --}}
    <div id="modalReglement" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs overflow-y-auto">
        <div class="min-h-full flex items-center justify-center p-4">
            <div class="panel max-w-md w-full p-6 relative shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 mb-5">
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Enregistrer le Règlement</h2>
                    <button type="button" onclick="document.getElementById('modalReglement').classList.add('hidden')" class="btn-icon text-slate-400">✕</button>
                </div>

                <form method="post" action="{{ route('bordereaux.regler', $bordereau) }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Mode de règlement *</label>
                        <select name="mode_reglement" required class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-sm">
                            <option value="Virement bancaire">Virement bancaire</option>
                            <option value="Chèque">Chèque</option>
                            <option value="Mobile Money">Mobile Money (Compte Pro)</option>
                            <option value="Espèces">Espèces</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Référence virement / N° Chèque</label>
                        <input type="text" name="reference_reglement" placeholder="ex: VIR-BDM-98342 ou CHQ-001248"
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-sm font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Notes / Observations</label>
                        <textarea name="notes" rows="2" placeholder="Date de valeur, banque émettrice..."
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-sm"></textarea>
                    </div>

                    <div class="rounded-xl bg-emerald-50 dark:bg-emerald-950/40 p-3 text-xs text-emerald-800 dark:text-emerald-300">
                        Cette action solde définitivement les <strong>{{ $bordereau->nombre_dossiers }} dossiers</strong> pour un montant de <strong>{{ number_format($bordereau->montant_total, 0, ',', ' ') }} FCFA</strong>.
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" onclick="document.getElementById('modalReglement').classList.add('hidden')" class="btn-ghost">Annuler</button>
                        <button type="submit" class="btn-primary">Valider l'encaissement</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
