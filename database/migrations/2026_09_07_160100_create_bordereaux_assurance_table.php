<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bordereaux_assurance', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 50)->unique();
            $table->foreignId('assurance_id')->constrained('assurances')->cascadeOnDelete();
            $table->date('periode_debut');
            $table->date('periode_fin');
            $table->decimal('montant_total', 12, 2)->default(0);
            $table->unsignedInteger('nombre_dossiers')->default(0);
            $table->string('statut', 30)->default('Brouillon'); // Brouillon, Transmis, Regle, Rejete
            $table->dateTime('date_transmission')->nullable();
            $table->dateTime('date_reglement')->nullable();
            $table->string('mode_reglement', 50)->nullable();
            $table->string('reference_reglement', 100)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bordereaux_assurance');
    }
};
