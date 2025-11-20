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
        Schema::create('candidat_justification_absences', function (Blueprint $table) {
            $table->id();
            $table->integer('examen_id')->nullable();
            $table->string('candidat_npi')->nullable();
            $table->dateTime('validated_at')->nullable();
            $table->dateTime('rejeted_at')->nullable();
            $table->string('agent_npi')->nullable();
            $table->integer('dossier_session_id')->nullable();
            $table->string('piece_justificatve');
            $table->string('fiche_medical');
            $table->text('note')->nullable();
            $table->enum('type_examen', ['code-conduite', 'conduite'])->default("code-conduite");
            $table->enum('status', ['init', 'used', 'validated', 'rejected']);
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
        Schema::dropIfExists('candidat_justification_absences');
    }
};
