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
        Schema::create('candidats', function (Blueprint $table) {
            $table->id();
            $table->string('npi', 10);
            $table->string('conduite_note')->nullable();
            $table->string('code_note')->nullable();
            $table->string('note_final')->nullable();
            $table->string('num_permis');
            $table->string('permis_file');
            $table->unsignedBigInteger('langue_id');
            $table->foreignId('recrutement_id')->constrained();
            $table->foreignId('entreprise_id')->constrained();
            $table->enum('state', ['init', 'pending', 'validate', 'rejected'])->default('init');

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
        Schema::dropIfExists('candidats');
    }
};
