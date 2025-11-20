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
        Schema::create('inscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('npi')->unique();
            $table->unsignedBigInteger('auto_ecole_id');
            $table->timestamp('date_inscription')->nullable();
            $table->string('status');
            $table->timestamp('date_resilience')->nullable();
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('auto_ecole_id')->references('id')->on('auto_ecoles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inscriptions');
    }
};
