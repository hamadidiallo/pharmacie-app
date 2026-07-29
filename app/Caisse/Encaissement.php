<?php

namespace App\Caisse;

use App\Exceptions\MontantInsuffisantException;

/**
 * Règlement d'une vente : mode de paiement et, pour les espèces, montant reçu
 * et monnaie à rendre.
 */
final class Encaissement
{
    private function __construct(
        public readonly string $mode,
        public readonly ?float $montantRecu,
        public readonly ?float $monnaieRendue,
    ) {
    }

    /**
     * @throws MontantInsuffisantException si le montant reçu ne couvre pas le total
     */
    public static function pour(string $mode, float $total, ?float $montantRecu = null): self
    {
        // hors espèces, le montant est réglé au centime : ni reçu ni monnaie
        if ($mode !== 'especes') {
            return new self($mode, null, null);
        }

        $recu = (float) ($montantRecu ?? 0);

        if ($recu < $total) {
            throw new MontantInsuffisantException;
        }

        return new self($mode, $recu, $recu - $total);
    }

    /** Attributs à enregistrer sur la vente. */
    public function attributs(): array
    {
        return [
            'mode_paiement' => $this->mode,
            'montant_recu' => $this->montantRecu,
            'monnaie_rendue' => $this->monnaieRendue,
        ];
    }
}
