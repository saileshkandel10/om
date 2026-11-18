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
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->integer('correct_answers_count')->default(0)->after('score');
            $table->integer('incorrect_answers_count')->default(0)->after('correct_answers_count');
            $table->integer('unanswered_count')->default(0)->after('incorrect_answers_count');
            $table->integer('time_taken_seconds')->default(0)->after('reward_points_awarded');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropColumn([
                'correct_answers_count',
                'incorrect_answers_count',
                'unanswered_count',
                'time_taken_seconds',
            ]);
        });
    }
};
