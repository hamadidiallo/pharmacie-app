@extends('layout')
@section('content')
    <nav>
        @include('app.menu')
    </nav>
    <section class="container">
        <br>

        <div class="card shadow">

            <div class="card-header d-flex justify-content-between">

                <h3>Liste des Médicaments</h3>

                <a href="{{ route('medicament.create') }}" class="btn btn-outline-info ">
                    Ajouter un medicament
                </a>

            </div>

            <div class="card-body">

                <table class="table table-bordered table-hover w-100">

                    <thead class="table-dark">

                        <tr class="text-center">
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Prix</th>
                            <th>Quantité</th>
                            <th>Date Expiration</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>

                    </thead>

                    <tbody>

                        @forelse ($medicaments as $medicament)
                            <tr class="text-center">
                                <td>
                                    {{ $medicament->id }}
                                </td>
                                <td>
                                    {{ $medicament->nom }}
                                </td>

                                <td>
                                    {{ $medicament->prix }} FCFA
                                </td>

                                <td>

                                    @if ($medicament->stock <= 5)
                                        <span id="stock-{{ $medicament->id }} "class="badge bg-danger">
                                            Stock faible :
                                            {{ $medicament->stock }}
                                        </span>
                                    @else
                                        <span id="stock-{{ $medicament->id }}" class="badge bg-success">
                                            {{ $medicament->stock }}
                                        </span>
                                    @endif

                                </td>

                                <td>
                                    {{ $medicament->date_expiration->format('d - m - y') }}
                                </td>

                                <td>
                                    {{ $medicament->description }}
                                </td>

                                <td class="d-flex gap-2 justify-content-center">
                                    <!-- Button trigger modal -->
                                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal"
                                        data-bs-target="#delete{{ $medicament->id }}">
                                        SUPPRIMER
                                    </button>

                                    <!-- Modal -->
                                    <div class="modal fade" id="delete{{ $medicament->id }}" tabindex="-1"
                                        aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h1 class="modal-title fs-5" id="exampleModalLabel">SUPPRESSION
                                                        MEDICAMENT</h1>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    VOULEZ-VOUS SUPPRIMER CE MEDICAMENT ?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">NON</button>
                                                    <form action="{{ route('medicament.delete', $medicament) }}"
                                                        method="POST">
                                                        @csrf
                                                        @method('delete')
                                                        <button type="submit" class="btn btn-danger btn-sm">
                                                            OUI
                                                        </button>

                                                    </form>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Button trigger modal -->
                                    <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal"
                                        data-bs-target="#update{{ $medicament->id }}">
                                        MODIFIER
                                    </button>

                                    <!-- Modal -->
                                    <div class="modal fade" id="update{{ $medicament->id }}" tabindex="-1"
                                        aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h1 class="modal-title fs-5" id="exampleModalLabel">MODIFICATION
                                                        MEDICAMENT</h1>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    @include('medicaments.edit', [
                                                        'medicament' => $medicament,
                                                    ])
                                                </div>

                                            </div>
                                        </div>
                                    </div>





                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="6" class="text-center">
                                    Nous n'avons pas de médicaments pour l'instant.
                                </td>
                            </tr>
                        @endforelse
                        <p>{{ $medicaments->links() }}</p>

                    </tbody>

                </table>

            </div>

        </div>


    </section>
@endsection
{{-- // SCRIPTS JS POUR RENVOIE STOCK RESTANT APRES VENTE CHAQUE 2 SECONDES --}}
<script>
    setInterval(() => {

        fetch('/stocks')

            .then(res => res.json())

            .then(data => {

                data.forEach(medicament => {

                    let stockElement = document.getElementById(
                        'stock-' + medicament.id
                    );

                    if (stockElement) {

                        stockElement.innerText = medicament.stock;

                    }

                });

            });

    }, 2000);
</script>
