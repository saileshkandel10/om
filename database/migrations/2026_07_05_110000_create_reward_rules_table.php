<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reward_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('user_type')->nullable();
            $table->string('basis_type')->nullable();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('illness_category_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('min_qty', 15, 2)->nullable();
            $table->decimal('min_amount', 15, 2)->nullable();
            $table->decimal('point_rate', 15, 2)->nullable();
            $table->string('bonus_type')->nullable();
            $table->decimal('bonus_value', 15, 2)->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reward_rules');
    }
};
