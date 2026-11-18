<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shareholder_masters', function (Blueprint $table) {
            $table->id();
            $table->string('boid')->unique();
            $table->string('shareholder_name');
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->string('status')->nullable();
            $table->text('source_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shareholder_masters');
    }
};
