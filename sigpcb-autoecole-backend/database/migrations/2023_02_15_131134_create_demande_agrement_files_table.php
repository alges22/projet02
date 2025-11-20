<?php

use App\Models\DemandeAgrement;
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
        Schema::create('demande_agrement_files', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(DemandeAgrement::class)->constrained();
            $table->string('nat_promoteur');
            $table->string('casier_promoteur');
            $table->string('ref_promoteur');
            $table->string('reg_commerce');
            $table->string('attest_fiscale');
            $table->string('attest_reg_organismes');
            $table->string('descriptive_locaux');
            $table->string('permis_moniteurs');
            $table->string('copie_statut')->nullable();
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
        Schema::dropIfExists('demande_agrement_files');
    }
};
