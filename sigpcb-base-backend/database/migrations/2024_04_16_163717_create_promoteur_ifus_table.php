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
        Schema::create('promoteur_ifus', function (Blueprint $table) {
            $table->id();
            $table->string('npi')->unique();
            $table->string('verify_code');
            $table->timestamp('verify_code_expire');
            $table->boolean('verified')->default(false);
            $table->string('ifu');
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
        Schema::dropIfExists('promoteur_ifus');
    }
};
