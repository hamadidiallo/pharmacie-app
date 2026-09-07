<?php

namespace App\Http\Controllers;

use App\Enums\StatutSessionCaisse;
use App\Models\SessionCaisse;
use App\Services\Caisse\SessionCaisseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionCaisseController extends Controller
{
    public function __construct(
        protected SessionCaisseService $caisseService
    ) {}

    /**
     * Liste des sessions de caisse et état de la session active.
     */
    public function index(Request $request)
    {
        $filtre = $request->query('filtre', 'toutes');
        $sessionActive = $this->caisseService->sessionActive();

        if ($sessionActive) {
            $sessionActive->synchroniserTotaux();
        }

        $sessions = SessionCaisse::query()
            ->with(['user'])
            ->when($filtre === 'ouvertes', fn ($q) => $q->where('statut', StatutSessionCaisse::Ouverte))
            ->when($filtre === 'cloturees', fn ($q) => $q->where('statut', StatutSessionCaisse::Cloturee))
            ->orderByDesc('date_ouverture')
            ->paginate(12)
            ->withQueryString();

        return view('caisse.sessions.index', [
            'sessions' => $sessions,
            'sessionActive' => $sessionActive,
            'filtre' => $filtre,
            'coupures' => SessionCaisseService::COUPURES_FCFA,
        ]);
    }

    /**
     * Ouverture d'une nouvelle session de caisse avec fond de caisse.
     */
    public function store(Request $request)
    {
        $donnees = $request->validate([
            'fond_caisse_ouverture' => 'required|numeric|min:0',
            'observations' => 'nullable|string|max:500',
        ]);

        $session = $this->caisseService->ouvrir(
            Auth::user(),
            (float) $donnees['fond_caisse_ouverture'],
            $donnees['observations'] ?? null
        );

        return redirect()->route('caisse.sessions.index')
            ->with('alert', "Session de caisse n°{$session->id} ouverte avec succès (Fond de caisse : " . number_format($session->fond_caisse_ouverture, 0, ',', ' ') . " FCFA).");
    }

    /**
     * Fiche détaillée d'une session de caisse (ventes, dépenses, billetage).
     */
    public function show(SessionCaisse $session)
    {
        $session->load(['user', 'mouvements.user', 'ventes.user']);
        $session->synchroniserTotaux();

        return view('caisse.sessions.show', [
            'session' => $session,
            'coupures' => SessionCaisseService::COUPURES_FCFA,
        ]);
    }

    /**
     * Formulaire interactif de clôture avec saisie du billetage.
     */
    public function clotureForm(SessionCaisse $session)
    {
        if (!$session->estOuverte()) {
            return redirect()->route('caisse.sessions.show', $session)
                ->with('alert', 'Cette session de caisse est déjà clôturée.');
        }

        $session->synchroniserTotaux();

        return view('caisse.sessions.cloture', [
            'session' => $session,
            'coupures' => SessionCaisseService::COUPURES_FCFA,
        ]);
    }

    /**
     * Validation de la clôture et enregistrement de l'écart.
     */
    public function cloturer(Request $request, SessionCaisse $session)
    {
        if (!$session->estOuverte()) {
            return redirect()->route('caisse.sessions.show', $session)
                ->with('alert', 'Cette session est déjà clôturée.');
        }

        $donnees = $request->validate([
            'billetage' => 'nullable|array',
            'observations' => 'nullable|string|max:1000',
        ]);

        $sessionCloturee = $this->caisseService->cloturer(
            $session,
            $donnees['billetage'] ?? [],
            $donnees['observations'] ?? null
        );

        $ecart = $sessionCloturee->ecart_caisse;
        $signe = $ecart >= 0 ? '+' : '';
        $messageEcart = "Écart de caisse : {$signe}" . number_format($ecart, 0, ',', ' ') . " FCFA.";

        return redirect()->route('caisse.sessions.rapport-z', $sessionCloturee)
            ->with('alert', "La session de caisse n°{$sessionCloturee->id} a été clôturée avec succès. {$messageEcart}");
    }

    /**
     * Rapport Z Journalier imprimable (Ticket 80 mm & Format officiel A4).
     */
    public function rapportZ(SessionCaisse $session)
    {
        $session->load(['user', 'mouvements.user', 'ventes']);
        $session->synchroniserTotaux();

        return view('caisse.sessions.rapport-z', [
            'session' => $session,
            'coupures' => SessionCaisseService::COUPURES_FCFA,
        ]);
    }

    /**
     * Enregistrement d'un mouvement d'espèces (sortie/dépense ou apport).
     */
    public function storeMouvement(Request $request, SessionCaisse $session)
    {
        $donnees = $request->validate([
            'type' => 'required|in:sortie,entree',
            'montant' => 'required|numeric|min:1',
            'motif' => 'required|string|max:255',
            'beneficiaire' => 'nullable|string|max:150',
        ]);

        $this->caisseService->ajouterMouvement(
            $session,
            $donnees['type'],
            (float) $donnees['montant'],
            $donnees['motif'],
            $donnees['beneficiaire'] ?? null,
            Auth::id()
        );

        $libelleType = $donnees['type'] === 'sortie' ? 'Dépense / Sortie' : 'Apport';
        return back()->with('alert', "{$libelleType} de " . number_format($donnees['montant'], 0, ',', ' ') . " FCFA enregistrée avec succès.");
    }
}
