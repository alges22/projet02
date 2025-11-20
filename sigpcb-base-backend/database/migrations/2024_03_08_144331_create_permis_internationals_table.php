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
        Schema::create('permis_internationals', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('num_permis');
            $table->string('npi', 11);
            $table->enum('state', ['init', 'validate', 'rejected', 'pending', 'payment'])->default('init');
            $table->string('permis_file');
            $table->date('date_validation')->nullable();
            $table->date('date_rejet')->nullable();
            $table->string('categorie_permis_ids');
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
        Schema::dropIfExists('permis_internationals');
    }
};
