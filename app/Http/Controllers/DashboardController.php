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
        $venteTrimestre = Vente::whereBetween(
            'date_vente',
            [
                Carbon::now()->firstOfQuarter(),
                Carbon::now()->lastOfQuarter()
            ]
        )->sum('total');
        // TOTAL MEDICAMENTS
        $totalMedicaments = Medicament::count();

        // ALERTES : ce qui demande une action au comptoir
        $nbRuptures = Medicament::where('stock', 0)->count();
        $nbStockFaible = Medicament::where('stock', '>', 0)->where('stock', '<=', 5)->count();
        $nbExpirations = Medicament::whereBetween('date_expiration', [now(), now()->addDays(30)])->count();

        return view('dashboard.index', compact(
            'venteJour',
            'venteSemaine',
            'venteMois',
            'venteTrimestre',
            'totalMedicaments',
            'nbRuptures',
            'nbStockFaible',
            'nbExpirations'
        ));
    }
    public function top_produit()
    {
        $topMedicaments = DB::table('medicament__vente')
            ->join('medicaments', 'medicaments.id', '=', 'medicament__vente.medicament_id')
            ->select(
                'medicaments.nom',
                DB::raw('SUM(medicament__vente.quantite) as total_quantite'),
                DB::raw('SUM(medicament__vente.quantite * medicaments.prix) as total_montant')
            )
            ->groupBy('medicaments.nom')
            ->orderByDesc('total_quantite')
            ->limit(5)
            ->get();
        return view('dashboard.top_produit', compact('topMedicaments'));
    }
    // METHODE STOCK FAIBLE
    public function stockFaible() {
        $stockFaible = Medicament::where('stock', '>', 0)->where('stock', '<=', 5)->get();
        return view('dashboard.stockFaible', compact('stockFaible'));
    }
    public function ruptureStock() {
        $ruptureStock = Medicament::where('stock', 0)->get();
        return view('dashboard.ruptureStock', compact('ruptureStock'));
    }
    public function expirationProche() {
        $expires = Medicament::whereBetween('date_expiration', [
            now(),
            now()->addDays(30)
        ])->get();
       return view('dashboard.expire', compact('expires'));
    }


}
