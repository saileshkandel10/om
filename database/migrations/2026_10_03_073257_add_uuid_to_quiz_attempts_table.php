<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
        });

        // Populate existing attempts with UUIDs
        $attempts = DB::table('quiz_attempts')->get();
        foreach ($attempts as $att) {
            DB::table('quiz_attempts')->where('id', $att->id)->update([
                'uuid' => (string) Str::uuid(),
            ]);
        }

        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->unique('uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropUnique(['uuid']);
            $table->dropColumn('uuid');
        });
    }
};
