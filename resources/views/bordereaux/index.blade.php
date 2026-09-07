@extends('layout')

@section('titre', 'Bordereaux de Facturation Tiers Payant — GESTA PHARM')

@section('topbar')
    <div class="flex items-center gap-3">
        <div class="grid size-9 place-items-center rounded-xl bg-teal-50 text-teal-700 dark:bg-teal-950/60 dark:text-teal-400">
            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>
            </svg>
        </div>
        <div>
            <h1 class="text-base font-bold text-slate-900 dark:text-white tracking-tight">Bordereaux de Facturation Tiers Payant</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">Suivi des états récapitulatifs, dépôts et encaissements des mutuelles</p>
        </div>
    </div>
    <div class="ml-auto flex items-center gap-2">
        <a href="{{ route('bordereaux.create') }}" class="btn-primary inline-flex items-center gap-2">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 5v14M5 12h14"/>
            </svg>
            <span>Nouveau Bordereau</span>
        </a>
    </div>
@endsection

@section('content')

    {{-- Cartes de synthèse --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="panel p-5 border-l-4 border-l-amber-500">
            <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1">Transmis / En cours de règlement</div>
            <div class="num text-2xl font-black text-amber-600 dark:text-amber-400">
                {{ number_format($stats['total_transmis'], 0, ',', ' ') }} <span class="text-xs font-normal text-slate-500">FCFA</span>
            </div>
            <p class="mt-2 text-xs text-slate-400">Déposés auprès des assurances</p>
        </div>

        <div class="panel p-5 border-l-4 border-l-emerald-500">
            <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1">Total Réglé & Encaissé</div>
            <div class="num text-2xl font-black text-emerald-600 dark:text-emerald-400">
                {{ number_format($stats['total_regle'], 0, ',', ' ') }} <span class="text-xs font-normal text-slate-500">FCFA</span>
            </div>
            <p class="mt-2 text-xs text-slate-400">Remboursements perçus sur compte</p>
        </div>

        <div class="panel p-5 border-l-4 border-l-slate-400">
            <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1">Brouillons en préparation</div>
            <div class="num text-2xl font-black text-slate-700 dark:text-slate-300">
                {{ number_format($stats['total_brouillon'], 0, ',', ' ') }} <span class="text-xs font-normal text-slate-500">FCFA</span>
            </div>
            <p class="mt-2 text-xs text-slate-400">Prêts pour validation & envoi</p>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="panel p-4 mb-5">
        <form method="get" action="{{ route('bordereaux.index') }}" class="flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[200px]">
                <select name="assurance_id" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-700 dark:text-slate-200">
                    <option value="">Toutes les assurances</option>
                    @foreach($assurances as $assur)
                        <option value="{{ $assur->id }}" @selected(request('assurance_id') == $assur->id)>
                            {{ $assur->nom }} ({{ $assur->code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="w-48">
                <select name="statut" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-700 dark:text-slate-200">
                    <option value="">Tous les statuts</option>
                    @foreach(\App\Enums\StatutBordereauAssurance::cases() as $st)
                        <option value="{{ $st->value }}" @selected(request('statut') === $st->value)>
                            {{ $st->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn-ghost text-xs font-bold inline-flex items-center gap-1.5">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                </svg>
                <span>Filtrer</span>
            </button>

            @if(request()->hasAny(['assurance_id', 'statut']))
                <a href="{{ route('bordereaux.index') }}" class="text-xs text-slate-500 hover:text-slate-700 underline">
                    Réinitialiser
                </a>
            @endif
        </form>
    </div>

    {{-- Liste des bordereaux --}}
    <div class="panel overflow-hidden">
        <div class="table-container">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-800/50 text-xs font-bold uppercase text-slate-500 dark:text-slate-400">
                        <th class="py-3 px-4">Référence</th>
                        <th class="py-3 px-4">Assurance / Mutuelle</th>
                        <th class="py-3 px-4">Période couverte</th>
                        <th class="py-3 px-4 text-center">Dossiers</th>
                        <th class="py-3 px-4 text-right">Montant Réclamé</th>
                        <th class="py-3 px-4 text-center">Statut</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($bordereaux as $bordereau)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900 dark:text-white">
                                <a href="{{ route('bordereaux.show', $bordereau) }}" class="text-emerald-600 dark:text-emerald-400 hover:underline">
                                    {{ $bordereau->reference }}
                                </a>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800 dark:text-slate-200">
                                {{ $bordereau->assurance->nom }}
                            </td>
                            <td class="py-3.5 px-4 text-xs text-slate-600 dark:text-slate-400">
                                Du {{ $bordereau->periode_debut->format('d/m/Y') }} au {{ $bordereau->periode_fin->format('d/m/Y') }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="rounded-full bg-slate-100 dark:bg-slate-800 px-2.5 py-0.5 text-xs font-bold text-slate-700 dark:text-slate-300">
                                    {{ $bordereau->nombre_dossiers }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right font-black num text-slate-900 dark:text-white">
                                {{ number_format($bordereau->montant_total, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">FCFA</span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[11px] font-bold {{ $bordereau->statut->badgeClasses() }}">
                                    {{ $bordereau->statut->label() }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-1">
                                <a href="{{ route('bordereaux.show', $bordereau) }}" class="btn-ghost text-xs inline-flex items-center gap-1">
                                    <span>Consulter</span>
                                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M5 12h14M12 5l7 7-7 7"/>
                                    </svg>
                                </a>
                                <a href="{{ route('bordereaux.imprimer', $bordereau) }}" target="_blank" class="btn-icon size-8 inline-flex items-center justify-center text-slate-500 hover:text-emerald-600" title="Imprimer le bordereau">
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-12 text-slate-400">
                                Aucun bordereau de facturation enregistré.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bordereaux->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $bordereaux->links() }}
            </div>
        @endif
    </div>

@endsection
