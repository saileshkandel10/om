<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shareholder_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shareholder_master_id')->nullable()->constrained()->nullOnDelete();
            $table->string('boid')->unique();
            $table->decimal('shares_owned', 15, 2)->nullable();
            $table->string('verification_status')->default('pending');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shareholder_accounts');
    }
};
