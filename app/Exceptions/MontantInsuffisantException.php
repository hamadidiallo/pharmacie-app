<?php

namespace App\Exceptions;

/**
 * Le montant reçu ne couvre pas le total à payer.
 *
 * Exception de domaine : elle ne dépend pas de la couche HTTP. C'est le
 * contrôleur qui la traduit en erreur de formulaire sur le champ concerné.
 */
class MontantInsuffisantException extends VenteException
{
    public const CHAMP = 'montant_recu';

    public function __construct(string $message = 'Le montant reçu est inférieur au total à payer.')
    {
        parent::__construct($message, 422);
    }
}
