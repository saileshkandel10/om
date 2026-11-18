<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('provinces')) {
            Schema::create('provinces', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('code')->unique();
                $table->string('status')->default('active')->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('districts')) {
            Schema::create('districts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('province_id')->constrained('provinces')->cascadeOnDelete();
                $table->string('name')->unique();
                $table->string('code')->nullable();
                $table->string('status')->default('active')->index();
                $table->timestamps();

                $table->index(['province_id', 'status']);
            });
        }

        Schema::table('user_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('user_profiles', 'province_id')) {
                $table->foreignId('province_id')->nullable()->after('gender')->constrained('provinces')->nullOnDelete();
            }
            if (! Schema::hasColumn('user_profiles', 'district_id')) {
                $table->foreignId('district_id')->nullable()->after('province_id')->constrained('districts')->nullOnDelete();
            }
            if (! Schema::hasColumn('user_profiles', 'municipality')) {
                $table->string('municipality')->nullable()->after('district_id');
            }
        });

        Schema::table('purchase_bills', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_bills', 'province_id')) {
                $table->foreignId('province_id')->nullable()->after('pharmacy_id')->constrained('provinces')->nullOnDelete();
            }
            if (! Schema::hasColumn('purchase_bills', 'district_id')) {
                $table->foreignId('district_id')->nullable()->after('province_id')->constrained('districts')->nullOnDelete();
            }
            if (! Schema::hasColumn('purchase_bills', 'order_status')) {
                $table->string('order_status')->default('delivered')->index()->after('validation_status');
            }
            if (! Schema::hasColumn('purchase_bills', 'payment_status')) {
                $table->string('payment_status')->default('paid')->index()->after('order_status');
            }
        });

        Schema::table('pharmacies', function (Blueprint $table) {
            if (! Schema::hasColumn('pharmacies', 'province_id')) {
                $table->foreignId('province_id')->nullable()->after('owner_name')->constrained('provinces')->nullOnDelete();
            }
            if (! Schema::hasColumn('pharmacies', 'district_id')) {
                $table->foreignId('district_id')->nullable()->after('province_id')->constrained('districts')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('pharmacies', function (Blueprint $table) {
            $table->dropConstrainedForeignId('district_id');
            $table->dropConstrainedForeignId('province_id');
        });

        Schema::table('purchase_bills', function (Blueprint $table) {
            $table->dropConstrainedForeignId('district_id');
            $table->dropConstrainedForeignId('province_id');
            $table->dropColumn(['order_status', 'payment_status']);
        });

        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('district_id');
            $table->dropConstrainedForeignId('province_id');
            $table->dropColumn('municipality');
        });

        Schema::dropIfExists('districts');
        Schema::dropIfExists('provinces');
    }
};
