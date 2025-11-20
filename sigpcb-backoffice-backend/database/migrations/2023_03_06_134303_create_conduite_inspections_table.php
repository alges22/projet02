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
        /**
         * La table d'inspection, par les inspecteurs
         */
        Schema::create('conduite_inspections', function (Blueprint $table) {
            $table->id();
            $table->boolean('status')->default(false);
            $table->text('observations')->nullable();
            $table->unsignedBigInteger('inspecteur_id');
            $table->foreign('inspecteur_id')
            ->references('id')
            ->on('inspecteurs');

            $table->unsignedBigInteger('vague_id'); // vient de la base
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
        Schema::dropIfExists('conduite_inspections');
    }
};
