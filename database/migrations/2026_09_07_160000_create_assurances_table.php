<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assurances', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('code', 50)->unique();
            $table->decimal('taux_couverture_defaut', 5, 2)->default(70.00);
            $table->string('telephone', 50)->nullable();
            $table->string('email')->nullable();
            $table->text('adresse')->nullable();
            $table->integer('delai_remboursement_jours')->default(30);
            $table->boolean('est_actif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assurances');
    }
};
