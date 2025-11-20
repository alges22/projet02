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
        Schema::create('duplicatas', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('npi', 11);
            $table->string('phone');
            $table->string('num_permis');
            $table->string('annexe_id');
            $table->string('file');
            $table->string('group_sanguin');
            $table->string('type');
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
        Schema::dropIfExists('duplicatas');
    }
};
