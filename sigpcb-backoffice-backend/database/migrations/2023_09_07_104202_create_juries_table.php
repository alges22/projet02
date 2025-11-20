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
        Schema::create('juries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger("annexe_jury_id");
            $table->foreignId('annexe_anatt_id')->constrained();
            $table->foreignId('examinateur_id')->constrained();
            $table->unsignedBigInteger("examen_id");

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
        Schema::dropIfExists('jurys');
    }
};
