<?php

use App\Models\Candidat;
use App\Models\Recrutement;
use App\Models\DossierSession;
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
        Schema::create('convocation_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->foreignId('candidat_id')->constrained();
            $table->foreignId('recrutement_id')->constrained();
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
        Schema::dropIfExists('convocation_codes');
    }
};
