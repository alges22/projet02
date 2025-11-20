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
        Schema::create('dossier_motif_rejets', function (Blueprint $table) {
            $table->id();
            $table->string('motif');
            $table->unsignedBigInteger('dossier_candidat_id');
            $table->timestamp('date_rejet')->nullable();
            $table->timestamp('date_soumission')->nullable();
            $table->timestamp('date_decision')->nullable();
            $table->timestamps();

            // $table->foreign('dossier_candidat_id')
            //       ->references('id')->on('dossier_candidats')
            //       ->onDelete('cascade');




        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dossier_motif_rejets');
    }
};
