<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Confirmed / Ticketed PNRs
        Schema::create('pnrs', function (Blueprint $table) {
            $table->string('record_locator', 6)->primary();
            $table->string('user_id')->index();
            $table->string('office_id')->default('LON1A0980');
            $table->string('status')->default('CONFIRMED');
            $table->longText('data_json');
            $table->timestamps();
        });

        // Student lesson progress
        Schema::create('lesson_progress', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('user_id')->index();
            $table->string('lesson_id');
            $table->string('module_id');
            $table->string('status')->default('in_progress');
            $table->longText('completed_objectives_json');
            $table->integer('score')->default(0);
            $table->integer('attempts')->default(1);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'lesson_id']);
        });

        // Airports reference table
        Schema::create('airports', function (Blueprint $table) {
            $table->string('iata', 3)->primary();
            $table->string('icao', 4)->nullable();
            $table->string('name');
            $table->string('city');
            $table->string('country');
            $table->float('lat')->nullable();
            $table->float('lon')->nullable();
            $table->string('tz')->nullable();
        });

        // Airlines reference table
        Schema::create('airlines', function (Blueprint $table) {
            $table->string('iata', 3)->primary();
            $table->string('icao', 4)->nullable();
            $table->string('name');
            $table->string('country')->nullable();
            $table->string('callsign')->nullable();
            $table->string('ticket_prefix', 4)->nullable();
        });

        // Aircraft equipment reference table
        Schema::create('aircraft', function (Blueprint $table) {
            $table->string('code', 5)->primary();
            $table->string('name');
            $table->string('manufacturer');
            $table->string('category')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aircraft');
        Schema::dropIfExists('airlines');
        Schema::dropIfExists('airports');
        Schema::dropIfExists('lesson_progress');
        Schema::dropIfExists('pnrs');
    }
};
