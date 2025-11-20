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
        Schema::create('candidat_conduite_reponses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bareme_conduite_id')->constrained();
            $table->foreignId('conduite_vague_id')->constrained();
            $table->unsignedBigInteger('mention_id')->nullable();
            $table->unsignedBigInteger('dossier_session_id');
            $table->unsignedBigInteger('jury_candidat_id')->constrained();
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
        Schema::dropIfExists('candidat_conduite_reponses');
    }
};
