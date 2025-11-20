<?php

use App\Models\Vague;
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
        Schema::table('vagues', function (Blueprint $table) {

            $table->foreignId('salle_compo_id')->nullable()->constrained();
            $table->text('questions')->nullable();
            $table->dateTime(column: 'closed_at')->nullable();

            $table->foreignId('langue_id')->nullable()->constrained();
            $table->foreignId('categorie_permis_id')->nullable()->constrained();


            $table->enum('status', Vague::STATUES)->default('new');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vagues', function (Blueprint $table) {
            //
        });
    }
};
