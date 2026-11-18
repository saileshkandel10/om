<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('brand_name');
            $table->string('generic_name')->nullable();
            $table->string('strength')->nullable();
            $table->string('dosage_form')->nullable();
            $table->string('pack_size')->nullable();
            $table->foreignId('product_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('illness_category_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('mrp', 15, 2)->nullable();
            $table->string('image')->nullable();
            $table->string('launch_status')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
