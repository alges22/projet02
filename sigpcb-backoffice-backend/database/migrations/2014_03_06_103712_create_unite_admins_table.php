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
        Schema::create('unite_admins', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedBigInteger('ua_parent_id')->nullable();
            $table->string('sigle', 25)->nullable()->unique();
            $table->boolean('status')->default(true);
            $table->foreign('ua_parent_id')->references('id')
                ->on('unite_admins')->onDelete('cascade');
            //permet de lier la clé étrangère à la table "unite_admins" elle-même, en référençant sa colonne "id"
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
        Schema::dropIfExists('unite_admins');
    }
};
