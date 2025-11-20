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
        Schema::create('duplicata_rejets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('duplicata_id')->constrained();
            $table->longText('motif');
            $table->dateTime('date_validation')->nullable();
            $table->dateTime('date_correction')->nullable();
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
        Schema::dropIfExists('duplicata_rejets');
    }
};
