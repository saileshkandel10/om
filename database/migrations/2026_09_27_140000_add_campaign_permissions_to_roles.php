<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $allPerms = [];
        foreach (Role::availablePermissionGroups() as $group => $perms) {
            foreach ($perms as $k => $v) {
                $allPerms[] = $k;
            }
        }

        // Admin gets all
        DB::table('roles')->where('name', 'admin')->update([
            'permissions' => json_encode($allPerms),
        ]);

        // Staff gets campaigns & notices
        $staff = DB::table('roles')->where('name', 'staff')->first();
        if ($staff) {
            $staffPerms = json_decode($staff->permissions ?? '[]', true) ?: [];
            $staffPerms = array_values(array_unique(array_merge($staffPerms, ['campaigns.view', 'campaigns.manage', 'notices.view', 'notices.manage'])));
            DB::table('roles')->where('name', 'staff')->update(['permissions' => json_encode($staffPerms)]);
        }

        // Audience roles get notices.view
        foreach (['shareholder', 'pharmacy', 'doctor', 'customer', 'nurse', 'receptionist', 'auditor'] as $rName) {
            $r = DB::table('roles')->where('name', $rName)->first();
            if ($r) {
                $perms = json_decode($r->permissions ?? '[]', true) ?: [];
                $perms = array_values(array_unique(array_merge($perms, ['notices.view'])));
                DB::table('roles')->where('name', $rName)->update(['permissions' => json_encode($perms)]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
