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
        Schema::create('compo_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidat_salle_id')->constrained('candidat_examen_salles');
            $table->dateTime('last_connected_at')->nullable();
            $table->string('ip')->nullable();
            $table->text('user_agent')->nullable();
            $table->boolean('authorized')->default(true);
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
        Schema::dropIfExists('compo_sessions');
    }
};