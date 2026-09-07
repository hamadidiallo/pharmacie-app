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
        Schema::create('commandes_fournisseur', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fournisseur_id')->constrained('fournisseurs')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reference', 60)->unique();
            $table->date('date_commande');
            $table->date('date_livraison_prevue')->nullable();
            $table->date('date_reception')->nullable();
            $table->string('numero_bl', 80)->nullable(); // N° de Bon de Livraison du grossiste
            
            // Statuts : brouillon, envoyee, partiellement_recue, recue, annulee
            $table->string('statut', 30)->default('brouillon');
            $table->decimal('total_estime', 12, 2)->default(0);
            $table->decimal('total_facture', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['fournisseur_id', 'statut']);
            $table->index('statut');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commandes_fournisseur');
    }
};
