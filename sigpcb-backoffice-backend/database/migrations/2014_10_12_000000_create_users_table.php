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
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->string('last_name');
            $table->string('first_name');
            $table->string('phone', 25)->nullable();

            $table->unsignedBigInteger('titre_id')->nullable();
            $table->foreign('titre_id')->references('id')->on('titres');

            $table->string('email')->unique();
            $table->string('password');


            $table->boolean('status')->default(true);

            $table->unsignedBigInteger('unite_admin_id')->nullable();
            $table->foreign('unite_admin_id')->references('id')->on('unite_admins');

            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
};
