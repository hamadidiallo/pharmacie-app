@extends('layout')

@section('titre', 'Médicaments — GESTA PHARM')

@section('content')

    <section class="card-officine overflow-hidden">

        <div class="card-head">
            <div>
                <h1 class="card-title">Médicaments</h1>
                <p class="mt-0.5 text-sm text-ink-soft">{{ $medicaments->total() }} produits référencés.</p>
            </div>
            <a href="{{ route('medicament.create') }}" class="btn-primary btn-sm">Ajouter un produit</a>
        </div>

        <div class="overflow-x-auto">
            <table class="table-officine">

                <thead>
                    <tr>
                        <th class="w-12">Réf</th>
                        <th>Produit</th>
                        <th class="text-right">Prix</th>
                        <th class="text-right">Stock</th>
                        <th class="text-right">Expiration</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($medicaments as $medicament)
                        <tr>

                            <td class="num text-ink-soft">{{ $medicament->id }}</td>

                            <td>
                                <span class="font-medium">{{ $medicament->nom }}</span>
                                <span class="mt-0.5 block text-xs text-ink-soft">{{ $medicament->description }}</span>
                            </td>

                            <td class="num whitespace-nowrap">
                                {{ number_format($medicament->prix, 0, ',', ' ') }}
                                <span class="text-xs text-ink-soft">FCFA</span>
                            </td>

                            <td class="num">
                                @if ($medicament->stock === 0)
                                    <span class="badge-rupture" id="stock-{{ $medicament->id }}">rupture</span>
                                @elseif ($medicament->stock <= 5)
                                    <span class="badge-faible" id="stock-{{ $medicament->id }}">
                                        {{ $medicament->stock }}
                                    </span>
                                @else
                                    <span class="badge-ok" id="stock-{{ $medicament->id }}">
                                        {{ $medicament->stock }}
                                    </span>
                                @endif
                            </td>

                            <td class="num whitespace-nowrap">
                                {{ $medicament->date_expiration->format('d/m/Y') }}
                            </td>

                            <td>
                                <div class="flex justify-end gap-2">
                                    <button type="button" data-dialog="modifier-{{ $medicament->id }}"
                                        class="btn-ghost btn-sm">
                                        Modifier
                                    </button>
                                    <button type="button" data-dialog="supprimer-{{ $medicament->id }}"
                                        class="btn-ghost btn-sm text-rouge-700 hover:bg-rouge-50 hover:border-rouge-100">
                                        Supprimer
                                    </button>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-ink-soft">
                                Aucun médicament pour l'instant.
                                <a href="{{ route('medicament.create') }}"
                                    class="font-semibold text-officine-600 underline underline-offset-4">
                                    Ajoutez le premier produit
                                </a>.
                            </td>
                        </tr>
                    @endforelse

                </tbody>

            </table>
        </div>

        @if ($medicaments->hasPages())
            <div class="border-t border-rule px-5 py-4">
                {{ $medicaments->links() }}
            </div>
        @endif

    </section>

    {{-- Les boîtes de dialogue vivent hors du tableau pour ne pas casser sa structure. --}}
    @foreach ($medicaments as $medicament)

        <dialog id="modifier-{{ $medicament->id }}" class="dialog-officine">

            <div class="card-head">
                <h2 class="card-title">Modifier {{ $medicament->nom }}</h2>
                <button type="button" data-dialog-close class="btn-ghost btn-sm" aria-label="Fermer">Fermer</button>
            </div>

            <div class="p-5">
                @include('medicaments.edit', ['medicament' => $medicament])
            </div>

        </dialog>

        <dialog id="supprimer-{{ $medicament->id }}" class="dialog-officine">

            <div class="card-head">
                <h2 class="card-title">Supprimer ce médicament ?</h2>
            </div>

            <div class="p-5 text-sm text-ink-soft">
                <strong class="font-semibold text-ink">{{ $medicament->nom }}</strong>
                sera retiré du stock. Cette action est définitive.
            </div>

            <div class="flex justify-end gap-2 border-t border-rule px-5 py-4">
                <button type="button" data-dialog-close class="btn-ghost">Annuler</button>
                <form action="{{ route('medicament.delete', $medicament) }}" method="POST">
                    @csrf
                    @method('delete')
                    <button type="submit" class="btn-danger">Supprimer</button>
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
                    pastille.textContent = medicament.stock === 0 ? 'rupture' : medicament.stock;
                });
            })
            .catch(() => {});
    </script>
@endpush
