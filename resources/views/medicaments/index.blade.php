@extends('layout')

@section('titre', 'Médicaments — GESTA PHARM')

@section('topbar')
    <div class="text-base font-bold">Médicaments</div>

    <form method="get" class="ml-auto flex items-center gap-2">
        <input type="hidden" name="filtre" value="{{ $filtre }}">
        <label for="q" class="sr-only">Rechercher un médicament</label>
        <input type="search" id="q" name="q" value="{{ $recherche }}" placeholder="Rechercher par nom…"
            class="field-input h-9 w-40 sm:w-[300px]">
    </form>

    <a href="{{ route('medicament.create') }}" class="btn-primary">
        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
            stroke-linecap="round">
            <path d="M12 5v14M5 12h14" />
        </svg>
        <span class="hidden sm:inline">Ajouter un médicament</span>
    </a>
@endsection

@section('content')

    @php
        $onglets = [
            'tous' => ['Tous', $compteurs['tous'], null],
            'stock' => ['En stock', null, null],
            'faible' => ['Stock faible', $compteurs['faible'], 'warn'],
            'rupture' => ['Rupture', $compteurs['rupture'], 'danger'],
            'expire' => ['Expire bientôt', $compteurs['expire'], 'warn'],
        ];
    @endphp

    {{-- Filtres --}}
    <div class="mb-3.5 flex flex-wrap gap-2.5">
        @foreach ($onglets as $cle => [$libelle, $compteur, $ton])
            <a href="{{ route('medicaments.index', array_filter(['filtre' => $cle, 'q' => $recherche])) }}"
                @class([
                    'filter-tab-active' => $filtre === $cle,
                    'filter-tab' => $filtre !== $cle && !$ton,
                    'filter-tab border-warning-border bg-warning-bg text-warning-text hover:bg-warning-border/60' =>
                        $filtre !== $cle && $ton === 'warn',
                    'filter-tab border-danger-border bg-danger-bg text-danger-text hover:bg-danger-border/60' =>
                        $filtre !== $cle && $ton === 'danger',
                ])>
                {{ $libelle }}@if ($compteur !== null)
                    · {{ $compteur }}
                @endif
            </a>
        @endforeach
    </div>

    <div class="panel overflow-hidden">

        <div class="overflow-x-auto">
            <table class="table-data">

                <thead>
                    <tr>
                        <th>MÉDICAMENT</th>
                        <th>PRIX</th>
                        <th>STOCK</th>
                        <th>EXPIRATION</th>
                        <th class="text-right">ACTIONS</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($medicaments as $medicament)
                        <tr>

                            <td>
                                <div class="font-semibold">{{ $medicament->nom }}</div>
                                <div class="truncate text-xs text-faint">{{ $medicament->description }}</div>
                            </td>

                            <td class="num whitespace-nowrap">
                                {{ number_format($medicament->prix, 0, ',', ' ') }} FCFA
                            </td>

                            <td>
                                @if ($medicament->stock === 0)
                                    <span class="pill-danger num" id="stock-{{ $medicament->id }}">0 · rupture</span>
                                @elseif ($medicament->stock <= 5)
                                    <span class="pill-warn num" id="stock-{{ $medicament->id }}">
                                        {{ $medicament->stock }} · faible
                                    </span>
                                @else
                                    <span class="pill-ok num" id="stock-{{ $medicament->id }}">
                                        {{ $medicament->stock }}
                                    </span>
                                @endif
                            </td>

                            <td @class([
                                'num whitespace-nowrap',
                                'text-danger-fg' => $medicament->date_expiration->lt(now()->addDays(30)),
                                'text-slate-ink' => !$medicament->date_expiration->lt(now()->addDays(30)),
                            ])>
                                {{ $medicament->date_expiration->format('m / Y') }}
                            </td>

                            <td>
                                <div class="flex justify-end gap-2">

                                    <button type="button" data-dialog="modifier-{{ $medicament->id }}"
                                        class="btn-icon" aria-label="Modifier {{ $medicament->nom }}">
                                        <svg class="size-[15px]" viewBox="0 0 24 24" fill="none" stroke="#B47500"
                                            stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 20h9" />
                                            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" />
                                        </svg>
                                    </button>

                                    <button type="button" data-dialog="supprimer-{{ $medicament->id }}"
                                        class="btn-icon-danger" aria-label="Supprimer {{ $medicament->nom }}">
                                        <svg class="size-[15px]" viewBox="0 0 24 24" fill="none" stroke="#C1352B"
                                            stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 6h18M8 6V4h8v2M6 6l1 14h10l1-14" />
                                        </svg>
                                    </button>

                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-muted">
                                @if ($recherche !== '' || $filtre !== 'tous')
                                    Aucun médicament ne correspond à ce filtre.
                                    <a href="{{ route('medicaments.index') }}"
                                        class="font-semibold text-brand-500">Tout afficher</a>
                                @else
                                    Aucun médicament pour l'instant.
                                    <a href="{{ route('medicament.create') }}" class="font-semibold text-brand-500">
                                        Ajoutez le premier produit
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforelse

                </tbody>

            </table>
        </div>

        <div class="flex items-center justify-between gap-3 border-t border-hairline px-[18px] py-3.5">
            <span class="text-xs text-faint">
                {{ $medicaments->firstItem() ?? 0 }} – {{ $medicaments->lastItem() ?? 0 }}
                sur {{ number_format($medicaments->total(), 0, ',', ' ') }}
            </span>
            {{ $medicaments->onEachSide(1)->links() }}
        </div>

    </div>

    {{-- Les boîtes de dialogue vivent hors du tableau pour ne pas casser sa structure. --}}
    @foreach ($medicaments as $medicament)

        <dialog id="modifier-{{ $medicament->id }}" class="dialog-panel">

            <div class="panel-head">
                <h2 class="panel-title">Modifier {{ $medicament->nom }}</h2>
                <button type="button" data-dialog-close class="btn-icon" aria-label="Fermer">
                    <svg class="size-[15px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round">
                        <path d="M6 6l12 12M18 6 6 18" />
                    </svg>
                </button>
            </div>

            <div class="p-5">
                @include('medicaments.edit', ['medicament' => $medicament])
            </div>

        </dialog>

        <dialog id="supprimer-{{ $medicament->id }}" class="dialog-panel">

            <div class="panel-head">
                <h2 class="panel-title">Supprimer ce médicament ?</h2>
            </div>

            <div class="p-5 text-[13px] text-muted">
                <strong class="font-semibold text-ink">{{ $medicament->nom }}</strong>
                sera retiré du stock. Cette action est définitive.
            </div>

            <div class="flex justify-end gap-2 border-t border-hairline px-5 py-4">
                <button type="button" data-dialog-close class="btn-ghost">Annuler</button>
                <form action="{{ route('medicament.delete', $medicament) }}" method="POST">
                    @csrf
                    @method('delete')
                    <button type="submit" class="btn bg-danger-fg text-white hover:bg-danger-text">Supprimer</button>
                </form>
            </div>

        </dialog>

    @endforeach

@endsection

@push('scripts')
    <script>
        // Rafraîchit les pastilles de stock sans recharger la page.
        fetch('/medicaments/stocks')
            .then(reponse => reponse.json())
            .then(medicaments => {
                medicaments.forEach(medicament => {
                    const pastille = document.getElementById('stock-' + medicament.id);
                    if (!pastille) return;
                    pastille.textContent = medicament.stock === 0 ?
                        '0 · rupture' :
                        medicament.stock <= 5 ? medicament.stock + ' · faible' : medicament.stock;
                });
            })
            .catch(() => {});
    </script>
@endpush
