<?php

namespace App\Http\Controllers;

use App\Services\Analytics\DashboardKpiService;
use App\Services\Analytics\StatistiquesVenteService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardKpiService $kpiService,
        private readonly StatistiquesVenteService $statsService
    ) {
    }

    public function index()
    {
        $kpis = $this->kpiService->recupererKpis();
        $serieCa = $this->statsService->caParJour(7);

        return view('dashboard.index', [
            ...$kpis,
            'serieCa' => $serieCa,
        ]);
    }

    public function statistiques(Request $request)
    {
        $periode = (string) $request->query('periode', 'mois');
        $statistiques = $this->statsService->analyserPeriode($periode);

        return view('dashboard.statistiques', $statistiques);
    }
}
