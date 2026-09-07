<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventes', function (Blueprint $table) {
            $table->foreignId('assurance_id')->nullable()->after('session_caisse_id')->constrained('assurances')->nullOnDelete();
            $table->foreignId('bordereau_assurance_id')->nullable()->after('assurance_id')->constrained('bordereaux_assurance')->nullOnDelete();
            $table->string('matricule_assure', 100)->nullable()->after('bordereau_assurance_id');
            $table->string('nom_assure', 150)->nullable()->after('matricule_assure');
            $table->decimal('taux_couverture', 5, 2)->nullable()->after('nom_assure');
            $table->decimal('part_assurance', 10, 2)->default(0)->after('taux_couverture');
            $table->decimal('part_patient', 10, 2)->nullable()->after('part_assurance');
            $table->string('statut_remboursement', 30)->default('non_applicable')->after('part_patient'); // non_applicable, en_attente, transmis, rembourse, rejete
        });
    }

    public function down(): void
    {
        Schema::table('ventes', function (Blueprint $table) {
            $table->dropForeign(['assurance_id']);
            $table->dropForeign(['bordereau_assurance_id']);
            $table->dropColumn([
                'assurance_id',
                'bordereau_assurance_id',
                'matricule_assure',
                'nom_assure',
                'taux_couverture',
                'part_assurance',
                'part_patient',
                'statut_remboursement',
            ]);
        });
    }
};
