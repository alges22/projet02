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
        Schema::create('demande_agrements', function (Blueprint $table) {
            $table->id();
            $table->enum('state', ['init', 'validate', 'rejected', 'pending', 'payment'])->default('init');
            $table->string('auto_ecole');
            $table->string('promoteur_npi');
            $table->string('ifu', 13);
            $table->foreignId('departement_id')->constrained();
            $table->foreignId('commune_id')->constrained();
            $table->string('quartier')->nullable();
            $table->string('ilot')->nullable();
            $table->string('parcelle')->nullable();
            $table->text('moniteurs'); //json
            $table->text('vehicules')->nullable(); // json
            $table->string('telephone_pro');
            $table->string('email_pro');
            $table->string('email_promoteur');
            $table->foreignId('promoteur_id')->constrained('promoteurs');
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
        Schema::dropIfExists('demande_agrements');
    }
};
