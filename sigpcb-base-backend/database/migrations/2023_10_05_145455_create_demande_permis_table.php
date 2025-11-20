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
        Schema::create('demande_permis', function (Blueprint $table) {
            $table->id();
            $table->string('code_permis');
            $table->string('permis_file');
            $table->string('npi', 11);
            $table->string('group_sanguin');
            $table->string('restriction_medical');
            $table->string('group_sanguin_file');
            $table->string('categorie_permis_ids');
            $table->enum('state', ['init', 'validate', 'rejected', 'pending', 'payment'])->default('init');
            $table->string('delivrance_date');
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
        Schema::dropIfExists('demande_permis');
    }
};
