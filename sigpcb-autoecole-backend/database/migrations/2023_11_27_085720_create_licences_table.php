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
        Schema::create('licences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('auto_ecole_id');
            $table->boolean('status')->default(false);
            $table->dateTime('date_debut');
            $table->dateTime('date_fin');
            $table->string('code', 16)->unique();

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
        Schema::dropIfExists('licences');
    }
};
