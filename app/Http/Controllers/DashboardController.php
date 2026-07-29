<?php

namespace App\Http\Controllers;

use App\Models\Medicament;
use App\Models\Vente;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // STATISTIQUES VENTES
        $venteJour = Vente::whereDate('date_vente', today())->sum('total');
        $venteHier = Vente::whereDate('date_vente', today()->subDay())->sum('total');
        $venteSemaine = Vente::whereBetween(
            'date_vente',
            [
                now()->startOfWeek(),
                now()->endOfWeek()
            ]
        )->sum('total');
        $venteMois = Vente::whereBetween(
            'date_vente',
            [
                now()->startOfMonth(),
                now()->endOfMonth()
            ]
        )->sum('total');
        // TOTAL MEDICAMENTS
        $totalMedicaments = Medicament::count();

        // ALERTES : ce qui demande une action au comptoir
        $ruptures = Medicament::enRupture()->orderBy('nom')->get(['id', 'nom']);
        $stockFaible = Medicament::stockFaible()->orderBy('stock')->get(['id', 'nom']);
        $expirations = Medicament::procheExpiration()->orderBy('date_expiration')->get(['id', 'nom']);

        return view('dashboard.index', [
            'venteJour' => $venteJour,
            'venteSemaine' => $venteSemaine,
            'venteMois' => $venteMois,
            'totalMedicaments' => $totalMedicaments,
            'evolutionJour' => $this->evolution($venteJour, $venteHier),
            'serieCa' => $this->caParJour(7),
            'ruptures' => $ruptures,
            'stockFaible' => $stockFaible,
            'expirations' => $expirations,
        ]);
    }

    public function statistiques(Request $request)
    {
        $periode = in_array($request->query('periode'), ['semaine', 'mois', 'trimestre'], true)
            ? $request->query('periode')
            : 'mois';

        [$debut, $fin, $libelle] = match ($periode) {
            'semaine' => [now()->startOfWeek(), now()->endOfWeek(), 'Cette semaine'],
            'trimestre' => [Carbon::now()->firstOfQuarter(), Carbon::now()->lastOfQuarter(), 'Ce trimestre'],
            default => [now()->startOfMonth(), now()->endOfMonth(), now()->translatedFormat('F Y')],
        };

        $topMedicaments = DB::table('medicament__vente')
            ->join('medicaments', 'medicaments.id', '=', 'medicament__vente.medicament_id')
            ->join('ventes', 'ventes.id', '=', 'medicament__vente.vente_id')
            ->whereBetween('ventes.date_vente', [$debut, $fin])
            ->select(
                'medicaments.nom',
                DB::raw('SUM(medicament__vente.quantite) as total_quantite'),
                DB::raw('SUM(medicament__vente.sous_total) as total_montant')
            )
            ->groupBy('medicaments.nom')
            ->orderByDesc('total_quantite')
            ->limit(5)
            ->get();

        $surveillance = Medicament::aSurveiller()
            ->orderBy('stock')
            ->limit(15)
            ->get();

        return view('dashboard.statistiques', [
            'periode' => $periode,
            'libellePeriode' => $libelle,
            'caPeriode' => Vente::whereBetween('date_vente', [$debut, $fin])->sum('total'),
            'nbVentes' => Vente::whereBetween('date_vente', [$debut, $fin])->count(),
            'topMedicaments' => $topMedicaments,
            'serieCa' => $this->caParJour(7),
            'surveillance' => $surveillance,
        ]);
    }

    // METHODE STOCK FAIBLE
    public function stockFaible()
    {
        $stockFaible = Medicament::stockFaible()->orderBy('stock')->get();
        return view('dashboard.stockFaible', compact('stockFaible'));
    }

    public function ruptureStock()
    {
        $ruptureStock = Medicament::enRupture()->orderBy('nom')->get();
        return view('dashboard.ruptureStock', compact('ruptureStock'));
    }

    public function expirationProche()
    {
        $expires = Medicament::procheExpiration()->orderBy('date_expiration')->get();
        return view('dashboard.expire', compact('expires'));
    }

    /**
     * Chiffre d'affaires jour par jour sur les N derniers jours, aujourd'hui inclus.
     * Les jours sans vente sont présents avec un total à zéro.
     *
     * @return array<int,array{jour: string, date: string, total: float}>
     */
    private function caParJour(int $nbJours): array
    {
        $debut = today()->subDays($nbJours - 1);

        $totaux = Vente::whereBetween('date_vente', [$debut->copy()->startOfDay(), today()->endOfDay()])
            ->get(['date_vente', 'total'])
            ->groupBy(fn (Vente $vente) => $vente->date_vente->toDateString())
            ->map(fn ($ventes) => (float) $ventes->sum('total'));

        $serie = [];

        for ($i = 0; $i < $nbJours; $i++) {

            $jour = $debut->copy()->addDays($i);

            $serie[] = [
                'jour' => $jour->translatedFormat('D'),
                'date' => $jour->toDateString(),
                'total' => $totaux->get($jour->toDateString(), 0.0),
            ];
        }

        return $serie;
    }

    private function evolution(float $actuel, float $precedent): ?int
    {
        if ($precedent <= 0) {
            return null;
        }

        return (int) round((($actuel - $precedent) / $precedent) * 100);
    }
}
