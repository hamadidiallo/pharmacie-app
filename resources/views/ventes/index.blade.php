@extends('layout')

@section('content')
    <nav>@include('app.menu')</nav>

    <div class="container mt-4">

        <div class="card shadow">

            <div class="card-header">
                <h3>Liste des ventes</h3>
            </div>
            {{-- INPUT RECHERCHE ID TICKET --}}
            <div class="row mb-3">

                <div class="col-md-4">

                    <input type="text" id="searchTicket" class="form-control" placeholder="🔍 Rechercher par ticket ID">

                </div>

            </div>
            <div id="noResult" class="alert alert-danger mt-2" style="display: none;">

                ❌ Aucun ticket trouvé

            </div>

            <div class="card-body">

                <table class="table table-bordered text-center">

                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Total</th>
                            <th>Date</th>
                            <th>HEURE</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach ($ventes as $vente)
                            <tr class="vente-row">
                                <td>{{ $vente->id }}</td>
                                <td>{{ $vente->total }} FCFA</td>
                                <td>{{ $vente->date_vente }}</td>
                                <td>{{ $vente->created_at->format('H:m:i') }}</td>
                                <td>
                                    <a href="{{ route('ventes.show', $vente->id) }}" class="btn btn-outline-primary btn-sm">
                                        Voir ticket
                                    </a>
                                </td>
                                <td>
                                <td class="d-flex gap-2 justify-content-center">
                                    <!-- Button trigger modal -->
                                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal"
                                        data-bs-target="#delete{{ $vente->id }}">
                                        SUPPRIMER TICKET
                                    </button>

                                    <!-- Modal -->
                                    <div class="modal fade" id="delete{{ $vente->id }}" tabindex="-1"
                                        aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h1 class="modal-title fs-5" id="exampleModalLabel">SUPPRESSION
                                                        VENTE</h1>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    VOULEZ-VOUS SUPPRIMER CETTE VENTE ?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">NON</button>
                                                    <form action="{{ route('ventes.destroy', $vente->id) }}" method="POST">
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
                                </td>
                            </tr>
                        @endforeach
                        <p>{{ $ventes->links() }}</p>

                    </tbody>

                </table>

            </div>

        </div>

    </div>
@endsection
{{-- SCRIPT POUR RECHERCHE TICKET  --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {

        const searchInput = document.getElementById('searchTicket');

        searchInput.addEventListener('keyup', function() {

            let valeur = this.value.toLowerCase();

            let lignes = document.querySelectorAll('.vente-row');

            let found = false;

            lignes.forEach(ligne => {

                let ticket = ligne.dataset.ticket.toLowerCase();

                if (ticket.includes(valeur)) {

                    ligne.style.display = '';

                    found = true;

                } else {

                    ligne.style.display = 'none';
                }

            });

            let message = document.getElementById('noResult');

            if (!found) {

                message.style.display = 'block';

            } else {

                message.style.display = 'none';
            }

        });

    });
</script>
