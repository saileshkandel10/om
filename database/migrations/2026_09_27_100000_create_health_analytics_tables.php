<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Health Categories Table
        Schema::create('health_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->default('iconoir-activity');
            $table->string('color')->default('#CD0B6F');
            $table->string('status')->default('active'); // active, inactive
            $table->timestamps();
        });

        // 2. Canonical Medicines Table
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('brand_name')->index();
            $table->string('generic_name')->index();
            $table->string('active_ingredients')->nullable();
            $table->string('strength')->nullable();
            $table->string('dosage_form')->nullable(); // Tablet, Capsule, Syrup, Injection
            $table->string('therapeutic_class')->nullable()->index();
            $table->string('manufacturer')->nullable();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index(['brand_name', 'generic_name']);
        });

        // 3. Medicine Indications Mapping Table
        Schema::create('medicine_indications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->constrained('medicines')->cascadeOnDelete();
            $table->foreignId('health_category_id')->constrained('health_categories')->cascadeOnDelete();
            $table->string('evidence_level')->default('High'); // High, Moderate, Low
            $table->text('notes')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['medicine_id', 'health_category_id']);
        });

        // 4. Patient Health Analytics Table
        Schema::create('patient_health_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('health_category_id')->constrained('health_categories')->cascadeOnDelete();
            $table->string('association_level')->default('Possible Association'); // Weak, Possible, Moderate, Strong
            $table->unsignedInteger('supporting_medicines_count')->default(1);
            $table->unsignedInteger('supporting_records_count')->default(1);
            $table->date('first_detected_date')->nullable();
            $table->date('latest_detected_date')->nullable();
            $table->text('explanation')->nullable();
            $table->string('status')->default('pending_review'); // pending_review, confirmed_pattern, not_applicable, needs_review
            $table->timestamps();

            $table->unique(['patient_id', 'health_category_id']);
            $table->index(['patient_id', 'status']);
            $table->index(['health_category_id', 'association_level'], 'pha_cat_assoc_idx');
        });

        // 5. Health Analytics Evidence Table
        Schema::create('health_analytics_evidence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('health_analytic_id')->constrained('patient_health_analytics')->cascadeOnDelete();
            $table->string('source_type')->default('purchase_bill'); // purchase_bill, prescription, manual
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('medicine_name');
            $table->string('generic_name')->nullable();
            $table->date('detected_date')->nullable();
            $table->string('source_reference')->nullable(); // e.g. "Bill #10291", "Prescription #P-8821"
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['health_analytic_id', 'detected_date']);
            $table->index(['source_type', 'source_id']);
        });

        // 6. Clinical Reviews Table
        Schema::create('clinical_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('health_analytic_id')->constrained('patient_health_analytics')->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->string('decision'); // confirmed_pattern, not_applicable, needs_review
            $table->text('comments')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['health_analytic_id', 'decision']);
        });

        // 7. Health Analytics Audit Logs Table
        Schema::create('health_analytics_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('patient_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action'); // Viewed Patient Health Analytics, Generated Analysis, etc.
            $table->string('module')->default('health_analytics');
            $table->json('details')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['admin_id', 'created_at']);
            $table->index(['patient_id', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_analytics_logs');
        Schema::dropIfExists('clinical_reviews');
        Schema::dropIfExists('health_analytics_evidence');
        Schema::dropIfExists('patient_health_analytics');
        Schema::dropIfExists('medicine_indications');
        Schema::dropIfExists('medicines');
        Schema::dropIfExists('health_categories');
    }
};
