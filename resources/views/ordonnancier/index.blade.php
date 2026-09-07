@extends('layout')

@section('titre', 'Registre de l\'Ordonnancier — GESTA PHARM')

@section('topbar')
    <div class="flex items-center gap-3">
        <div class="grid size-9 place-items-center rounded-xl bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-400">
            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/>
                <path d="M8 7h6M8 11h8"/>
            </svg>
        </div>
        <div>
            <h1 class="text-base font-bold text-slate-900 dark:text-white tracking-tight">Registre de l'Ordonnancier Réglementaire</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">Traçabilité légale des prescriptions, stupéfiants et substances vénéneuses (Listes I & II)</p>
        </div>
    </div>
    <div class="ml-auto flex items-center gap-2">
        <a href="{{ route('ordonnancier.imprimer', request()->query()) }}" target="_blank" class="btn-primary inline-flex items-center gap-2">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/>
            </svg>
            <span>Imprimer le Registre</span>
        </a>
    </div>
@endsection

@section('content')

    {{-- Cartes de synthèse --}}
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
        <div class="panel p-5 border-l-4 border-l-purple-500">
            <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1">Inscriptions totales</div>
            <div class="num text-2xl font-black text-purple-700 dark:text-purple-400">
                {{ $stats['total_inscriptions'] }} <span class="text-xs font-normal text-slate-400">délivrances</span>
            </div>
            <p class="mt-2 text-xs text-slate-400">Ordonnances enregistrées</p>
        </div>

        <div class="panel p-5 border-l-4 border-l-rose-500">
            <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1">Stupéfiants (Tab. B)</div>
            <div class="num text-2xl font-black text-rose-600 dark:text-rose-400">
                {{ $stats['total_stupefiants'] }}
            </div>
            <p class="mt-2 text-xs text-slate-400">Substances hautement surveillées</p>
        </div>

        <div class="panel p-5 border-l-4 border-l-amber-500">
            <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1">Liste I (Tab. A)</div>
            <div class="num text-2xl font-black text-amber-600 dark:text-amber-400">
                {{ $stats['total_liste_1'] }}
            </div>
            <p class="mt-2 text-xs text-slate-400">Prescription stricte non renouvelable</p>
        </div>

        <div class="panel p-5 border-l-4 border-l-blue-500">
            <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1">Liste II (Tab. C)</div>
            <div class="num text-2xl font-black text-blue-600 dark:text-blue-400">
                {{ $stats['total_liste_2'] }}
            </div>
            <p class="mt-2 text-xs text-slate-400">Substances dangereuses</p>
        </div>
    </div>

    {{-- Filtres de recherche --}}
    <div class="panel p-4 mb-5">
        <form method="get" action="{{ route('ordonnancier.index') }}" class="flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[220px]">
                <input type="text" name="recherche" value="{{ request('recherche') }}"
                    placeholder="Recherche prescripteur, patient, N° ordonnancier..."
                    class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-sm">
            </div>

            <div class="w-44">
                <select name="tableau" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-700 dark:text-slate-200">
                    <option value="">Tous les tableaux</option>
                    <option value="Stupéfiant" @selected(request('tableau') === 'Stupéfiant')>Stupéfiants</option>
                    <option value="Liste I" @selected(request('tableau') === 'Liste I')>Liste I</option>
                    <option value="Liste II" @selected(request('tableau') === 'Liste II')>Liste II</option>
                </select>
            </div>

            <div class="flex items-center gap-1.5">
                <input type="date" name="date_debut" value="{{ request('date_debut') }}"
                    class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-2.5 py-1.5 text-xs">
                <span class="text-xs text-slate-400">à</span>
                <input type="date" name="date_fin" value="{{ request('date_fin') }}"
                    class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-2.5 py-1.5 text-xs">
            </div>

            <button type="submit" class="btn-ghost text-xs font-bold inline-flex items-center gap-1.5">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                </svg>
                <span>Filtrer</span>
            </button>

            @if(request()->hasAny(['recherche', 'tableau', 'date_debut', 'date_fin']))
                <a href="{{ route('ordonnancier.index') }}" class="text-xs text-slate-500 hover:text-slate-700 underline">
                    Effacer
                </a>
            @endif
        </form>
    </div>

    {{-- Registre officiel des ordonnances --}}
    <div class="panel overflow-hidden">
        <div class="table-container">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-800/50 text-xs font-bold uppercase text-slate-500 dark:text-slate-400">
                        <th class="py-3 px-4">N° Ordonnancier</th>
                        <th class="py-3 px-4">Délivrance</th>
                        <th class="py-3 px-4">Médecin Prescripteur</th>
                        <th class="py-3 px-4">Patient Bénéficiaire</th>
                        <th class="py-3 px-4">Médicament & Tableau</th>
                        <th class="py-3 px-4">Lot Débité</th>
                        <th class="py-3 px-4 text-center">Qté</th>
                        <th class="py-3 px-4">Posologie & Pharmacien</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($lignes as $ligne)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900 dark:text-white">
                                <span class="rounded bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 px-2 py-0.5 text-xs">
                                    {{ $ligne->numero_ordonnancier }}
                                </span>
                                @if($ligne->vente_id)
                                    <div class="text-[10px] text-slate-400 font-mono mt-0.5">
                                        Réf TCK-{{ $ligne->vente_id }}
                                    </div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-xs text-slate-600 dark:text-slate-400">
                                <div>{{ $ligne->date_delivrance->format('d/m/Y') }}</div>
                                <div class="text-[11px] text-slate-400">{{ $ligne->date_delivrance->format('H:i') }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-800 dark:text-slate-200">{{ $ligne->nom_prescripteur }}</div>
                                @if($ligne->specialite_prescripteur)
                                    <div class="text-xs text-slate-400">{{ $ligne->specialite_prescripteur }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-slate-800 dark:text-slate-200">{{ $ligne->nom_patient }}</div>
                                @if($ligne->age_patient)
                                    <div class="text-xs text-slate-400">{{ $ligne->age_patient }} ans</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 dark:text-white">{{ $ligne->medicament->nom }}</div>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    @if($ligne->medicament->tableau && $ligne->medicament->tableau->value !== 'Non listé')
                                        <span class="inline-flex rounded-full px-1.5 py-0.2 text-[10px] font-bold {{ $ligne->medicament->tableau->badgeClasses() }}">
                                            {{ $ligne->medicament->tableau->value }}
                                        </span>
                                    @endif
                                    @if($ligne->medicament->dci)
                                        <span class="text-[11px] text-slate-500 italic">{{ $ligne->medicament->dci }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($ligne->lot)
                                    <span class="font-mono text-xs font-bold text-slate-700 dark:text-slate-300">
                                        {{ $ligne->lot->numero_lot }}
                                    </span>
                                    <div class="text-[10px] text-slate-400">
                                        Exp: {{ $ligne->lot->date_expiration->format('m/Y') }}
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 italic">Lot standard</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="rounded-full bg-slate-100 dark:bg-slate-800 px-2 py-0.5 text-xs font-bold text-slate-700 dark:text-slate-300">
                                    {{ $ligne->quantite_delivree }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-xs">
                                <div class="text-slate-700 dark:text-slate-300 font-medium">
                                    {{ $ligne->posologie ?? 'Non renseignée' }}
                                </div>
                                <div class="text-[10px] text-slate-400 mt-0.5">
                                    Délivré par {{ $ligne->pharmacien?->name ?? 'Officine' }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-12 text-slate-400">
                                Aucune inscription au registre de l'ordonnancier pour le moment.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($lignes->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $lignes->links() }}
            </div>
        @endif
    </div>

@endsection
