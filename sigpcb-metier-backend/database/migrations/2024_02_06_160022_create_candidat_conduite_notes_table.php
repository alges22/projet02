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
        Schema::create('candidat_conduite_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recrutement_id')->constrained();
            $table->foreignId('recrutement_epreuve_id')->constrained();
            $table->string('candidat_npi');
            $table->unsignedBigInteger('examinateur_id')->constrained();
            $table->decimal('note', 5, 2);
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
        Schema::dropIfExists('candidat_conduite_notes');
    }
};
