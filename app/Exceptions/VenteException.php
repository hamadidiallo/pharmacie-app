<?php

namespace App\Exceptions;

use Exception;

/**
 * Erreur métier survenue pendant l'enregistrement d'une vente.
 * Le code de l'exception porte le statut HTTP à renvoyer au client.
 */
class VenteException extends Exception
{
}
