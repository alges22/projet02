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
        Schema::create('auto_ecole_candidat_inscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('npi');
            $table->unsignedBigInteger('auto_ecole_id');
            $table->timestamp('date_inscription')->nullable();
            $table->string('status');
            $table->timestamp('date_resilience')->nullable();
            $table->foreign('auto_ecole_id')->references('id')->on('auto_ecoles')->onDelete('cascade');
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
        Schema::dropIfExists('auto_ecole_candidat_inscriptions');
    }
};
