@extends('layout')
@section('content')
    <nav>
        @include('app.menu')
    </nav>
    <section>
        <div class="row mt-4">

            <div class="col-md-12">

                <div class="card shadow">

                    <div class="card-header bg-dark text-white text-center fs-30">
                        TOP 5 DES PRODUITS LES PLUS VENDUS
                    </div>

                    <div class="card-body">

                        <table class="table table-striped text-center">

                            <thead>
                                <tr>
                                    <th>Produit</th>
                                    <th>Quantité Vendue</th>
                                    <th>Montant Généré</th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse($topMedicaments as $m)
                                    <tr>
                                        <td>{{ $m->nom }}</td>

                                        <td>
                                            <span class="badge bg-primary">
                                                {{ $m->total_quantite }}
                                            </span>
                                        </td>

                                        <td>
                                            <span class="badge bg-success">
                                                {{ number_format($m->total_montant, 0, ',', ' ') }} FCFA
                                            </span>
                                        </td>
                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="3">Aucune vente enregistrée</td>
                                    </tr>
                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </section>
@endsection
