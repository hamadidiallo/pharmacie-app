<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medicaments', function (Blueprint $table) {
            $table->string('dci', 150)->nullable()->index()->after('nom'); // Dénomination Commune Internationale
            $table->string('code_barre', 60)->nullable()->unique()->after('dci'); // EAN-13, Code 128, DataMatrix
            $table->string('forme', 80)->nullable()->after('code_barre'); // Comprimé, Gélule, Sirop, etc.
            $table->string('dosage', 80)->nullable()->after('forme'); // 500mg, 1g, etc.
            $table->string('tableau', 30)->default('non_liste')->after('dosage'); // non_liste, liste_1, liste_2, stupefiant
            $table->boolean('ordonnance_requise')->default(false)->after('tableau');
            $table->unsignedInteger('stock_securite')->default(10)->after('stock');
            $table->unsignedInteger('stock_alerte')->default(5)->after('stock_securite');
        });
    }

    public function down(): void
    {
        Schema::table('medicaments', function (Blueprint $table) {
            $table->dropColumn([
                'dci',
                'code_barre',
                'forme',
                'dosage',
                'tableau',
                'ordonnance_requise',
                'stock_securite',
                'stock_alerte',
            ]);
        });
    }
};
