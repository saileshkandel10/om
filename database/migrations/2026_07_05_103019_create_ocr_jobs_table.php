<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ocr_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('module_name')->nullable();
            $table->foreignId('source_file_id')->nullable()->constrained('uploaded_files')->nullOnDelete();
            $table->string('status')->nullable();
            $table->string('engine_name')->nullable();
            $table->longText('raw_text')->nullable();
            $table->json('parsed_json')->nullable();
            $table->decimal('confidence_score', 5, 2)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ocr_jobs');
    }
};
