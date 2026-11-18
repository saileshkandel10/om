<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_bill_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_bill_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name_raw')->nullable();
            $table->decimal('quantity', 15, 2)->nullable();
            $table->decimal('unit_price', 15, 2)->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->string('batch_no')->nullable();
            $table->decimal('confidence_score', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_bill_items');
    }
};
