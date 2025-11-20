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
        Schema::table('parcours_suivis', function (Blueprint $table) {
            $table->unsignedBigInteger('permis_num_payment_id')->nullable();
            $table->foreign('permis_num_payment_id')->references('id')->on('permis_num_payments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('parcours_suivis', function (Blueprint $table) {
            $table->dropForeign(['permis_num_payment_id']);
            $table->dropColumn('permis_num_payment_id');
        });
    }
};

