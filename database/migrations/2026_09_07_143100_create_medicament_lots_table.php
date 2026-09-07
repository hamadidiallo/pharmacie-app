<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicament_lots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicament_id')->constrained('medicaments')->cascadeOnDelete();
            $table->string('numero_lot', 80);
            $table->date('date_fabrication')->nullable();
            $table->date('date_expiration')->index();
            $table->unsignedInteger('quantite_initiale')->default(0);
            $table->unsignedInteger('quantite_actuelle')->default(0);
            $table->decimal('prix_achat_unitaire', 12, 2)->nullable();
            $table->string('statut', 30)->default('actif')->index(); // actif, isole, rappele, epuise
            $table->text('motif_isolement')->nullable();
            $table->timestamps();

            // Index pour la sélection FEFO instantanée (produit + statut actif + tri par expiration)
            $table->index(['medicament_id', 'statut', 'date_expiration'], 'idx_fefo_selection');
            $table->unique(['medicament_id', 'numero_lot'], 'uniq_medicament_lot');
        });

        Schema::create('vente_medicament_lot', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vente_id')->constrained('ventes')->cascadeOnDelete();
            $table->foreignId('medicament_id')->constrained('medicaments')->cascadeOnDelete();
            $table->foreignId('lot_id')->constrained('medicament_lots')->cascadeOnDelete();
            $table->unsignedInteger('quantite');
            $table->decimal('prix_unitaire', 12, 2);
            $table->timestamps();

            $table->index(['vente_id', 'lot_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vente_medicament_lot');
        Schema::dropIfExists('medicament_lots');
    }
};
