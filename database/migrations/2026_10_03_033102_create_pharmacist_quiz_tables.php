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
        // 1. Quiz Categories
        Schema::create('quiz_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('color', 50)->nullable()->default('#CD0B6F');
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Quizzes
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_category_id')->nullable()->constrained('quiz_categories')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();
            $table->integer('time_limit_minutes')->default(15);
            $table->decimal('passing_percentage', 5, 2)->default(60.00);
            $table->decimal('reward_points', 10, 2)->default(50.00);
            $table->integer('max_attempts')->default(1);
            $table->boolean('is_randomized')->default(true);
            $table->boolean('is_active')->default(true);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('display_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'start_date', 'end_date']);
        });

        // 3. Quiz Questions
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            $table->text('question_text');
            $table->text('explanation')->nullable();
            $table->integer('points')->default(1);
            $table->string('question_type', 30)->default('single_choice'); // single_choice, multiple_choice, true_false
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 4. Quiz Question Options
        Schema::create('quiz_question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_question_id')->constrained('quiz_questions')->cascadeOnDelete();
            $table->text('option_text');
            $table->boolean('is_correct')->default(false);
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });

        // 5. Quiz Attempts
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            $table->integer('attempt_number')->default(1);
            $table->integer('score')->default(0);
            $table->integer('total_points')->default(0);
            $table->decimal('percentage', 5, 2)->default(0.00);
            $table->boolean('is_passed')->default(false);
            $table->decimal('reward_points_awarded', 10, 2)->default(0.00);
            $table->string('status', 30)->default('in_progress'); // in_progress, completed, expired, abandoned
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'quiz_id', 'status']);
        });

        // 6. Quiz Attempt Answers
        Schema::create('quiz_attempt_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_attempt_id')->constrained('quiz_attempts')->cascadeOnDelete();
            $table->foreignId('quiz_question_id')->constrained('quiz_questions')->cascadeOnDelete();
            $table->foreignId('selected_option_id')->nullable()->constrained('quiz_question_options')->nullOnDelete();
            $table->boolean('is_correct')->default(false);
            $table->integer('points_earned')->default(0);
            $table->timestamps();

            $table->index(['quiz_attempt_id', 'quiz_question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_attempt_answers');
        Schema::dropIfExists('quiz_attempts');
        Schema::dropIfExists('quiz_question_options');
        Schema::dropIfExists('quiz_questions');
        Schema::dropIfExists('quizzes');
        Schema::dropIfExists('quiz_categories');
    }
};
