<?php

namespace App\Services\Analytics;

use App\Models\Medicament;
use App\Models\Vente;
use Illuminate\Support\Collection;

class DashboardKpiService
{
    /**
     * Récupère les indicateurs clés (KPIs) et alertes pour l'écran principal du tableau de bord.
     *
     * @return array{
     *     venteJour: float,
     *     venteSemaine: float,
     *     venteMois: float,
     *     totalMedicaments: int,
     *     evolutionJour: ?int,
     *     ruptures: Collection,
     *     stockFaible: Collection,
     *     expirations: Collection
     * }
     */
    public function recupererKpis(): array
    {
        $venteJour = (float) Vente::whereDate('date_vente', today())->sum('total');
        $venteHier = (float) Vente::whereDate('date_vente', today()->subDay())->sum('total');

        $venteSemaine = (float) Vente::whereBetween('date_vente', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ])->sum('total');

        $venteMois = (float) Vente::whereBetween('date_vente', [
            now()->startOfMonth(),
            now()->endOfMonth(),
        ])->sum('total');

        $totalMedicaments = Medicament::count();

        $ruptures = Medicament::enRupture()->orderBy('nom')->get(['id', 'nom']);
        $stockFaible = Medicament::stockFaible()->orderBy('stock')->get(['id', 'nom']);
        $expirations = Medicament::procheExpiration()->orderBy('date_expiration')->get(['id', 'nom']);

        return [
            'venteJour' => $venteJour,
            'venteSemaine' => $venteSemaine,
            'venteMois' => $venteMois,
            'totalMedicaments' => $totalMedicaments,
            'evolutionJour' => $this->evolution($venteJour, $venteHier),
            'ruptures' => $ruptures,
            'stockFaible' => $stockFaible,
            'expirations' => $expirations,
        ];
    }

    /**
     * Calcule le pourcentage d'évolution entre deux montants.
     */
    public function evolution(float $actuel, float $precedent): ?int
    {
        if ($precedent <= 0) {
            return null;
        }

        return (int) round((($actuel - $precedent) / $precedent) * 100);
    }
}
