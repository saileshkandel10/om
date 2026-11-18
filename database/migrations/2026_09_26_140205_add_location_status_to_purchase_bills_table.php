<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_bills', function (Blueprint $table) {
            $table->string('location_status')->default('pending')->index()->after('district_id');
            $table->text('location_notes')->nullable()->after('location_status');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_bills', function (Blueprint $table) {
            $table->dropColumn(['location_status', 'location_notes']);
        });
    }
};
