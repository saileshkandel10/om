<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_illnesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('illness_category_id')->constrained('illness_categories')->cascadeOnDelete();
            $table->string('source_type')->default('purchase_bill'); // 'purchase_bill', 'prescription', 'manual'
            $table->unsignedBigInteger('source_id')->nullable();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->timestamp('detected_at')->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'illness_category_id']);
            $table->index(['illness_category_id', 'user_id']);
            $table->index(['source_type', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_illnesses');
    }
};
