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
        Schema::create('medicament__vente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicament_id')->constrained('medicaments')->onDelete('cascade');
            $table->foreignId('vente_id')->constrained('ventes')->onDelete('cascade');
            $table->integer('quantite');
            $table->decimal('prix',10,2);
            $table->decimal('sous_total',10,2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicament__vente');
    }
};
