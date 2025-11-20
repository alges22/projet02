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
        Schema::create('candidat_otps', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->timestamp('expire');
            $table->string('action');
            $table->integer('nombre_de_fois')->default(0);
            $table->timestamp('retry_times')->nullable();
            $table->foreignId('user_id')->constrained('candidats');
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
        Schema::dropIfExists('candidat_otps');
    }
};
