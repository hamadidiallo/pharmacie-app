@extends('layout')

@section('content')
    <nav>
        @include('app.menu')
    </nav>
    <div class="container py-4">

        <h1 class="mb-4 text-center">
            TABLEAU DE BORDS POUR SUIVI VENTE
        </h1>

        <div class="row g-3 flex-nowrap overflow-auto">

            <!-- Vente Jour -->

            <div class="col-md-3">

                <div class="card dashboard-card shadow border-0 bg-primary text-white">

                    <div class="card-body text-center">

                        <h5 class="fw-bold">VENTES DU JOUR</h5>

                        <h2 class="fw-bold">{{ $venteJour }} FCFA</h2>

                    </div>

                </div>

            </div>

            <!-- Vente Semaine -->

            <div class="col-md-3">

                <div class="card dashboard-card shadow border-0 bg-success text-white">

                    <div class="card-body text-center">

                        <h5 class="fw-bold">VENTES SEMAINES</h5>

                        <h2 class="fw-bold">{{ $venteSemaine }} FCFA</h2>

                    </div>

                </div>

            </div>

            <!-- Vente Mois -->

            <div class="col-md-3">

                <div class="card dashboard-card shadow border-0 bg-info text-dark">

                    <div class="card-body text-center">

                        <h5 class="fw-bold">VENTES MOIS</h5>

                        <h2 class="fw-bold">{{ $venteMois }} FCFA</h2>

                    </div>

                </div>

            </div>
            <div class="col-md-3">

                <div class="card dashboard-card shadow border-0 bg-light text-dark">

                    <div class="card-body text-center">

                        <h5 class="fw-bold">VENTES TRIMESTRE</h5>

                        <h2 class="fw-bold">{{ $venteTrimestre }} FCFA</h2>

                    </div>

                </div>

            </div>

            <!-- Total Produits -->

            <div class="col-md-3">

                <div class="card dashboard-card shadow  border-0 bg-danger text-white">

                    <div class="card-body text-center">

                        <h5 class="fw-bold">NOMBRE MEDICAMENTS</h5>

                        <h2 class="fw-bold">{{ $totalMedicaments }}</h2>

                    </div>

                </div>

            </div>

        </div>

    </div>
    {{-- // ACTIONS SUR LE TABEAU DE BORDS --}}
    <div class="row mt-4 g-3">

        <div class="col-md-3">
            <a href="{{ route('medicament.create') }}" class="text-decoration-none">
                <div class="card dashboard-card bg-primary text-white text-center p-3">
                    <h5> <span class="text-warning">➕ </span>AJOUTER PRODUIT</h5>
                </div>
            </a>
        </div>

        <div class="col-md-3">
            <a href="{{ route('medicaments.index') }}" class="text-decoration-none">
                <div class="card dashboard-card bg-success text-white text-center p-3">
                    <h5>📦 LISTES DES PRODUITS</h5>
                </div>
            </a>
        </div>

        <div class="col-md-3">
            <a href="{{ route('ventes.create') }}" class="text-decoration-none">
                <div class="card dashboard-card bg-warning text-dark text-center p-3">
                    <h5>💊 NOUVELLE VENTE</h5>
                </div>
            </a>
        </div>

        <div class="col-md-3">
            <a href="{{ route('ventes.index') }}" class="text-decoration-none">
                <div class="card dashboard-card bg-dark text-white text-center p-3">
                    <h5>📊 HISTORIQUE VENTES</h5>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('dashboard.top_produit') }}" class="text-decoration-none">
                <div class="card dashboard-card bg-dark text-white text-center p-3 border-warning">
                    <h5>VOIR TOP 5 DES PRODUITS</h5>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('dashboard.stockFaible') }}" class="text-decoration-none">
                <div class="card dashboard-card bg-info text-white text-center p-3">
                    <h5>VOIR STOCK FAIBLE</h5>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('dashboard.ruptureStock') }}" class="text-decoration-none">
                <div class="card dashboard-card bg-danger text-white text-center p-3">
                    <h5> 🚫 VOIR RUPTURE EN STOCK</h5>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('dashboard.expire') }}" class="text-decoration-none">
                <div class="card dashboard-card bg-primary text-white text-center p-3">
                    <h5>⏳EXPIRATION PROCHE</h5>
                </div>
            </a>
        </div>
    {{-- BESOIN D'AIDE ? BOUTON  --}}
        <div class="col-md-3">
            <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#aideModal"> ❓ BESOIN D'AIDE ?</button>
            <!-- MODAL AIDE -->
            <div class="modal fade" id="aideModal" tabindex="-1" aria-hidden="true">

                <div class="modal-dialog modal-lg modal-dialog-scrollable">

                    <div class="modal-content">

                        <!-- HEADER -->
                        <div class="modal-header bg-info text-white">

                            <h1 class="modal-title fs-4">
                                📘 Centre d'aide - GESTA PHARM
                            </h1>

                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal">
                            </button>

                        </div>

                        <!-- BODY -->
                        <div class="modal-body">

                            <h5 class="fw-bold text-primary">
                                🏥 Présentation du logiciel
                            </h5>

                            <p>
                                GESTA PHARM est une application de gestion de pharmacie
                                permettant :
                            </p>

                            <ul>
                                <li>Gestion des médicaments</li>
                                <li>Gestion des ventes</li>
                                <li>Gestion du stock</li>
                                <li>Recherche rapide des produits</li>
                                <li>Impression des tickets</li>
                                <li>Statistiques et tableau de bord</li>
                            </ul>

                            <hr>

                            <h5 class="fw-bold text-success">
                                💊 Gestion des médicaments
                            </h5>

                            <p>
                                Cette section permet :
                            </p>

                            <ul>
                                <li>Ajouter un médicament</li>
                                <li>Modifier un médicament</li>
                                <li>Supprimer un médicament</li>
                                <li>Consulter le stock disponible</li>
                                <li>Vérifier les dates d'expiration</li>
                            </ul>

                            <hr>

                            <h5 class="fw-bold text-warning">
                                🛒 Gestion des ventes
                            </h5>

                            <ul>
                                <li>Rechercher un médicament</li>
                                <li>Ajouter au panier</li>
                                <li>Contrôle automatique du stock</li>
                                <li>Validation des ventes</li>
                                <li>Impression des tickets</li>
                            </ul>

                            <hr>

                            <h5 class="fw-bold text-danger">
                                ⚠️ En cas de problème
                            </h5>

                            <p>
                                Si vous rencontrez un problème ou une incompréhension
                                dans l'utilisation du logiciel, veuillez contacter :
                            </p>

                            <div class="alert alert-light border">

                                📞 Support 1 :
                                <strong>+223 78 14 43 59</strong>

                                <br><br>

                                📞 Support 2 :
                                <strong>+223 95 57 60 40</strong>
                                <br><br>
                                📧 Support 3 :
                                <strong>ballaldialloubehd78@gmail.com</strong>

                            </div>

                            <hr>

                            <h5 class="fw-bold text-secondary">
                                👨‍💻 Développement
                            </h5>

                            <p>
                                Application développée par LE CABINET DE CONSULTING BALLAL DIALLOUBE.
                            </p>

                        </div>

                        <!-- FOOTER -->
                        <div class="modal-footer">

                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">

                                Fermer

                            </button>

                        </div>

                    </div>

                </div>

            </div>
        </div>


    </div>
@endsection
