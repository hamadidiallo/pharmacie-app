<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordonnancier_lignes', function (Blueprint $table) {
            $table->id();
            $table->string('numero_ordonnancier', 50)->unique();
            $table->foreignId('vente_id')->nullable()->constrained('ventes')->nullOnDelete();
            $table->foreignId('medicament_id')->constrained('medicaments')->cascadeOnDelete();
            $table->foreignId('medicament_lot_id')->nullable()->constrained('medicament_lots')->nullOnDelete();
            $table->date('date_prescription')->nullable();
            $table->dateTime('date_delivrance');
            $table->string('nom_prescripteur', 150); // Ex: Dr Oumar Coulibaly
            $table->string('specialite_prescripteur', 100)->nullable();
            $table->string('nom_patient', 150);
            $table->unsignedInteger('age_patient')->nullable();
            $table->text('posologie')->nullable();
            $table->unsignedInteger('quantite_delivree');
            $table->foreignId('pharmacien_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordonnancier_lignes');
    }
};
