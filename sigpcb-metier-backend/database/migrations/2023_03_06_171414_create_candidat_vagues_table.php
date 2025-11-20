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
        Schema::create('candidat_vagues', function (Blueprint $table) {

            $table->id();
            $table->unsignedBigInteger('candidat_id');
            $table->unsignedBigInteger('vague_id');
            $table->boolean('is_present')->default(false);
            $table->timestamps();
        
            $table->foreign('candidat_id')->references('id')->on('users')->onDelete('cascade');
            // $table->foreign('vague_id')->references('id')->on('vagues')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('candidat_vagues');
    }
};
