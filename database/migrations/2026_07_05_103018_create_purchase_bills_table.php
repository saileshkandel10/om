<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pharmacy_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('uploaded_file_id')->nullable()->constrained()->nullOnDelete();
            $table->string('invoice_number')->nullable();
            $table->date('invoice_date')->nullable();
            $table->decimal('subtotal_amount', 15, 2)->nullable();
            $table->decimal('tax_amount', 15, 2)->nullable();
            $table->decimal('total_amount', 15, 2)->nullable();
            $table->string('ocr_status')->nullable();
            $table->string('validation_status')->nullable();
            $table->decimal('confidence_score', 5, 2)->nullable();
            $table->boolean('duplicate_flag')->default(false);
            $table->boolean('fraud_flag')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_bills');
    }
};
