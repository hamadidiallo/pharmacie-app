@extends('layout')

@section('content')
    <nav>@include('app.menu')</nav>
    <div id="messageStock"></div>
    <div class="container mt-4">

        <div class="card shadow">

            <div class="card-header">

                <h3>
                    Nouvelle Vente
                </h3>

            </div>

            <div class="card-body">

                <div class="row">

                    <!-- RECHERCHE -->
                    <div class="col-md-5">

                        <input type="text" id="search" class="form-control" placeholder="Rechercher médicament">

                        <div id="resultats" class="mt-3">

                        </div>

                    </div>

                    <!-- PANIER -->
                    <div class="col-md-7">

                        <div class="card shadow">

                            <div class="card-header">
                                🛒 Panier
                            </div>

                            <div class="card-body">

                                <table class="table table-bordered">
                                    <thead>
                                        <tr class="text-center">
                                            <th>Produit</th>
                                            <th>Prix</th>
                                            <th>Quantité</th>
                                            <th>Total</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>

                                    <tbody id="panier">
                                        <!-- JS va remplir ici -->
                                    </tbody>
                                </table>

                                <h4>
                                    Total : <span id="total">0</span> FCFA
                                </h4>

                                <button class="btn btn-success w-100" onclick="validerVente()">
                                    Valider Vente
                                </button>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>
@endsection
