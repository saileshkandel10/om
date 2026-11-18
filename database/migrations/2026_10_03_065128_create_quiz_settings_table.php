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
        Schema::create('quiz_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('questions_per_quiz')->default(10);
            $table->integer('time_limit_minutes')->default(10);
            $table->decimal('passing_percentage', 5, 2)->default(70.00);
            $table->boolean('is_randomized_questions')->default(true);
            $table->boolean('is_randomized_options')->default(true);
            $table->boolean('show_explanations')->default(true);
            $table->boolean('allow_retry')->default(true);
            $table->integer('max_attempts')->default(3);
            $table->decimal('reward_points', 10, 2)->default(50.00);
            $table->boolean('is_active')->default(true);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('instructions')->nullable();
            $table->text('announcement')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_settings');
    }
};
