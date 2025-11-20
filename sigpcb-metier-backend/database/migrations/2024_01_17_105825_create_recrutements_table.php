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
        Schema::create('recrutements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entreprise_id')->constrained();
            $table->string('categorie_permis_id');
            $table->string('annexe_id');
            $table->dateTime('date_compo');
            $table->boolean('finished')->default(false);
            $table->boolean('closed')->default(false);
            $table->boolean('resultat')->default(false);
            $table->boolean('convocation')->default(false);
            $table->enum('state', ['init', 'validate', 'rejected', 'pending', 'payment'])->default('init');
            $table->date('date_validation')->nullable();
            $table->date('date_rejet')->nullable();
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
        Schema::dropIfExists('recrutements');
    }
};
