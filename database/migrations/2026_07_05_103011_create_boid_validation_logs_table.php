<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boid_validation_logs', function (Blueprint $table) {
            $table->id();
            $table->string('boid');
            $table->foreignId('requested_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('result')->nullable();
            $table->text('message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boid_validation_logs');
    }
};
