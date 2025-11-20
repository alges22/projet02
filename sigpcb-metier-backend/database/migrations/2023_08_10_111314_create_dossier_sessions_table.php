<?php

use App\Models\CandidatJustifAbsence;
use App\Models\DossierCandidat;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dossier_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('npi');
            $table->string('montant_paiement')->nullable();
            $table->string('is_militaire')->nullable();
            $table->unsignedBigInteger('old_ds_rejet_id')->nullable();
            $table->unsignedBigInteger('old_ds_justif_id')->nullable();
            // $table->string('restriction_medical');
            $table->json('restriction_medical');
            $table->date('date_payment')->nullable();
            $table->date('date_validation')->nullable();
            $table->enum('resultat_conduite', ['success', 'failed'])->nullable();
            $table->enum('resultat_code', ['success', 'failed'])->nullable();
            $table->enum('bouton_paiement', ['0', '1', '-1'])->default('0');
            $table->enum('state', ['init', 'pending', 'payment', 'validate', 'rejet'])->default('init');
            $table->boolean('closed')->default(false);
            $table->boolean('is_paid')->default(false);
            $table->string('fiche_medical')->nullable();
            $table->enum('type_examen', ['code-conduite', 'conduite'])->default("code-conduite");
            $table->string('presence')->nullable();
            $table->string('presence_conduite')->nullable();
            $table->unsignedBigInteger('permis_extension_id')->nullable();
            $table->unsignedBigInteger('langue_id');
            $table->unsignedBigInteger('auto_ecole_id');
            $table->unsignedBigInteger('annexe_id');
            $table->unsignedBigInteger('examen_id')->nullable();
            $table->unsignedBigInteger('categorie_permis_id');
            $table->unsignedBigInteger('permis_prealable_id')->nullable();
            $table->unsignedBigInteger('permis_prealable_dure')->nullable();
            $table->foreignIdFor(DossierCandidat::class)->constrained(); //dossier_candidat_id

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dossier_sessions');
    }
};
