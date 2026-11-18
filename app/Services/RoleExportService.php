<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\CSV\Writer as CsvWriter;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;

class RoleExportService
{
    /**
     * Export all system roles summary.
     */
    public function exportRoles(string $filePath, string $format = 'xlsx'): void
    {
        $writer = ($format === 'csv') ? new CsvWriter : new XlsxWriter;
        $writer->openToFile($filePath);

        // Header
        $writer->addRow(Row::fromValues([
            'Role Slug',
            'Display Name',
            'Description',
            'Role Type',
            'Assigned Users Count',
            'Permissions Count',
            'Permissions List',
            'Created Date',
        ]));

        $roles = Role::withCount('users')->orderBy('id')->get();

        foreach ($roles as $role) {
            $perms = $role->permissions ?? [];
            $writer->addRow(Row::fromValues([
                $role->name,
                $role->display_name ?: ucfirst($role->name),
                $role->description ?? '',
                $role->is_system ? 'System Core' : 'Custom',
                $role->users_count,
                count($perms),
                implode(', ', $perms),
                $role->created_at ? $role->created_at->format('Y-m-d H:i') : '',
            ]));
        }

        $writer->close();
    }

    /**
     * Export all users assigned to a specific role.
     */
    public function exportRoleUsers(string $filePath, Role $role, string $format = 'xlsx'): void
    {
        $writer = ($format === 'csv') ? new CsvWriter : new XlsxWriter;
        $writer->openToFile($filePath);

        $writer->addRow(Row::fromValues([
            'User ID',
            'Name',
            'Email',
            'Mobile / Phone',
            'Status',
            'Role Assigned',
            'All Assigned Roles',
            'Registration Date',
        ]));

        $role->users()->with(['profile', 'roles'])->chunk(500, function ($users) use ($writer, $role) {
            foreach ($users as $user) {
                $writer->addRow(Row::fromValues([
                    'USR-'.(1000 + $user->id),
                    $user->name,
                    $user->email,
                    $user->mobile ?? ($user->profile?->phone ?? ''),
                    ucfirst($user->status ?? 'Active'),
                    $role->display_name ?: ucfirst($role->name),
                    $user->roles->pluck('name')->implode(', '),
                    $user->created_at ? $user->created_at->format('Y-m-d H:i') : '',
                ]));
            }
        });

        $writer->close();
    }

    /**
     * Export complete user-to-role assignment matrix for all users.
     */
    public function exportUserRoleMatrix(string $filePath, string $format = 'xlsx'): void
    {
        $writer = ($format === 'csv') ? new CsvWriter : new XlsxWriter;
        $writer->openToFile($filePath);

        $roles = Role::orderBy('id')->get();
        $roleNames = $roles->pluck('name')->toArray();

        // Header Row
        $header = array_merge([
            'User ID',
            'Full Name',
            'Email',
            'Mobile',
            'Status',
            'Assigned Roles Count',
            'All Roles',
        ], array_map(fn ($r) => 'Has Role: '.ucfirst($r), $roleNames), ['Registered At']);

        $writer->addRow(Row::fromValues($header));

        User::with('roles')->chunk(500, function ($users) use ($writer, $roles) {
            foreach ($users as $user) {
                $userRoleSlugs = $user->roles->pluck('name')->map(fn ($n) => strtolower($n))->toArray();

                $row = [
                    'USR-'.(1000 + $user->id),
                    $user->name,
                    $user->email,
                    $user->mobile ?? '',
                    ucfirst($user->status ?? 'Active'),
                    $user->roles->count(),
                    $user->roles->pluck('name')->implode(', '),
                ];

                foreach ($roles as $r) {
                    $row[] = in_array(strtolower($r->name), $userRoleSlugs, true) ? 'YES' : 'NO';
                }

                $row[] = $user->created_at ? $user->created_at->format('Y-m-d H:i') : '';

                $writer->addRow(Row::fromValues($row));
            }
        });

        $writer->close();
    }

    /**
     * Export role vs permission capabilities matrix.
     */
    public function exportPermissionsMatrix(string $filePath, string $format = 'xlsx'): void
    {
        $writer = ($format === 'csv') ? new CsvWriter : new XlsxWriter;
        $writer->openToFile($filePath);

        $roles = Role::orderBy('id')->get();
        $roleLabels = $roles->map(fn ($r) => $r->display_name ?: ucfirst($r->name))->toArray();

        $header = array_merge([
            'Module Category',
            'Permission Key',
            'Permission Description',
        ], $roleLabels);

        $writer->addRow(Row::fromValues($header));

        $groups = Role::availablePermissionGroups();

        foreach ($groups as $groupName => $perms) {
            foreach ($perms as $permKey => $permDesc) {
                $row = [
                    $groupName,
                    $permKey,
                    $permDesc,
                ];

                foreach ($roles as $role) {
                    $has = $role->hasPermission($permKey);
                    $row[] = $has ? 'GRANTED' : '-';
                }

                $writer->addRow(Row::fromValues($row));
            }
        }

        $writer->close();
    }
}
