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
        Schema::table('quiz_attempt_answers', function (Blueprint $table) {
            $table->text('question_text_snapshot')->nullable()->after('quiz_question_id');
            $table->text('explanation_snapshot')->nullable()->after('question_text_snapshot');
            $table->json('options_snapshot')->nullable()->after('explanation_snapshot');
            $table->unsignedBigInteger('correct_option_id')->nullable()->after('selected_option_id');
            $table->integer('points_possible')->default(1)->after('is_correct');
            $table->integer('display_order')->default(0)->after('points_possible');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quiz_attempt_answers', function (Blueprint $table) {
            $table->dropColumn([
                'question_text_snapshot',
                'explanation_snapshot',
                'options_snapshot',
                'correct_option_id',
                'points_possible',
                'display_order',
            ]);
        });
    }
};
