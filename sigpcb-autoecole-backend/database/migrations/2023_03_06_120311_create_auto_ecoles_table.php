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

        Schema::create('auto_ecoles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->unique();
            $table->string('raison_sociale', 255)->nullable();
            $table->string('email', 255)->unique();
            $table->enum('type', ['civil', 'militaire'])->default('civil');
            $table->string('phone', 25);
            $table->string('adresse', 255)->nullable();
            $table->string('code')->unique();
            $table->string('num_ifu', 255)->unique();
            $table->boolean('status')->default(false);
            $table->boolean('imported')->default(false);
            $table->unsignedBigInteger('commune_id');
            $table->unsignedBigInteger('departement_id');
            $table->year('annee_creation')->nullable();
            $table->foreignId('agrement_id');
            $table->foreignId('promoteur_id')->constrained('users');
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
        Schema::dropIfExists('auto_ecoles');
    }
};
