<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->string('patient_ref_code')->nullable();
            $table->date('prescription_date')->nullable();
            $table->string('source_type')->nullable();
            $table->foreignId('uploaded_file_id')->nullable()->constrained()->nullOnDelete();
            $table->string('parsed_status')->nullable();
            $table->decimal('confidence_score', 5, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
