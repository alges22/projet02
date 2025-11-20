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
        Schema::create('old_auto_ecoles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('departement');
            $table->string('commune');
            $table->string('moniteur_npis')->nullable();
            $table->string('promoteur_npi');
            $table->string('adresse');
            $table->string('agrement');
            $table->string('expire_licence');
            $table->string('code_licence');
            $table->string('ifu');
            $table->string('email_pro');
            $table->string('email_promoteur');
            $table->string('telephone_pro');
            $table->text('vehicules');

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
        Schema::dropIfExists('old_auto_ecoles');
    }
};