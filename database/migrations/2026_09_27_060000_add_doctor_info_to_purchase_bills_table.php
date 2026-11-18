<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_bills', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_bills', 'doctor_name')) {
                $table->string('doctor_name')->nullable();
            }
            if (! Schema::hasColumn('purchase_bills', 'doctor_nmc')) {
                $table->string('doctor_nmc')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_bills', function (Blueprint $table) {
            $table->dropColumn(['doctor_name', 'doctor_nmc']);
        });
    }
};
