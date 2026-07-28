<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventes', function (Blueprint $table) {
            // especes | mobile_money | carte
            $table->string('mode_paiement', 20)->default('especes')->after('total');
            // renseignés pour les espèces uniquement
            $table->decimal('montant_recu', 10, 2)->nullable()->after('mode_paiement');
            $table->decimal('monnaie_rendue', 10, 2)->nullable()->after('montant_recu');
        });
    }

    public function down(): void
    {
        Schema::table('ventes', function (Blueprint $table) {
            $table->dropColumn(['mode_paiement', 'montant_recu', 'monnaie_rendue']);
        });
    }
};
