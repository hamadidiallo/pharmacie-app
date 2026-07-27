@extends('layout')

@section('content')
    <nav>@include('app.menu')</nav>
    <div class="container mt-4 d-flex justify-content-center">

        <div class="card shadow p-4" id="ticket" style="width: 400px;">

            <!-- HEADER -->
            <div class="text-center">
                <h4 class="fw-bold">PHARMACIE GESTAPHARM</h4>
                <small>Tel: +223 78 14 43 59</small><br>
                <small>KATI, MALI</small>
            </div>

            <hr>

            <!-- INFO VENTE -->
            <div>
                <p><strong>Ticket N° :</strong> TCK-{{ $vente->id }}</p>
                <p><strong>Date :</strong> {{ $vente->date_vente }}</p>
                <p><strong>Heure :</strong> {{ $vente->created_at->format('H:i:s') }}</p>
            </div>

            <hr>

            <!-- TABLE -->
            <table class="table table-sm text-center">

                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Qté</th>
                        <th>Prix</th>
                        <th>Total</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach ($vente->medicaments as $m)
                        <tr>
                            <td class="text-start">{{ $m->nom }}</td>
                            <td>{{ $m->pivot->quantite }}</td>
                            <td>{{ $m->pivot->prix }}</td>
                            <td>{{ $m->pivot->sous_total }}</td>
                        </tr>
                    @endforeach

                </tbody>

            </table>

            <hr>

            <!-- TOTAL -->
            <div class="text-end">
                <h5 class="fw-bold">
                    TOTAL : {{ $vente->total }} FCFA
                </h5>
            </div>

            <hr>

            <!-- FOOTER -->
            <div class="text-center">
                <small>Merci pour votre confiance</small>
            </div>
            <p>-----------------------------------------------------</p>

            <!-- BUTTON -->
            <div class="d-flex justify-content-center gap-5">
                <div class="mt-3 text-center no-print">

                    <button onclick="window.print()" class="btn btn-dark w-100">
                        🖨️ Imprimer le ticket
                    </button>
                    <a href="{{ route('ventes.pdf', $vente->id) }}" class="btn btn-info w-100 mt-2">
                        📄 Télécharger PDF
                    </a>

                </div>

            </div>


        </div>

    </div>
@endsection
{{-- // CSS SHOW TICKET POUR IMPRESSION --}}
<style>
    #ticket {
        width: 80mm;
        margin: auto;
        font-family: monospace;
        font-size: 12px;
        background: white;
    }

    @media print {

        @page {
            size: 80mm auto;
            margin: 0mm;
        }

        html,
        body {
            width: 80mm;
            height: auto;
            margin: 0 !important;
            padding: 0 !important;
            overflow: hidden;
            background: white;
        }

        body * {
            visibility: hidden;
        }

        #ticket,
        #ticket * {
            visibility: visible;
        }

        #ticket {
            position: absolute;
            left: 0;
            top: 0;
            width: 80mm;
            margin: 0;
            padding: 5px;
            box-shadow: none !important;
            border: none !important;
        }

        .no-print {
            display: none !important;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th,
        table td {
            font-size: 11px;
            padding: 2px;
        }

        hr {
            margin: 3px 0;
            border-top: 1px dashed black;
        }
    }
</style>
