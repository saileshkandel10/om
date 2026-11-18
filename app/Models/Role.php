<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'color',
        'permissions',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'is_system' => 'boolean',
        ];
    }

    /**
     * Users assigned to this role.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_has_roles');
    }

    /**
     * Check if the role contains a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        if (strtolower($this->name) === 'admin') {
            return true;
        }

        $perms = $this->permissions ?? [];

        return in_array($permission, $perms, true) || in_array('*', $perms, true);
    }

    /**
     * Get human-friendly label for display.
     */
    public function getLabelAttribute(): string
    {
        return $this->display_name ?: ucfirst(str_replace('_', ' ', $this->name));
    }

    /**
     * All system permission definitions organized by module.
     */
    public static function availablePermissionGroups(): array
    {
        return [
            'Dashboard & Overview' => [
                'dashboard.view' => 'View Dashboard & Global KPI Summary',
            ],
            'User & Stakeholder Management' => [
                'users.view' => 'View User Directory',
                'users.create' => 'Create New Users',
                'users.edit' => 'Edit User Details',
                'users.delete' => 'Delete Users',
                'shareholders.view' => 'View Shareholder Portfolio',
                'shareholders.manage' => 'Manage Shareholder Accounts & Imports',
                'pharmacies.view' => 'View Partner Pharmacies',
                'pharmacies.manage' => 'Manage Pharmacies & Sourcing',
                'doctors.view' => 'View Doctors Directory',
                'doctors.manage' => 'Manage Doctors & Specializations',
            ],
            'Product & Market Catalog' => [
                'products.view' => 'View Internal Product Catalog',
                'products.manage' => 'Manage Products, Categories & Batches',
                'other_products.view' => 'View Market Other Products',
                'other_products.manage' => 'Bulk Import & Map Market Products',
                'illness_categories.view' => 'View Illness Categories',
                'illness_categories.manage' => 'Manage Illness Categories & Product Links',
            ],
            'Bills, OCR & Prescriptions' => [
                'purchase_bills.view' => 'View Purchase Bills',
                'purchase_bills.verify' => 'Verify & Approve Purchase Bills',
                'purchase_bills.ocr' => 'Trigger & Review OCR Data',
                'prescriptions.view' => 'View Patient Prescriptions',
                'prescriptions.manage' => 'Create & Edit Prescriptions',
            ],
            'Health Analytics & Reports' => [
                'health_analytics.view' => 'View Health Analytics & Pattern Engine',
                'health_analytics.manage' => 'Configure Pattern Rules & Clinical Review',
                'patient_reports.view' => 'View Patient Illness Reports',
                'patient_reports.export' => 'Export Patient Illness & Analytics Reports',
            ],
            'Financials & Loyalty Rewards' => [
                'financials.view' => 'View Reward Transactions & Statements',
                'financials.manage' => 'Process Royalty Payouts & Adjust Wallets',
            ],
            'Campaigns & Communication' => [
                'campaigns.view' => 'View Campaigns & Target Recipient Lists',
                'campaigns.manage' => 'Create, Edit & Launch Marketing Campaigns',
                'notices.view' => 'View System Notices & Announcements',
                'notices.manage' => 'Create, Edit, Publish & Dispatch Notices',
            ],
            'System Settings & Governance' => [
                'locations.view' => 'View Districts & Locations',
                'locations.manage' => 'Normalize & Map Location Data',
                'system_settings.view' => 'View System Settings',
                'system_settings.manage' => 'Update System Configuration & Rules',
                'audit_logs.view' => 'Inspect System Audit Logs',
            ],
        ];
    }
}
