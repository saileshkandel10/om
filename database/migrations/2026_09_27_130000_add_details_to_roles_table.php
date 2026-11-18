<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->string('display_name')->nullable()->after('name');
            $table->text('description')->nullable()->after('display_name');
            $table->string('color')->default('primary')->after('description');
            $table->json('permissions')->nullable()->after('color');
            $table->boolean('is_system')->default(false)->after('permissions');
        });

        // Seed/Update standard system roles with rich metadata and permissions
        $systemRoles = [
            [
                'name' => 'admin',
                'display_name' => 'Super Administrator',
                'description' => 'Full administrative control over all system modules, configurations, users, and audit logs.',
                'color' => 'danger',
                'is_system' => true,
                'permissions' => [
                    'dashboard.view', 'users.view', 'users.create', 'users.edit', 'users.delete',
                    'shareholders.view', 'shareholders.manage', 'pharmacies.view', 'pharmacies.manage',
                    'doctors.view', 'doctors.manage', 'products.view', 'products.manage',
                    'other_products.view', 'other_products.manage', 'illness_categories.view', 'illness_categories.manage',
                    'purchase_bills.view', 'purchase_bills.verify', 'purchase_bills.ocr',
                    'prescriptions.view', 'prescriptions.manage', 'health_analytics.view', 'health_analytics.manage',
                    'patient_reports.view', 'patient_reports.export', 'financials.view', 'financials.manage',
                    'locations.view', 'locations.manage', 'system_settings.view', 'system_settings.manage', 'audit_logs.view',
                ],
            ],
            [
                'name' => 'staff',
                'display_name' => 'Operations Staff',
                'description' => 'Front-office and operations staff capable of managing bills, customer verifications, and master records.',
                'color' => 'primary',
                'is_system' => true,
                'permissions' => [
                    'dashboard.view', 'users.view', 'shareholders.view', 'pharmacies.view', 'doctors.view',
                    'products.view', 'products.manage', 'other_products.view', 'illness_categories.view',
                    'purchase_bills.view', 'purchase_bills.verify', 'purchase_bills.ocr',
                    'prescriptions.view', 'patient_reports.view', 'patient_reports.export', 'locations.view',
                ],
            ],
            [
                'name' => 'shareholder',
                'display_name' => 'Shareholder / Investor',
                'description' => 'Investor portal access to view shareholding, portfolio valuation, statement downloads, and dividend reports.',
                'color' => 'secondary',
                'is_system' => true,
                'permissions' => [
                    'dashboard.view', 'shareholders.view', 'financials.view',
                ],
            ],
            [
                'name' => 'pharmacy',
                'display_name' => 'Partner Pharmacy',
                'description' => 'Pharmacy partner access for bill uploads, medicine sourcing, customer purchase validation, and reward claims.',
                'color' => 'success',
                'is_system' => true,
                'permissions' => [
                    'dashboard.view', 'products.view', 'purchase_bills.view', 'purchase_bills.ocr', 'financials.view',
                ],
            ],
            [
                'name' => 'doctor',
                'display_name' => 'Medical Practitioner (Doctor)',
                'description' => 'Licensed physician access for patient diagnoses, electronic prescriptions, and clinical illness analytics.',
                'color' => 'info',
                'is_system' => true,
                'permissions' => [
                    'dashboard.view', 'prescriptions.view', 'prescriptions.manage', 'illness_categories.view',
                    'health_analytics.view', 'patient_reports.view',
                ],
            ],
            [
                'name' => 'customer',
                'display_name' => 'Patient / Customer',
                'description' => 'Patient user access to the mobile/web portal for uploading pharmacy bills, viewing reward points, and health history.',
                'color' => 'warning',
                'is_system' => true,
                'permissions' => [
                    'dashboard.view', 'purchase_bills.view', 'financials.view',
                ],
            ],
            [
                'name' => 'receptionist',
                'display_name' => 'Receptionist / Front Desk',
                'description' => 'Front desk reception for patient check-in, initial purchase bill intake, and customer lookups.',
                'color' => 'dark',
                'is_system' => true,
                'permissions' => [
                    'dashboard.view', 'users.view', 'users.create', 'purchase_bills.view', 'purchase_bills.ocr',
                ],
            ],
            [
                'name' => 'lab_technician',
                'display_name' => 'Laboratory Technician',
                'description' => 'Medical lab staff for entering test results, LIMS test management, and diagnostic reports.',
                'color' => 'info',
                'is_system' => true,
                'permissions' => [
                    'dashboard.view', 'patient_reports.view', 'patient_reports.export', 'health_analytics.view',
                ],
            ],
            [
                'name' => 'inventory_manager',
                'display_name' => 'Inventory & Product Manager',
                'description' => 'Manages internal product catalog, market other products, categories, batches, and supplier stock.',
                'color' => 'primary',
                'is_system' => true,
                'permissions' => [
                    'dashboard.view', 'products.view', 'products.manage', 'other_products.view', 'other_products.manage',
                    'illness_categories.view', 'illness_categories.manage',
                ],
            ],
            [
                'name' => 'accountant',
                'display_name' => 'Finance & Accountant',
                'description' => 'Financial operations, wallet management, reward royalty payouts, and shareholder dividend audits.',
                'color' => 'success',
                'is_system' => true,
                'permissions' => [
                    'dashboard.view', 'financials.view', 'financials.manage', 'shareholders.view',
                    'purchase_bills.view', 'audit_logs.view',
                ],
            ],
            [
                'name' => 'nurse',
                'display_name' => 'Clinical Nurse',
                'description' => 'Clinical nursing staff for recording patient vitals, illness tracking, and prescription monitoring.',
                'color' => 'pink',
                'is_system' => true,
                'permissions' => [
                    'dashboard.view', 'prescriptions.view', 'illness_categories.view', 'patient_reports.view',
                ],
            ],
            [
                'name' => 'auditor',
                'display_name' => 'Compliance & Auditor',
                'description' => 'Internal auditor with read-only oversight across transactions, fraud flags, and system audit logs.',
                'color' => 'danger',
                'is_system' => true,
                'permissions' => [
                    'dashboard.view', 'audit_logs.view', 'purchase_bills.view', 'financials.view',
                    'health_analytics.view', 'patient_reports.view', 'patient_reports.export',
                ],
            ],
        ];

        foreach ($systemRoles as $roleData) {
            $existing = DB::table('roles')->whereRaw('LOWER(name) = ?', [strtolower($roleData['name'])])->first();

            if ($existing) {
                DB::table('roles')->where('id', $existing->id)->update([
                    'display_name' => $roleData['display_name'],
                    'description' => $roleData['description'],
                    'color' => $roleData['color'],
                    'permissions' => json_encode($roleData['permissions']),
                    'is_system' => true,
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('roles')->insert([
                    'name' => $roleData['name'],
                    'display_name' => $roleData['display_name'],
                    'description' => $roleData['description'],
                    'color' => $roleData['color'],
                    'permissions' => json_encode($roleData['permissions']),
                    'is_system' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['display_name', 'description', 'color', 'permissions', 'is_system']);
        });
    }
};
