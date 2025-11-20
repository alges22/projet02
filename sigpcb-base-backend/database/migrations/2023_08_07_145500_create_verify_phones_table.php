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
        Schema::create('verify_phones', function (Blueprint $table) {
            $table->id();
            $table->string('ifu')->nullable();
            $table->string('npi')->nullable();
            $table->string('code', 6)->nullable();
            $table->dateTime("expired_at");
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
        Schema::dropIfExists('verify_phones');
    }
};