<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Registre de l'Ordonnancier — GESTA PHARM</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 12mm;
        }
        * {
            box-sizing: border-box;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        }
        body {
            color: #1e293b;
            font-size: 10px;
            line-height: 1.35;
            margin: 0;
            padding: 0;
            background: #fff;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #581c87;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }
        .brand h1 {
            margin: 0 0 2px 0;
            font-size: 18px;
            color: #581c87;
            font-weight: 800;
        }
        .brand p {
            margin: 1px 0;
            color: #64748b;
            font-size: 9px;
        }
        .doc-title {
            text-align: right;
        }
        .doc-title h2 {
            margin: 0;
            font-size: 14px;
            color: #0f172a;
            text-transform: uppercase;
        }
        .doc-title p {
            margin: 2px 0 0 0;
            font-size: 9px;
            color: #64748b;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 5px 6px;
            text-align: left;
        }
        th {
            background-color: #f1f5f9;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 8.5px;
            color: #475569;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-mono { font-family: monospace; }
        .badge-tab {
            font-weight: bold;
            font-size: 8px;
            padding: 1px 3px;
            border-radius: 3px;
            display: inline-block;
        }
        .badge-stup { background: #ffe4e6; color: #9f1239; border: 1px solid #f43f5e; }
        .badge-l1 { background: #fef3c7; color: #92400e; border: 1px solid #f59e0b; }
        .badge-l2 { background: #dbeafe; color: #1e40af; border: 1px solid #3b82f6; }
        .footer {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            page-break-inside: avoid;
        }
        .footer-box {
            width: 40%;
            border: 1px dashed #94a3b8;
            border-radius: 4px;
            padding: 8px;
            height: 70px;
        }
        .footer-box h4 {
            margin: 0 0 4px 0;
            font-size: 9px;
            text-transform: uppercase;
            color: #475569;
        }
        .no-print {
            position: fixed;
            top: 10px;
            right: 10px;
            background: #581c87;
            color: white;
            border: none;
            padding: 6px 14px;
            border-radius: 5px;
            font-size: 11px;
            font-weight: bold;
            cursor: pointer;
        }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <button onclick="window.print()" class="no-print">🖨️ Imprimer le Registre A4</button>

    <div class="header">
        <div class="brand">
            <h1>PHARMACIE GESTAPHARM</h1>
            <p>Officine Pharmaceutique Agréée • Kati Centre, Mali</p>
            <p>Tél : +223 78 14 43 59 • Pharmacien Responsable Titulaire</p>
        </div>
        <div class="doc-title">
            <h2>Registre Officiel de l'Ordonnancier</h2>
            <p>Conforme aux Bonnes Pratiques Pharmaceutiques et à la réglementation sanitaire</p>
            <p>Édition du <strong>{{ now()->format('d/m/Y à H:i') }}</strong> — {{ $lignes->count() }} inscriptions</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 10%;">N° Ordonnancier</th>
                <th style="width: 9%;">Date & Heure</th>
                <th style="width: 16%;">Médecin Prescripteur</th>
                <th style="width: 15%;">Patient Bénéficiaire</th>
                <th style="width: 18%;">Médicament & Tableau</th>
                <th style="width: 10%;">Lot Débité</th>
                <th class="text-center" style="width: 5%;">Qté</th>
                <th style="width: 17%;">Posologie & Pharmacien</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($lignes as $l)
                <tr>
                    <td class="font-mono" style="font-weight: bold; color: #581c87;">
                        {{ $l->numero_ordonnancier }}
                    </td>
                    <td>
                        {{ $l->date_delivrance->format('d/m/Y') }}<br>
                        <span style="color: #64748b; font-size: 8px;">{{ $l->date_delivrance->format('H:i') }}</span>
                    </td>
                    <td>
                        <strong>{{ $l->nom_prescripteur }}</strong><br>
                        <span style="color: #64748b; font-size: 8px;">{{ $l->specialite_prescripteur ?? 'Médecin' }}</span>
                    </td>
                    <td>
                        <strong>{{ $l->nom_patient }}</strong><br>
                        <span style="color: #64748b; font-size: 8px;">{{ $l->age_patient ? $l->age_patient . ' ans' : 'Âge non précisé' }}</span>
                    </td>
                    <td>
                        <strong>{{ $l->medicament->nom }}</strong><br>
                        @if($l->medicament->tableau && $l->medicament->tableau->value !== 'Non listé')
                            @php
                                $class = match($l->medicament->tableau->value) {
                                    'Stupéfiant' => 'badge-stup',
                                    'Liste I' => 'badge-l1',
                                    'Liste II' => 'badge-l2',
                                    default => ''
                                };
                            @endphp
                            <span class="badge-tab {{ $class }}">{{ $l->medicament->tableau->value }}</span>
                        @endif
                        @if($l->medicament->dci)
                            <span style="color: #64748b; font-style: italic; font-size: 8px;">({{ $l->medicament->dci }})</span>
                        @endif
                    </td>
                    <td class="font-mono">
                        {{ $l->lot?->numero_lot ?? 'N/A' }}<br>
                        @if($l->lot)
                            <span style="color: #64748b; font-size: 8px;">Exp: {{ $l->lot->date_expiration->format('m/Y') }}</span>
                        @endif
                    </td>
                    <td class="text-center font-mono font-bold">{{ $l->quantite_delivree }}</td>
                    <td>
                        <div>{{ $l->posologie ?? 'Conforme à l\'ordonnance' }}</div>
                        <div style="color: #64748b; font-size: 8px;">Par: {{ $l->pharmacien?->name ?? 'Officine' }}</div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 20px; color: #94a3b8;">
                        Aucune inscription enregistrée sur cette sélection.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <div class="footer-box">
            <h4>Visa du Pharmacien Titulaire</h4>
            <p style="color: #64748b; font-size: 8px; margin: 0;">Certifié exact et conforme aux registres de dispensation</p>
        </div>
        <div class="footer-box">
            <h4>Inspection Pharmaceutique & Sanitaire</h4>
            <p style="color: #64748b; font-size: 8px; margin: 0;">Date d'inspection, Visa et Observations</p>
        </div>
    </div>

</body>
</html>
