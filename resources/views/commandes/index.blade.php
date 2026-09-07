@extends('layout')

@section('titre', 'Commandes d\'Approvisionnement — GESTA PHARM')

@section('topbar')
    <div class="flex items-center gap-3">
        <div>
            <h1 class="text-base font-bold text-slate-900 tracking-tight">Approvisionnements & Commandes</h1>
            <p class="text-xs text-slate-500 font-medium">Gestion des achats grossistes, réassorts et suivi des réceptions BL</p>
        </div>
    </div>

    <div class="ml-auto flex items-center gap-2.5">
        <a href="{{ route('commandes.suggerer') }}" class="btn-primary bg-gradient-to-r from-emerald-600 via-teal-600 to-sky-600 shadow-md">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3Z"/>
            </svg>
            <span>Générer commande suggérée</span>
        </a>

        <a href="{{ route('commandes.create') }}" class="btn-ghost">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M12 5v14M5 12h14" />
            </svg>
            <span>Commande manuelle</span>
        </a>
    </div>
@endsection

@section('content')

    {{-- Filtres par Statut --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap gap-2 text-xs">
            <a href="{{ route('commandes.index') }}"
                @class(['filter-tab-active' => !$statutActif, 'filter-tab' => $statutActif])>
                Toutes les commandes
            </a>
            @foreach ($statuts as $st)
                <a href="{{ route('commandes.index', ['statut' => $st->value]) }}"
                    @class(['filter-tab-active' => $statutActif === $st->value, 'filter-tab' => $statutActif !== $st->value])>
                    {{ $st->libelle() }}
                </a>
            @endforeach
        </div>

        {{-- Filtre par fournisseur --}}
        @if ($fournisseurs->isNotEmpty())
            <form method="GET" class="flex items-center gap-2">
                @if ($statutActif) <input type="hidden" name="statut" value="{{ $statutActif }}"> @endif
                <label for="fournisseur_id" class="text-xs text-slate-500 font-medium">Fournisseur :</label>
                <select id="fournisseur_id" name="fournisseur_id" onchange="this.form.submit()" class="field-input h-9 text-xs py-1">
                    <option value="">Tous les fournisseurs</option>
                    @foreach ($fournisseurs as $fourn)
                        <option value="{{ $fourn->id }}" @selected($fournisseurActif == $fourn->id)>{{ $fourn->nom }}</option>
                    @endforeach
                </select>
            </form>
        @endif
    </div>

    {{-- Tableau des Commandes --}}
    <div class="panel overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table-data">
                <thead>
                    <tr>
                        <th>RÉFÉRENCE & DATE</th>
                        <th>FOURNISSEUR</th>
                        <th>LIVRAISON PRÉVUE</th>
                        <th>ARTICLES</th>
                        <th>MONTANT ESTIMÉ</th>
                        <th>STATUT</th>
                        <th class="text-right">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($commandes as $commande)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5">
                                <a href="{{ route('commandes.show', $commande) }}" class="font-bold text-slate-900 hover:text-emerald-600 transition-colors">
                                    {{ $commande->reference }}
                                </a>
                                <div class="text-xs text-slate-400">
                                    Créée le {{ $commande->date_commande->format('d/m/Y') }} par {{ $commande->user->firstname }}
                                </div>
                            </td>

                            <td>
                                <div class="font-semibold text-slate-800 text-xs">{{ $commande->fournisseur->nom }}</div>
                                <div class="text-[11px] text-slate-400">{{ $commande->fournisseur->ville }}</div>
                            </td>

                            <td class="text-xs text-slate-600">
                                @if ($commande->date_livraison_prevue)
                                    <span class="num">{{ $commande->date_livraison_prevue->format('d/m/Y') }}</span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                                @if ($commande->numero_bl)
                                    <div class="text-[10px] text-emerald-700 font-mono font-bold">BL: {{ $commande->numero_bl }}</div>
                                @endif
                            </td>

                            <td>
                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-700">
                                    {{ $commande->lignes->count() }} référence(s)
                                </span>
                            </td>

                            <td class="num font-bold text-slate-900">
                                {{ number_format($commande->total_estime, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">FCFA</span>
                            </td>

                            <td>
                                <span class="inline-flex items-center text-[10px] font-bold px-2.5 py-0.5 rounded-full {{ $commande->statut->badgeClasses() }}">
                                    {{ $commande->statut->libelle() }}
                                </span>
                            </td>

                            <td>
                                <div class="flex justify-end gap-1.5">
                                    <a href="{{ route('commandes.show', $commande) }}" class="btn-icon" title="Consulter la commande">
                                        <svg class="size-4 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </a>

                                    <a href="{{ route('commandes.bon-commande', $commande) }}" class="btn-icon" title="Imprimer le bon de commande">
                                        <svg class="size-4 text-sky-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="6 9 6 2 18 2 18 9"/>
                                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                                            <rect width="12" height="8" x="6" y="14"/>
                                        </svg>
                                    </a>

                                    @if ($commande->peutEtreRecue())
                                        <a href="{{ route('commandes.reception', $commande) }}" class="btn-icon bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100" title="Réceptionner le BL">
                                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" />
                                                <path d="m3.3 7 8.7 5 8.7-5" /><path d="M12 22V12" />
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                Aucune commande trouvée.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-between border-t border-slate-100 px-6 py-4">
            <span class="text-xs text-slate-500 font-medium">
                {{ $commandes->total() }} commande(s) au total
            </span>
            {{ $commandes->links() }}
        </div>
    </div>

@endsection
