<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bordereau {{ $bordereau->reference }} — GESTA PHARM</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 12mm 15mm 12mm;
        }
        * {
            box-sizing: border-box;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        }
        body {
            color: #1e293b;
            font-size: 11px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
            background: #fff;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0f766e;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        .brand h1 {
            margin: 0 0 4px 0;
            font-size: 20px;
            color: #0f766e;
            font-weight: 800;
            letter-spacing: 0.5px;
        }
        .brand p {
            margin: 2px 0;
            color: #64748b;
            font-size: 10px;
        }
        .doc-meta {
            text-align: right;
        }
        .doc-meta .ref {
            font-family: monospace;
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 4px;
        }
        .destinataire {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
        }
        .destinataire h3 {
            margin: 0 0 4px 0;
            font-size: 11px;
            text-transform: uppercase;
            color: #475569;
        }
        .destinataire .org-name {
            font-size: 14px;
            font-weight: bold;
            color: #0f766e;
        }
        .recap-boxes {
            display: flex;
            gap: 12px;
            margin-bottom: 15px;
        }
        .box {
            flex: 1;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 8px 12px;
            background: #fff;
        }
        .box .label {
            font-size: 9px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 600;
        }
        .box .val {
            font-size: 15px;
            font-weight: 800;
            color: #0f172a;
            margin-top: 3px;
        }
        .box.primary {
            background: #f0fdf4;
            border-color: #86efac;
        }
        .box.primary .val {
            color: #166534;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10px;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background-color: #f1f5f9;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 9px;
            color: #475569;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-mono { font-family: monospace; }
        .total-row {
            background-color: #f8fafc;
            font-weight: bold;
            font-size: 11px;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 35px;
            page-break-inside: avoid;
        }
        .sig-box {
            width: 45%;
            border: 1px dashed #94a3b8;
            border-radius: 6px;
            padding: 12px;
            height: 90px;
        }
        .sig-box h4 {
            margin: 0 0 6px 0;
            font-size: 10px;
            text-transform: uppercase;
            color: #475569;
        }
        .no-print {
            position: fixed;
            top: 15px;
            right: 15px;
            background: #0f766e;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <button onclick="window.print()" class="no-print">🖨️ Imprimer le Bordereau</button>

    <div class="header">
        <div class="brand">
            <h1>PHARMACIE GESTAPHARM</h1>
            <p>Officine Pharmaceutique Agréée • Kati Centre, Mali</p>
            <p>Tél : +223 78 14 43 59 / 20 22 .. .. • NIF : 087612344A</p>
            <p>Dr Pharmacien Responsable Titulaire</p>
        </div>
        <div class="doc-meta">
            <div class="ref">{{ $bordereau->reference }}</div>
            <p>Date d'émission : <strong>{{ $bordereau->created_at->format('d/m/Y') }}</strong></p>
            <p>Période : <strong>Du {{ $bordereau->periode_debut->format('d/m/Y') }} au {{ $bordereau->periode_fin->format('d/m/Y') }}</strong></p>
        </div>
    </div>

    <div class="destinataire">
        <div>
            <h3>Organisme Payeur / Mutuelle Conventionnée :</h3>
            <div class="org-name">{{ $bordereau->assurance->nom }} (Code : {{ $bordereau->assurance->code }})</div>
            <p style="margin: 3px 0 0 0; color: #64748b; font-size: 10px;">
                {{ $bordereau->assurance->adresse ?? 'Siège Régional Bamako / Kati' }} •
                Tél: {{ $bordereau->assurance->telephone ?? 'N/A' }}
            </p>
        </div>
        <div style="text-align: right;">
            <div style="font-size: 11px; font-weight: bold; color: #334155;">BORDEREAU RÉCAPITULATIF</div>
            <div style="font-size: 9px; color: #64748b;">Conforme à la réglementation Tiers Payant</div>
        </div>
    </div>

    <div class="recap-boxes">
        <div class="box">
            <div class="label">Nombre de dossiers / factures</div>
            <div class="val">{{ $bordereau->nombre_dossiers }} ordonnances</div>
        </div>
        <div class="box">
            <div class="label">Montant Brut des délivrances</div>
            <div class="val">{{ number_format($bordereau->ventes->sum('total'), 0, ',', ' ') }} FCFA</div>
        </div>
        <div class="box">
            <div class="label">Part Assurés (Encaissée)</div>
            <div class="val">{{ number_format($bordereau->ventes->sum('part_patient'), 0, ',', ' ') }} FCFA</div>
        </div>
        <div class="box primary">
            <div class="label">Total Net Réclamé à l'Assurance</div>
            <div class="val">{{ number_format($bordereau->montant_total, 0, ',', ' ') }} FCFA</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">N°</th>
                <th style="width: 14%;">Ticket / Date</th>
                <th style="width: 22%;">Bénéficiaire / N° Assuré</th>
                <th style="width: 27%;">Médicaments Délivrés</th>
                <th class="text-right" style="width: 11%;">Brut (FCFA)</th>
                <th class="text-center" style="width: 7%;">Taux</th>
                <th class="text-right" style="width: 14%;">Part Mutuelle</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($bordereau->ventes as $index => $v)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <strong class="font-mono">TCK-{{ $v->id }}</strong><br>
                        <span style="color: #64748b; font-size: 9px;">{{ $v->date_vente->format('d/m/Y') }}</span>
                    </td>
                    <td>
                        <strong>{{ $v->nom_assure ?? 'Assuré' }}</strong><br>
                        <span class="font-mono" style="font-size: 9px; color: #475569;">Matr: {{ $v->matricule_assure }}</span>
                    </td>
                    <td style="font-size: 9px; color: #334155;">
                        @foreach($v->medicaments as $med)
                            <div>• {{ $med->nom }} (x{{ $med->pivot->quantite }})</div>
                        @endforeach
                    </td>
                    <td class="text-right font-mono">{{ number_format($v->total, 0, ',', ' ') }}</td>
                    <td class="text-center"><strong>{{ (int)$v->taux_couverture }}%</strong></td>
                    <td class="text-right font-mono" style="font-weight: bold;">{{ number_format($v->part_assurance, 0, ',', ' ') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4" style="text-align: right; text-transform: uppercase;">Total Général Réclamé :</td>
                <td class="text-right font-mono">{{ number_format($bordereau->ventes->sum('total'), 0, ',', ' ') }}</td>
                <td></td>
                <td class="text-right font-mono" style="color: #0f766e; font-size: 12px;">{{ number_format($bordereau->montant_total, 0, ',', ' ') }} FCFA</td>
            </tr>
        </tfoot>
    </table>

    <div class="signatures">
        <div class="sig-box">
            <h4>Pour la Pharmacie GestaPharm</h4>
            <p style="font-size: 9px; color: #64748b; margin: 0;">Le Pharmacien Responsable (Visa & Cachet)</p>
        </div>
        <div class="sig-box">
            <h4>Accusé de réception Organisme</h4>
            <p style="font-size: 9px; color: #64748b; margin: 0;">Date, Nom du réceptionnaire & Cachet de l'Assurance</p>
        </div>
    </div>

</body>
</html>
