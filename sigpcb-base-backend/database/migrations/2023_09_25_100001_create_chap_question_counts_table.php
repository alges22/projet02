<?php

use App\Models\Chapitre;
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
        Schema::create('chap_question_counts', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('counts');
            $table->foreignIdFor(Chapitre::class)->constrained();
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
        Schema::dropIfExists('chap_question_counts');
    }
};
