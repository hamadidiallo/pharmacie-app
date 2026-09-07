@extends('layout')

@section('titre', 'Catalogue & Traçabilité des Lots — GESTA PHARM')

@section('topbar')
    <div class="flex items-center gap-3">
        <div>
            <h1 class="text-base font-bold text-slate-900 tracking-tight">Catalogue & Traçabilité des Lots</h1>
            <p class="text-xs text-slate-500 font-medium">Gestion officinale, conformité FEFO & contrôle sanitaire des arrivages</p>
        </div>
    </div>

    <div class="ml-auto flex items-center gap-3">
        <form method="get" class="flex items-center gap-2">
            <input type="hidden" name="filtre" value="{{ $filtre }}">
            <label for="q" class="sr-only">Rechercher un médicament</label>
            <div class="relative">
                <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 size-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                </svg>
                <input type="search" id="q" name="q" value="{{ $recherche }}" placeholder="Nom, DCI, code-barres…"
                    class="field-input h-10 w-48 sm:w-[320px] pl-9 text-xs">
            </div>
        </form>

        @can('gerer-medicaments')
            <a href="{{ route('medicaments.create') }}" class="btn-primary">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round">
                    <path d="M12 5v14M5 12h14" />
                </svg>
                <span class="hidden sm:inline">Nouveau médicament</span>
            </a>
        @endcan
    </div>
@endsection

