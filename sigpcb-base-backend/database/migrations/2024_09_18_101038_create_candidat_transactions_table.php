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
        Schema::create('candidat_transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->string('service');
            $table->string('service_id')->nullable();
            $table->decimal('amount', 32);
            $table->string('npi');
            $table->string('note', 255)->nullable();
            $table->timestamp('perform_time')->nullable();
            $table->timestamp('refund_time')->nullable();
            $table->string("transaction_id", 191)->unique();
            $table->dateTime("expired_at")->nullable();
            $table->enum('status', ['init', "approved", "failed"])
                ->default("init")
                ->index();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('candidat_transactions');
    }
};
