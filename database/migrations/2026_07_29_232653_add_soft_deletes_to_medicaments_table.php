<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Un médicament retiré du catalogue est archivé, jamais détruit : le pivot
 * portait onDelete('cascade'), ce qui effaçait ses lignes dans toutes les
 * ventes passées et rendait les tickets et les statistiques incohérents.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medicaments', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Garde-fou au niveau de la base contre une suppression définitive.
        // SQLite (utilisé par les tests) ne sait pas remplacer une contrainte.
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table('medicament__vente', function (Blueprint $table) {
                $table->dropForeign(['medicament_id']);
                $table->foreign('medicament_id')->references('id')->on('medicaments')->restrictOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table('medicament__vente', function (Blueprint $table) {
                $table->dropForeign(['medicament_id']);
                $table->foreign('medicament_id')->references('id')->on('medicaments')->cascadeOnDelete();
            });
        }

        Schema::table('medicaments', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
