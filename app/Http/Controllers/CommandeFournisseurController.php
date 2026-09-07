<?php

namespace App\Http\Controllers;

use App\Enums\StatutCommandeFournisseur;
use App\Models\CommandeFournisseur;
use App\Models\Fournisseur;
use App\Models\Medicament;
use App\Services\Approvisionnement\GenerateurCommandeAutomatique;
use App\Services\Approvisionnement\ReceptionCommandeAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CommandeFournisseurController extends Controller
{
    public function __construct(
        protected GenerateurCommandeAutomatique $generateur,
        protected ReceptionCommandeAction $receptionAction
    ) {}

    public function index(Request $request)
    {
        $statut = $request->query('statut');
        $fournisseurId = $request->query('fournisseur_id');

        $commandes = CommandeFournisseur::query()
            ->with(['fournisseur', 'user', 'lignes'])
            ->when($statut, fn ($q) => $q->where('statut', $statut))
            ->when($fournisseurId, fn ($q) => $q->where('fournisseur_id', $fournisseurId))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('commandes.index', [
            'commandes' => $commandes,
            'statuts' => StatutCommandeFournisseur::cases(),
            'fournisseurs' => Fournisseur::actif()->orderBy('nom')->get(),
            'statutActif' => $statut,
            'fournisseurActif' => $fournisseurId,
        ]);
    }

    public function create()
    {
        return view('commandes.create', [
            'fournisseurs' => Fournisseur::actif()->orderBy('nom')->get(),
            'medicaments' => Medicament::orderBy('nom')->get(['id', 'nom', 'stock', 'stock_securite', 'prix']),
        ]);
    }

    public function store(Request $request)
    {
        $donnees = $request->validate([
            'fournisseur_id' => 'required|exists:fournisseurs,id',
            'date_commande' => 'required|date',
            'date_livraison_prevue' => 'nullable|date|after_or_equal:date_commande',
            'notes' => 'nullable|string|max:500',
            'lignes' => 'required|array|min:1',
            'lignes.*.medicament_id' => 'required|exists:medicaments,id',
            'lignes.*.quantite_commandee' => 'required|integer|min:1',
            'lignes.*.prix_achat_unitaire_estime' => 'required|numeric|min:0',
        ]);

        $commande = DB::transaction(function () use ($donnees) {
            $cmd = CommandeFournisseur::create([
                'fournisseur_id' => $donnees['fournisseur_id'],
                'user_id' => Auth::id(),
                'reference' => CommandeFournisseur::genererReference(),
                'date_commande' => $donnees['date_commande'],
                'date_livraison_prevue' => $donnees['date_livraison_prevue'] ?? null,
                'statut' => StatutCommandeFournisseur::Brouillon,
                'total_estime' => 0,
                'notes' => $donnees['notes'] ?? null,
            ]);

            $total = 0;
            foreach ($donnees['lignes'] as $ligne) {
                $st = ((int) $ligne['quantite_commandee']) * ((float) $ligne['prix_achat_unitaire_estime']);
                $total += $st;

                $cmd->lignes()->create([
                    'medicament_id' => $ligne['medicament_id'],
                    'quantite_commandee' => $ligne['quantite_commandee'],
                    'quantite_recue' => 0,
                    'prix_achat_unitaire_estime' => $ligne['prix_achat_unitaire_estime'],
                ]);
            }

            $cmd->update(['total_estime' => $total]);

            return $cmd;
        });

        return redirect()->route('commandes.show', $commande)
            ->with('alert', "Bon de commande « {$commande->reference} » créé avec succès.");
    }

    public function show(CommandeFournisseur $commande)
    {
        $commande->load(['fournisseur', 'user', 'lignes.medicament', 'lignes.lot']);

        return view('commandes.show', [
            'commande' => $commande,
        ]);
    }

    public function bonCommande(CommandeFournisseur $commande)
    {
        $commande->load(['fournisseur', 'user', 'lignes.medicament']);

        return view('commandes.bon-commande', [
            'commande' => $commande,
        ]);
    }

    public function envoyer(CommandeFournisseur $commande)
    {
        if (!$commande->estModifiable()) {
            return back()->with('alert', 'Cette commande ne peut plus être modifiée.');
        }

        $commande->update(['statut' => StatutCommandeFournisseur::Envoyee]);

        return back()->with('alert', "Le bon de commande {$commande->reference} a été marqué comme envoyé au grossiste.");
    }

    /**
     * Analyse des ruptures et page de suggestion automatique.
     */
    public function suggerer()
    {
        $besoins = $this->generateur->detecterBesoinsReassort();
        $fournisseurs = Fournisseur::actif()->orderBy('nom')->get();

        return view('commandes.suggerer', [
            'besoins' => $besoins,
            'fournisseurs' => $fournisseurs,
            'totalEstime' => $besoins->sum('sous_total_estime'),
        ]);
    }

    /**
     * Génération en 1-clic de la commande automatique.
     */
    public function genererAutomatique(Request $request)
    {
        $donnees = $request->validate([
            'fournisseur_id' => 'required|exists:fournisseurs,id',
            'medicaments' => 'nullable|array',
            'medicaments.*' => 'integer|exists:medicaments,id',
        ]);

        $fournisseur = Fournisseur::findOrFail($donnees['fournisseur_id']);

        $commande = $this->generateur->genererBonCommande(
            $fournisseur,
            Auth::user(),
            $donnees['medicaments'] ?? null
        );

        return redirect()->route('commandes.show', $commande)
            ->with('alert', "Bon de commande automatique {$commande->reference} généré avec succès pour {$fournisseur->nom}.");
    }

    /**
     * Écran de saisie du Bon de Livraison (BL) lors de l'arrivée du colis.
     */
    public function receptionnerForm(CommandeFournisseur $commande)
    {
        if (!$commande->peutEtreRecue()) {
            return redirect()->route('commandes.show', $commande)
                ->with('alert', 'Cette commande ne peut pas être réceptionnée.');
        }

        $commande->load(['fournisseur', 'lignes.medicament']);

        return view('commandes.reception', [
            'commande' => $commande,
        ]);
    }

    /**
     * Validation et enregistrement de l'arrivage de marchandises.
     */
    public function enregistrerReception(Request $request, CommandeFournisseur $commande)
    {
        $donnees = $request->validate([
            'numero_bl' => 'required|string|max:80',
            'total_facture' => 'nullable|numeric|min:0',
            'lignes' => 'required|array|min:1',
            'lignes.*.ligne_id' => 'required|exists:commande_fournisseur_lignes,id',
            'lignes.*.quantite_recue' => 'required|integer|min:0',
            'lignes.*.numero_lot' => 'nullable|string|max:80',
            'lignes.*.date_expiration' => 'nullable|date|after:today',
            'lignes.*.prix_achat_facture' => 'nullable|numeric|min:0',
        ]);

        $commandeRecue = $this->receptionAction->execute(
            $commande,
            $donnees['numero_bl'],
            $donnees['lignes'],
            isset($donnees['total_facture']) ? (float) $donnees['total_facture'] : null
        );

        $statutMsg = $commandeRecue->statut === StatutCommandeFournisseur::Recue
            ? 'La commande a été intégralement réceptionnée et soldée.'
            : 'La commande a été partiellement réceptionnée (articles restants en attente).';

        return redirect()->route('commandes.show', $commandeRecue)
            ->with('alert', "Arrivage BL n°{$donnees['numero_bl']} validé avec succès ! Les lots et stocks ont été mis à jour. {$statutMsg}");
    }
}
