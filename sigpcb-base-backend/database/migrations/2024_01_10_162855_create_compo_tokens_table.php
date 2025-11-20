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
        Schema::create('compo_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('token');
            $table->dateTime('expire_at');
            $table->foreignId('candidat_salle_id')->constrained('candidat_examen_salles');
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
        Schema::dropIfExists('compo_tokens');
    }
};