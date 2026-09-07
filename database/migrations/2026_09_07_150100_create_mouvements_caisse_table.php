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
        Schema::create('mouvements_caisse', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_caisse_id')->constrained('sessions_caisse')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            // type: sortie (dépense) ou entree (rajout d'espèces)
            $table->string('type', 20);
            $table->decimal('montant', 10, 2);
            $table->string('motif', 255);
            $table->string('beneficiaire', 150)->nullable();
            $table->timestamps();

            $table->index(['session_caisse_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mouvements_caisse');
    }
};
