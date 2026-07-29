<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Le serveur MySQL local a MyISAM comme moteur par défaut, et `engine => null`
 * dans la configuration laissait ce défaut s'appliquer. Conséquences mesurées :
 *
 *   - DB::transaction() ne rollback pas : une exception au milieu d'un
 *     encaissement laissait la vente à moitié écrite ;
 *   - lockForUpdate() est un no-op : deux ventes simultanées pouvaient vendre
 *     le même stock ;
 *   - aucune clé étrangère n'a jamais été créée, malgré les `constrained()`
 *     des migrations : MyISAM les accepte puis les ignore.
 *
 * Cette migration convertit les tables et crée les contraintes manquantes.
 */
return new class extends Migration
{
    /** Ordre de conversion : les tables référencées d'abord. */
    private const TABLES = [
        'users',
        'medicaments',
        'ventes',
        'medicament__vente',
        'sessions',
        'cache',
        'cache_locks',
        'jobs',
        'job_batches',
        'failed_jobs',
        'password_reset_tokens',
    ];

    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        foreach (self::TABLES as $table) {
            if (Schema::hasTable($table)) {
                DB::statement("ALTER TABLE `{$table}` ENGINE = InnoDB");
            }
        }

        // Les index posés par les anciennes migrations existent déjà ; seules
        // les contraintes manquent.
        Schema::table('medicaments', function (Blueprint $table) {
            // retirer un pharmacien ne doit pas emporter le catalogue
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
        });

        Schema::table('ventes', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
        });

        Schema::table('medicament__vente', function (Blueprint $table) {
            // un produit vendu ne peut plus être détruit : il est archivé
            $table->foreign('medicament_id')->references('id')->on('medicaments')->restrictOnDelete();
            // supprimer une vente emporte ses lignes
            $table->foreign('vente_id')->references('id')->on('ventes')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        Schema::table('medicament__vente', function (Blueprint $table) {
            $table->dropForeign(['medicament_id']);
            $table->dropForeign(['vente_id']);
        });

        Schema::table('ventes', fn (Blueprint $table) => $table->dropForeign(['user_id']));
        Schema::table('medicaments', fn (Blueprint $table) => $table->dropForeign(['user_id']));

        // le moteur n'est volontairement pas remis à MyISAM
    }
};
