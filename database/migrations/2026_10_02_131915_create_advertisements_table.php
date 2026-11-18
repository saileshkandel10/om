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
        Schema::create('advertisements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type', 20)->default('text'); // 'image' or 'text'
            $table->string('image_path')->nullable();
            $table->string('background_color', 50)->nullable()->default('#1E3A5F');
            $table->string('text_color', 50)->nullable()->default('#FFFFFF');
            $table->string('button_color', 50)->nullable()->default('#CD0B6F');
            $table->string('button_text_color', 50)->nullable()->default('#FFFFFF');
            $table->string('cta_text')->nullable();
            $table->string('cta_action')->nullable();
            $table->string('target_role', 50)->default('all'); // 'all', 'patient', 'doctor', 'pharmacy', 'shareholder'
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('impressions_count')->default(0);
            $table->unsignedBigInteger('clicks_count')->default(0);
            $table->timestamps();

            $table->index(['target_role', 'is_active']);
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advertisements');
    }
};
