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
        Schema::create('parcours_suivis', function (Blueprint $table) {
            $table->id();
            $table->string('service');
            $table->unsignedBigInteger('auto_ecole_id')->nullable();
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->unsignedBigInteger('candidat_justif_absence_id')->nullable();
            $table->foreignId('categorie_permis_id')->nullable()->constrained();
            $table->string('npi');
            $table->string('slug')->nullable();
            $table->longText('message')->nullable();
            $table->json('bouton')->nullable();
            $table->json('eservice')->nullable();
            $table->string('action')->nullable();
            $table->text('url')->nullable();
            $table->date('date_action');
            $table->foreignId('dossier_candidat_id')->nullable()->constrained();
            $table->foreignId('dossier_session_id')->nullable()->constrained();
            $table->foreignId('candidat_id')->constrained();
            $table->unsignedBigInteger('permis_num_payment_id')->nullable();
            $table->foreign('permis_num_payment_id')->references('id')->on('permis_num_payments')->onDelete('cascade');
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
        Schema::dropIfExists('parcours_suivis');
    }
};
