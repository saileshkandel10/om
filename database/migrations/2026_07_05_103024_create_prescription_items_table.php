<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescription_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('medicine_name_raw')->nullable();
            $table->string('generic_name')->nullable();
            $table->string('dosage')->nullable();
            $table->decimal('quantity', 15, 2)->nullable();
            $table->foreignId('illness_category_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('confidence_score', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescription_items');
    }
};
