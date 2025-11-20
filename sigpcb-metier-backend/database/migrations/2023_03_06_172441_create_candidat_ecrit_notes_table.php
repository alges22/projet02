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
        Schema::create('candidat_ecrit_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('candidat_id');
            $table->unsignedBigInteger('vague_id');
            $table->unsignedBigInteger('examen_id');
            $table->float('note', 8, 2);
            $table->timestamps();

        
            $table->foreign('candidat_id')->references('id')->on('users')->onDelete('cascade');
            // $table->foreign('vague_id')->references('id')->on('vagues')->onDelete('cascade');
            // $table->foreign('examen_id')->references('id')->on('examens')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('candidat_ecrit_notes');
    }
};
