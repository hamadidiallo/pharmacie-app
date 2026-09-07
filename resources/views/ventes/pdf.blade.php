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

    <p><strong>Ticket N° :</strong> TCK-{{ $vente->id }}</p>
    <p><strong>Date :</strong> {{ $vente->date_vente->format('d/m/Y') }}</p>
    <p><strong>Heure :</strong> {{ $vente->created_at->format('H:i:s') }}</p>

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
        TOTAL BRUT : {{ number_format($vente->total, 0, ',', ' ') }} FCFA
    </p>

    @if($vente->estPriseEnCharge())
        <div style="background-color: #fef3c7; border: 1px dashed #d97706; padding: 6px; margin-top: 5px; font-size: 11px;">
            <p style="margin: 0;"><strong>Prise en charge {{ $vente->assurance?->code ?? 'Assurance' }} ({{ (int)$vente->taux_couverture }}%) :</strong> -{{ number_format($vente->part_assurance, 0, ',', ' ') }} FCFA</p>
            <p style="margin: 3px 0 0 0; font-size: 12px; font-weight: bold; color: #0f766e;"><strong>NET PAYÉ PAR L'ASSURÉ :</strong> {{ number_format($vente->part_patient, 0, ',', ' ') }} FCFA</p>
            <p style="margin: 3px 0 0 0; font-size: 10px; color: #475569;">Matricule : {{ $vente->matricule_assure }} {{ $vente->nom_assure ? '— ' . $vente->nom_assure : '' }}</p>
        </div>
    @endif

    @if($vente->ordonnancierLignes->isNotEmpty())
        <div style="background-color: #f3e8ff; border: 1px dashed #9333ea; padding: 5px; margin-top: 5px; font-size: 10px;">
            <p style="margin: 0;"><strong>Prescription Réglementaire :</strong></p>
            <p style="margin: 2px 0 0 0;">Dr {{ $vente->ordonnancierLignes->first()->nom_prescripteur }} • Patient : {{ $vente->ordonnancierLignes->first()->nom_patient }}</p>
            <p style="margin: 2px 0 0 0;">N° Ordonnancier : {{ $vente->ordonnancierLignes->pluck('numero_ordonnancier')->join(', ') }}</p>
        </div>
    @endif

    <p style="margin-top: 10px;"><strong>Paiement :</strong> {{ $vente->libelleModePaiement() }}</p>

    @if ($vente->montant_recu !== null)
        <p>
            Reçu : {{ number_format($vente->montant_recu, 0, ',', ' ') }} FCFA ·
            Rendu : {{ number_format($vente->monnaie_rendue, 0, ',', ' ') }} FCFA
        </p>
    @endif

    <hr>

    <p class="center">Merci pour votre confiance</p>

</body>
</html>
