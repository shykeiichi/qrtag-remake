<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username');
            $table->string('display_name');
            $table->enum('class', ['TE21A', 'TE21B', 'TE21C', 'TE21D', 'TE22A', 'TE22B', 'TE22C', 'TE22D', 'TE23A', 'TE23B', 'TE23C', 'TE23D']);
            $table->boolean('is_admin')->default(false);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent();
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
        });

        Schema::create('event_users', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('event_id')->unsigned();
            $table->boolean('is_alive')->default(true);
            $table->string('secret');
            $table->bigInteger('target_id')->unsigned();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('target_id')->references('id')->on('users');
            $table->foreign('event_id')->references('id')->on('events');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('events');
        Schema::dropIfExists('event_users');
    }
};
