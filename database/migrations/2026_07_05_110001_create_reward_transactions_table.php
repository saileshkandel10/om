<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reward_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_bill_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('reward_rule_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('points_earned', 15, 2)->default(0);
            $table->decimal('bonus_amount', 15, 2)->default(0);
            $table->decimal('royalty_amount', 15, 2)->default(0);
            $table->string('transaction_type')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reward_transactions');
    }
};
