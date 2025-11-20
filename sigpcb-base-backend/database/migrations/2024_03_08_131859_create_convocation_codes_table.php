<?php

use Illuminate\Support\Facades\Schema;
use App\Models\Candidat\DossierSession;
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
            $table->uuid('uuid')->unique();
            $table->foreignId('dossier_session_id')->constrained();
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
