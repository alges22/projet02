<?php

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
        Schema::create('demande_agrement_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demande_agrement_id')->constrained();
            $table->string('nat_promoteur');
            $table->string('casier_promoteur');
            $table->string('ref_promoteur');
            $table->string('reg_commerce');
            $table->string('attest_fiscale');
            $table->string('attest_reg_organismes');
            $table->string('descriptive_locaux');
            $table->string('copie_statut')->nullable();
            $table->text('carte_grise')->nullable();
            $table->text('assurance_visite')->nullable();
            $table->text('photo_vehicules')->nullable();

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
        Schema::dropIfExists('demande_agrement_files');
    }
};