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
        Schema::table('patient_illnesses', function (Blueprint $table) {
            if (! Schema::hasColumn('patient_illnesses', 'status')) {
                $table->string('status')->default('active')->index()->after('notes');
            }
            if (! Schema::hasColumn('patient_illnesses', 'diagnosed_by')) {
                $table->string('diagnosed_by')->nullable()->after('notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('patient_illnesses', function (Blueprint $table) {
            if (Schema::hasColumn('patient_illnesses', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('patient_illnesses', 'diagnosed_by')) {
                $table->dropColumn('diagnosed_by');
            }
        });
    }
};
