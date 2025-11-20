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
        Schema::create('moniteur_otps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('moniteur_id')->constrained("auto_ecole_moniteurs");
            $table->string('code');
            $table->timestamp('expire');
            $table->string('action');
            $table->integer('nombre_de_fois')->default(0);
            $table->timestamp('retry_times')->nullable();

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
        Schema::dropIfExists('moniteur_otps');
    }
};