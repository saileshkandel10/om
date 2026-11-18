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
        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->foreignId('quiz_id')->nullable()->change();
            $table->foreignId('quiz_category_id')->nullable()->after('quiz_id')->constrained('quiz_categories')->nullOnDelete();
            $table->string('difficulty', 20)->default('medium')->after('question_type'); // easy, medium, hard
            $table->foreignId('product_id')->nullable()->after('difficulty')->constrained('products')->nullOnDelete();
            $table->string('product_reference')->nullable()->after('product_id');
            $table->softDeletes()->after('is_active');

            $table->index(['quiz_category_id', 'difficulty', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->dropForeign(['quiz_category_id']);
            $table->dropForeign(['product_id']);
            $table->dropColumn(['quiz_category_id', 'difficulty', 'product_id', 'product_reference', 'deleted_at']);
        });
    }
};
