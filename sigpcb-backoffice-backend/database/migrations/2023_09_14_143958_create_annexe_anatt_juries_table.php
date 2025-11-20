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
        Schema::create('annexe_anatt_juries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("annexe_anatt_id");
            $table->string("name");
            $table->timestamps();
            $table->foreign('annexe_anatt_id')->references('id')->on('annexe_anatts')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('annexe_anatt_juries');
    }
};
