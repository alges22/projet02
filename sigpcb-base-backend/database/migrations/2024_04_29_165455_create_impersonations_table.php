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
        Schema::create('impersonations', function (Blueprint $table) {
            $table->id();
            $table->string('user_type');
            $table->string('admin_npi');
            $table->string('user_npi');
            $table->dateTime('expire_at');
            $table->string('token')->unique();
            $table->string('login_url');
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
        Schema::dropIfExists('impersonations');
    }
};
