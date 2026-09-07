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
        Schema::create('sessions_caisse', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->dateTime('date_ouverture');
            $table->dateTime('date_fermeture')->nullable();
            
            // Fond de caisse initial
            $table->decimal('fond_caisse_ouverture', 12, 2)->default(0);
            
            // Totaux théoriques calculés
            $table->decimal('total_especes_theorique', 12, 2)->default(0);
            $table->decimal('total_mobile_money', 12, 2)->default(0);
            $table->decimal('total_carte', 12, 2)->default(0);
            $table->decimal('total_sorties_especes', 12, 2)->default(0);
            $table->decimal('total_entrees_especes', 12, 2)->default(0);
            
            // Clôture physique & Billetage
            $table->decimal('montant_reel_compte', 12, 2)->nullable();
            $table->decimal('ecart_caisse', 12, 2)->nullable();
            $table->json('billetage')->nullable();
            
            // Statut
            $table->string('statut', 20)->default('ouverte'); // ouverte, cloturee
            $table->text('observations')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'statut']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions_caisse');
    }
};
