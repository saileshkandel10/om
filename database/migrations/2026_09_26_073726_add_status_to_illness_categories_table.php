<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('illness_categories', function (Blueprint $table) {
            if (! Schema::hasColumn('illness_categories', 'status')) {
                $table->string('status')->default('active')->after('description')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('illness_categories', function (Blueprint $table) {
            if (Schema::hasColumn('illness_categories', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
