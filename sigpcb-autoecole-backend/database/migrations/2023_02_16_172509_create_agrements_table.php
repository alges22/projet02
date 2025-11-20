<?php

use App\Models\DemandeAgrement;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agrements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promoteur_id')->constrained('users');
            $table->dateTime('date_obtention');
            $table->string('code')->unique();
            $table->foreignIdFor(DemandeAgrement::class)->constrained();
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
        Schema::dropIfExists('agrements');
    }
};
