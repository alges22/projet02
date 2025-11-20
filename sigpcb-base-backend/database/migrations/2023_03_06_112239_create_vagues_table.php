<?php

use App\Models\Vague;
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
        Schema::create('vagues', function (Blueprint $table) {
            $table->id();
            $table->integer('numero')->nullable();
            $table->dateTime('date_compo')->nullable();
            $table->foreignId('salle_compo_id')->nullable()->constrained();
            $table->text('questions')->nullable();
            $table->dateTime('closed_at')->nullable();


            $table->foreignId('langue_id')->constrained()->nullable();
            $table->foreignId('categorie_permis_id')->constrained();
            $table->foreignId('examen_id')->constrained();

            $table->unsignedBigInteger('annexe_anatt_id');
            $table->enum('status', Vague::STATUES);

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
        Schema::dropIfExists('vagues');
    }
};