@section('content')

    @php
        $onglets = [
            'tous' => ['Tous les produits', $compteurs['tous'], null],
            'stock' => ['En stock normal', null, null],
            'faible' => ['Stock faible', $compteurs['faible'], 'warn'],
            'rupture' => ['Rupture de stock', $compteurs['rupture'], 'danger'],
            'expire' => ['Expire bientôt', $compteurs['expire'], 'warn'],
        ];
    @endphp

    {{-- Filtres Rapides --}}
    <div class="flex flex-wrap gap-2">
        @foreach ($onglets as $cle => [$libelle, $compteur, $ton])
            <a href="{{ route('medicaments.index', array_filter(['filtre' => $cle, 'q' => $recherche])) }}"
                @class([
                    'filter-tab-active' => $filtre === $cle,
                    'filter-tab' => $filtre !== $cle && !$ton,
                    'filter-tab border-amber-200 bg-amber-50/70 text-amber-800 hover:bg-amber-100/80' =>
                        $filtre !== $cle && $ton === 'warn',
                    'filter-tab border-red-200 bg-red-50/70 text-red-700 hover:bg-red-100/80' =>
                        $filtre !== $cle && $ton === 'danger',
                ])>
                {{ $libelle }}
                @if ($compteur !== null)
                    <span class="ml-1 opacity-80">({{ $compteur }})</span>
                @endif
            </a>
        @endforeach
    </div>

    {{-- Tableau des Médicaments & Lots --}}
    <div class="panel overflow-hidden">

        <div class="overflow-x-auto">
            <table class="table-data">

                <thead>
                    <tr>
                        <th>MÉDICAMENT & IDENTIFICATION</th>
                        <th>TABLEAU & DÉLIVRANCE</th>
                        <th>PRIX UNITAIRE</th>
                        <th>STOCK DISPONIBLE</th>
                        <th>TRAÇABILITÉ / FEFO</th>
                        <th class="text-right">ACTIONS</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($medicaments as $medicament)
                        @php
                            $prochain = $medicament->prochainLot();
                            $nbLotsActifs = $medicament->lots->where('statut', \App\Enums\StatutLot::Actif)->count();
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">

                            <td class="py-3.5">
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-slate-900 text-sm">{{ $medicament->nom }}</span>
                                        @if ($medicament->forme || $medicament->dosage)
                                            <span class="inline-flex items-center text-[11px] font-medium text-slate-600 bg-slate-100 px-2 py-0.5 rounded">
                                                {{ implode(' · ', array_filter([$medicament->forme, $medicament->dosage])) }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500">
                                        @if ($medicament->dci)
                                            <span class="inline-flex items-center gap-1 text-emerald-700 font-medium">
                                                <span class="text-[10px] uppercase font-bold text-emerald-600 tracking-wider">DCI:</span>
                                                {{ $medicament->dci }}
                                            </span>
                                        @endif

                                        @if ($medicament->code_barre)
                                            <span class="inline-flex items-center gap-1 font-mono text-[11px] text-slate-400">
                                                <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M3 5v14M8 5v14M12 5v14M17 5v14M21 5v14" />
                                                </svg>
                                                {{ $medicament->code_barre }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div class="flex flex-col gap-1 items-start">
                                    @if ($medicament->tableau)
                                        <span class="inline-flex items-center text-[10px] font-bold px-2 py-0.5 rounded-full {{ $medicament->tableau->badgeClasses() }}">
                                            {{ $medicament->tableau->libelle() }}
                                        </span>
                                    @endif

                                    @if ($medicament->ordonnance_requise)
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-rose-700 bg-rose-50 px-1.5 py-0.5 rounded border border-rose-200">
                                            <svg class="size-2.5 text-rose-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                                <circle cx="12" cy="12" r="10" /><line x1="12" y1="8" x2="12" y2="12" /><line x1="12" y1="16" x2="12.01" y2="16" />
                                            </svg>
                                            Rx obligatoire
                                        </span>
                                    @else
                                        <span class="text-[11px] text-slate-400">Vente libre</span>
                                    @endif
                                </div>
                            </td>

                            <td class="num font-bold text-slate-900">
                                {{ number_format($medicament->prix, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">FCFA</span>
                            </td>

                            <td>
                                <x-pastille-stock :medicament="$medicament" />
                            </td>

                            <td>
                                <div class="flex flex-col gap-1">
                                    @if ($prochain)
                                        <div class="flex items-center gap-1.5 text-xs">
                                            <span class="font-mono font-semibold text-slate-700 bg-slate-100 px-1.5 py-0.5 rounded text-[11px]">
                                                {{ $prochain->numero_lot }}
                                            </span>
                                            <span @class([
                                                'text-[11px] font-medium',
                                                'text-amber-700 font-bold' => $prochain->expireBientot(),
                                                'text-slate-500' => !$prochain->expireBientot(),
                                            ])>
                                                exp {{ $prochain->date_expiration->format('m/Y') }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400">Aucun lot actif</span>
                                    @endif

                                    <button type="button" data-dialog="lots-{{ $medicament->id }}"
                                        class="inline-flex items-center gap-1 text-[11px] font-semibold text-brand hover:underline self-start">
                                        <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" />
                                        </svg>
                                        {{ $medicament->lots->count() }} lot(s) · Gérer
                                    </button>
                                </div>
                            </td>

                            <td>
                                <div class="flex justify-end gap-1.5">

                                    <button type="button" data-dialog="lots-{{ $medicament->id }}"
                                        class="btn-icon" aria-label="Gérer les lots de {{ $medicament->nom }}" title="Lots & Traçabilité">
                                        <svg class="size-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" />
                                            <path d="m3.3 7 8.7 5 8.7-5" /><path d="M12 22V12" />
                                        </svg>
                                    </button>

                                    @can('gerer-medicaments')
                                        <button type="button" data-dialog="modifier-{{ $medicament->id }}"
                                            class="btn-icon" aria-label="Modifier {{ $medicament->nom }}" title="Modifier la fiche">
                                            <svg class="size-4 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M12 20h9" />
                                                <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" />
                                            </svg>
                                        </button>

                                        <button type="button" data-dialog="supprimer-{{ $medicament->id }}"
                                            class="btn-icon-danger" aria-label="Supprimer {{ $medicament->nom }}" title="Archiver">
                                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M3 6h18M8 6V4h8v2M6 6l1 14h10l1-14" />
                                            </svg>
                                        </button>
                                    @endcan

                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                @if ($recherche !== '' || $filtre !== 'tous')
                                    Aucun médicament ne correspond à cette recherche.
                                    <a href="{{ route('medicaments.index') }}" class="font-semibold text-emerald-600 hover:underline">Réinitialiser les filtres</a>
                                @else
                                    Aucun médicament enregistré dans le stock.
                                    @can('gerer-medicaments')
                                        <a href="{{ route('medicaments.create') }}" class="font-semibold text-emerald-600 hover:underline">Ajouter le premier médicament</a>
                                    @endcan
                                @endif
                            </td>
                        </tr>
                    @endforelse

                </tbody>

            </table>
        </div>

        <div class="flex items-center justify-between gap-3 border-t border-slate-100 px-6 py-4">
            <span class="text-xs font-medium text-slate-500">
                Affichage de {{ $medicaments->firstItem() ?? 0 }} à {{ $medicaments->lastItem() ?? 0 }} sur {{ $medicaments->total() }} références
            </span>
            {{ $medicaments->onEachSide(1)->links() }}
        </div>

    </div>

    {{-- Modals de Gestion des Lots, Modification & Suppression --}}
    @foreach ($medicaments as $medicament)

        {{-- 1. Modal Traçabilité & Lots --}}
        <dialog id="lots-{{ $medicament->id }}" class="dialog-panel max-w-3xl">
            <div class="panel-head">
                <div>
                    <h2 class="panel-title flex items-center gap-2">
                        <svg class="size-5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" />
                        </svg>
                        Lots & Traçabilité FEFO : {{ $medicament->nom }}
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Stock vendable actuel : <strong class="text-slate-900">{{ $medicament->stock }}</strong> boîtes
                        @if ($medicament->dci) · DCI : <span class="text-emerald-700 font-medium">{{ $medicament->dci }}</span> @endif
                    </p>
                </div>
                <button type="button" data-dialog-close class="btn-icon" aria-label="Fermer">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <path d="M18 6 6 18M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-6 space-y-6">

                {{-- Liste des lots enregistrés --}}
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Historique des Lots (Ordre FEFO)</h3>

                    @if ($medicament->lots->isEmpty())
                        <div class="p-4 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-xs">
                            Aucun lot n'est encore enregistré pour ce produit. Ajoutez votre premier arrivage ci-dessous.
                        </div>
                    @else
                        <div class="border border-slate-200 rounded-lg overflow-hidden">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                                    <tr>
                                        <th class="p-2.5">NUMÉRO DE LOT</th>
                                        <th class="p-2.5">EXPIRATION</th>
                                        <th class="p-2.5">DISPONIBLE / INITIAL</th>
                                        <th class="p-2.5">STATUT</th>
                                        @can('gerer-medicaments')
                                            <th class="p-2.5 text-right">ACTION</th>
                                        @endcan
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($medicament->lots as $lot)
                                        <tr class="hover:bg-slate-50/60">
                                            <td class="p-2.5 font-mono font-bold text-slate-900">
                                                {{ $lot->numero_lot }}
                                                @if ($lot->date_fabrication)
                                                    <div class="text-[10px] text-slate-400 font-sans">Fab: {{ $lot->date_fabrication->format('d/m/Y') }}</div>
                                                @endif
                                            </td>
                                            <td class="p-2.5">
                                                <span @class([
                                                    'font-semibold',
                                                    'text-rose-600 font-bold' => $lot->estExpire(),
                                                    'text-amber-600' => !$lot->estExpire() && $lot->expireBientot(),
                                                    'text-slate-700' => !$lot->estExpire() && !$lot->expireBientot(),
                                                ])>
                                                    {{ $lot->date_expiration->format('d/m/Y') }}
                                                    @if ($lot->estExpire())
                                                        <span class="text-[10px] text-rose-600 block font-normal">(Périmé)</span>
                                                    @endif
                                                </span>
                                            </td>
                                            <td class="p-2.5">
                                                <strong class="text-slate-900">{{ $lot->quantite_actuelle }}</strong> / {{ $lot->quantite_initiale }}
                                            </td>
                                            <td class="p-2.5">
                                                <span class="inline-flex items-center text-[10px] font-bold px-2 py-0.5 rounded-full {{ $lot->statut->badgeClasses() }}">
                                                    {{ $lot->statut->libelle() }}
                                                </span>
                                                @if ($lot->motif_isolement)
                                                    <div class="text-[10px] text-amber-700 mt-0.5 max-w-[150px] truncate" title="{{ $lot->motif_isolement }}">
                                                        Motif: {{ $lot->motif_isolement }}
                                                    </div>
                                                @endif
                                            </td>
                                            @can('gerer-medicaments')
                                                <td class="p-2.5 text-right">
                                                    @if ($lot->statut === \App\Enums\StatutLot::Actif)
                                                        <button type="button" data-dialog="isoler-lot-{{ $lot->id }}"
                                                            class="text-[11px] font-medium text-amber-700 hover:text-amber-900 underline">
                                                            Quarantaine / Rappel
                                                        </button>
                                                    @elseif ($lot->statut === \App\Enums\StatutLot::Isole || $lot->statut === \App\Enums\StatutLot::Rappele)
                                                        <form action="{{ route('medicaments.lots.reactiver', $lot) }}" method="POST" class="inline">
                                                            @csrf
                                                            <button type="submit" class="text-[11px] font-medium text-emerald-700 hover:text-emerald-900 underline">
                                                                Réactiver
                                                            </button>
                                                        </form>
                                                    @else
                                                        <span class="text-slate-400 text-[10px]">—</span>
                                                    @endif
                                                </td>
                                            @endcan
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- Formulaire d'ajout d'arrivage (Nouveau lot) --}}
                @can('gerer-medicaments')
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                            <svg class="size-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 5v14M5 12h14" />
                            </svg>
                            Réception Arrivage / Nouveau Lot
                        </h3>

                        <form action="{{ route('medicaments.lots.store', $medicament) }}" method="POST" class="space-y-3">
                            @csrf
                            <div class="grid gap-3 sm:grid-cols-4">
                                <div>
                                    <label class="field-label">N° de lot *</label>
                                    <input type="text" name="numero_lot" required placeholder="Ex: LOT-2026-B12" class="field-input text-xs font-mono">
                                </div>
                                <div>
                                    <label class="field-label">Quantité reçue *</label>
                                    <input type="number" name="quantite" required min="1" placeholder="50" class="field-input text-xs">
                                </div>
                                <div>
                                    <label class="field-label">Date d'expiration *</label>
                                    <input type="date" name="date_expiration" required class="field-input text-xs">
                                </div>
                                <div>
                                    <label class="field-label">Prix d'achat unitaire (opt.)</label>
                                    <input type="number" name="prix_achat_unitaire" min="0" placeholder="1200" class="field-input text-xs">
                                </div>
                            </div>
                            <div class="flex justify-end pt-1">
                                <button type="submit" class="btn-primary text-xs py-2">
                                    Enregistrer l'arrivage de lot
                                </button>
                            </div>
                        </form>
                    </div>
                @endcan

            </div>

            <div class="panel-foot flex justify-end">
                <button type="button" data-dialog-close class="btn-ghost">Fermer</button>
            </div>
        </dialog>

        {{-- 2. Modales de mise en quarantaine par lot --}}
        @foreach ($medicament->lots as $lot)
            @if ($lot->statut === \App\Enums\StatutLot::Actif)
                <dialog id="isoler-lot-{{ $lot->id }}" class="dialog-panel max-w-md">
                    <div class="panel-head">
                        <h2 class="panel-title text-amber-800">Isolement / Rappel Sanitaire : Lot {{ $lot->numero_lot }}</h2>
                    </div>

                    <form action="{{ route('medicaments.lots.isoler', $lot) }}" method="POST">
                        @csrf
                        <div class="p-6 space-y-4 text-xs">
                            <p class="text-slate-600">
                                L'isolement retire immédiatement les <strong>{{ $lot->quantite_actuelle }}</strong> boîtes de ce lot de la vente au comptoir et de l'algorithme FEFO.
                            </p>

                            <div>
                                <label class="field-label">Type de mesure sanitaire *</label>
                                <div class="space-y-2 mt-1">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="statut" value="isole" checked class="text-amber-600 focus:ring-amber-500">
                                        <span class="font-medium text-slate-800">Mise en quarantaine provisoire (contrôle qualité, doute)</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="statut" value="rappele" class="text-rose-600 focus:ring-rose-500">
                                        <span class="font-medium text-slate-800">Rappel officiel de lot (Avis ANRP / Laboratoire fabricant)</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="field-label">Motif du retrait / référence de la note *</label>
                                <input type="text" name="motif" required placeholder="Ex: Note d'alerte ANRP n° 2026/04 - Défaut d'opercule" class="field-input text-xs">
                            </div>
                        </div>

                        <div class="panel-foot flex justify-end gap-2">
                            <button type="button" data-dialog-close class="btn-ghost">Annuler</button>
                            <button type="submit" class="btn-danger">Confirmer l'isolement du lot</button>
                        </div>
                    </form>
                </dialog>
            @endif
        @endforeach

        {{-- 3. Modal de Modification générale --}}
        @can('gerer-medicaments')
            <dialog id="modifier-{{ $medicament->id }}" class="dialog-panel max-w-2xl">
                <div class="panel-head">
                    <h2 class="panel-title">Fiche Produit : {{ $medicament->nom }}</h2>
                    <button type="button" data-dialog-close class="btn-icon" aria-label="Fermer">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <path d="M18 6 6 18M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-6">
                    @include('medicaments.edit', ['medicament' => $medicament])
                </div>
            </dialog>

            {{-- 4. Modal de Suppression / Archivage --}}
            <dialog id="supprimer-{{ $medicament->id }}" class="dialog-panel">
                <div class="panel-head">
                    <h2 class="panel-title text-red-700">Archiver ce médicament ?</h2>
                </div>

                <div class="p-6 text-sm text-slate-600">
                    <strong class="font-bold text-slate-900">{{ $medicament->nom }}</strong> sera retiré du catalogue actif.
                    <p class="mt-2 text-xs text-slate-500">Ses lots, ses ventes passées et ses tickets de caisse resteront fidèlement archivés pour les audits officinaux.</p>
                </div>

                <div class="flex justify-end gap-2.5 border-t border-slate-100 px-6 py-4">
                    <button type="button" data-dialog-close class="btn-ghost">Annuler</button>
                    <form action="{{ route('medicaments.destroy', $medicament) }}" method="POST">
                        @csrf
                        @method('delete')
                        <button type="submit" class="btn-danger">Confirmer l'archivage</button>
                    </form>
                </div>
            </dialog>
        @endcan

    @endforeach

@endsection
