<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Scenarios & Practice Drills
        Schema::create('scenarios', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('title');
            $table->string('category');
            $table->text('description');
            $table->string('difficulty')->default('Beginner');
            $table->longText('passenger_profile');
            $table->longText('flight_segments');
            $table->longText('validation_criteria');
            $table->integer('time_limit_minutes')->default(30);
            $table->string('created_by')->default('instructor');
            $table->timestamps();
        });

        // 2. Scenario Submissions & Grading
        Schema::create('submissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('scenario_id')->index();
            $table->string('user_id')->index();
            $table->string('status')->default('not_started'); // not_started, in_progress, submitted, reviewed
            $table->integer('score')->default(0);
            $table->longText('auto_grade_results')->nullable();
            $table->integer('manual_override_score')->nullable();
            $table->text('instructor_feedback')->nullable();
            $table->longText('terminal_logs')->nullable();
            $table->string('pnr_record_locator', 6)->nullable();
            $table->longText('pnr_snapshot')->nullable();
            $table->integer('time_elapsed_seconds')->default(0);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        // 3. Simulated Amadeus Queues
        Schema::create('queue_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('queue_number')->index();
            $table->string('category');
            $table->string('office_id')->default('LON1A0980');
            $table->string('target_user_id')->nullable()->index();
            $table->string('pnr_locator', 6)->index();
            $table->longText('pnr_summary');
            $table->text('message');
            $table->string('status')->default('PENDING'); // PENDING, PROCESSED, REMOVED
            $table->timestamps();
        });

        // 4. Terminal Activity Logs (For Live Classroom Spectate & Analytics)
        Schema::create('terminal_activity_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('user_id')->index();
            $table->string('command');
            $table->longText('output_lines');
            $table->boolean('is_error')->default(false);
            $table->string('command_type')->default('GENERAL');
            $table->integer('execution_time_ms')->default(0);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('terminal_activity_logs');
        Schema::dropIfExists('queue_items');
        Schema::dropIfExists('submissions');
        Schema::dropIfExists('scenarios');
    }
};
