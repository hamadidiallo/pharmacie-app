<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('commande_fournisseur_lignes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commande_fournisseur_id')->constrained('commandes_fournisseur')->cascadeOnDelete();
            $table->foreignId('medicament_id')->constrained('medicaments')->cascadeOnDelete();
            
            $table->integer('quantite_commandee');
            $table->integer('quantite_recue')->default(0);
            $table->decimal('prix_achat_unitaire_estime', 10, 2)->default(0);
            $table->decimal('prix_achat_unitaire_facture', 10, 2)->nullable();
            
            // Métadonnées du lot reçu à la réception
            $table->string('numero_lot_recu', 80)->nullable();
            $table->date('date_expiration_recue')->nullable();
            $table->foreignId('medicament_lot_id')->nullable()->constrained('medicament_lots')->nullOnDelete();
            
            $table->timestamps();

            $table->index(['commande_fournisseur_id', 'medicament_id'], 'cmd_lignes_cmd_med_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commande_fournisseur_lignes');
    }
};
