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
        Schema::create('fournisseurs', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 150);
            $table->string('code_fournisseur', 50)->nullable()->unique();
            $table->string('telephone', 50)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('adresse', 255)->nullable();
            $table->string('ville', 100)->default('Kati');
            $table->integer('delai_livraison_jours')->default(2);
            $table->string('conditions_paiement', 100)->default('Comptant à livraison');
            $table->boolean('actif')->default(true);
            $table->timestamps();

            $table->index('nom');
            $table->index('actif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fournisseurs');
    }
};
