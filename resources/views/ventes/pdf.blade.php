<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ticket</title>

    <style>
        body {
            font-family: Arial;
            font-size: 12px;
        }

        .center {
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table, th, td {
            border: 1px solid #000;
        }

        th, td {
            padding: 5px;
            text-align: center;
        }

        .total {
            text-align: right;
            margin-top: 10px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="center">
        <h3>PHARMACIE GESTAPHARM</h3>
        <p>Ticket de caisse</p>
    </div>

    <hr>

    <p><strong>Ticket N° :</strong> {{ $vente->id }}</p>
    <p><strong>Date :</strong> {{ $vente->date_vente }}</p>
    <p><strong>Heure :</strong> {{ $vente->created_at->format('h:i:m') }}</p>

    <table>
        <thead>
            <tr>
                <th>Produit</th>
                <th>Qté</th>
                <th>Prix</th>
                <th>Total</th>
            </tr>
        </thead>

        <tbody>
            @foreach($vente->medicaments as $m)
                <tr>
                    <td>{{ $m->nom }}</td>
                    <td>{{ $m->pivot->quantite }}</td>
                    <td>{{ $m->pivot->prix }}</td>
                    <td>{{ $m->pivot->sous_total }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="total">
        TOTAL : {{ $vente->total }} FCFA
    </p>

    <hr>

    <p class="center">Merci pour votre confiance</p>

</body>
</html>
