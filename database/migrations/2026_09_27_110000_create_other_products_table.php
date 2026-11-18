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
        Schema::create('other_products', function (Blueprint $table) {
            $table->id();
            $table->string('brand_name')->index();
            $table->string('generic_name')->nullable()->index();
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->foreignId('illness_category_id')->nullable()->constrained('illness_categories')->nullOnDelete();
            $table->foreignId('suggested_illness_category_id')->nullable()->constrained('illness_categories')->nullOnDelete();
            $table->decimal('confidence_score', 5, 2)->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('active')->index(); // active, unmapped, flagged
            $table->boolean('is_verified')->default(true)->index();
            $table->string('source')->default('manual'); // manual, import, ocr
            $table->text('raw_ocr_text')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('other_products');
    }
};
