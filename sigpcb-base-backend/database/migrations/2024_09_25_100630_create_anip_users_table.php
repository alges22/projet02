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
        Schema::create('anip_users', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('prenoms');
            $table->string('email')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('date_de_naissance')->nullable();
            $table->string('lieu_de_naissance')->default('Inconnu');
            $table->string('avatar')->default('photos/avatar.png');
            $table->string('signature')->nullable();
            $table->char('sexe', 1);
            $table->string('adresse')->nullable();
            $table->string('ville_residence')->nullable();
            $table->string('telephone');
            $table->string('telephone_prefix')->nullable();
            $table->string('npi');
            $table->dateTime('last_updated');
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
        Schema::dropIfExists('anip_users');
    }
};
