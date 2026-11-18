<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\RoleExportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RoleController extends Controller
{
    public function __construct(
        protected RoleExportService $exportService
    ) {}

    /**
     * Display all roles and the comprehensive permissions matrix.
     */
    public function index(Request $request): View
    {
        $tab = $request->input('tab', 'roles');

        $query = Role::withCount('users')->orderBy('id');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('display_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $roles = $query->get();
        $permissionGroups = Role::availablePermissionGroups();

        // Metrics Summary
        $stats = [
            'total_roles' => Role::count(),
            'system_roles' => Role::where('is_system', true)->count(),
            'custom_roles' => Role::where('is_system', false)->count(),
            'total_assigned_users' => User::whereHas('roles')->count(),
        ];

        return view('admin.roles.index', compact('roles', 'permissionGroups', 'stats', 'tab'));
    }

    /**
     * Store a newly created custom role.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:roles,name', 'regex:/^[a-zA-Z0-9_\-]+$/'],
            'display_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'color' => ['nullable', 'string', 'in:primary,secondary,success,danger,warning,info,dark,pink'],
            'permissions' => ['nullable', 'array'],
        ]);

        $role = Role::create([
            'name' => strtolower($validated['name']),
            'display_name' => $validated['display_name'],
            'description' => $validated['description'] ?? null,
            'color' => $validated['color'] ?? 'primary',
            'permissions' => $validated['permissions'] ?? [],
            'is_system' => false,
        ]);

        return back()->with('success', __('Custom role ":name" created successfully with :count permissions.', [
            'name' => $role->display_name,
            'count' => count($role->permissions ?? []),
        ]));
    }

    /**
     * Show details for a specific role and its assigned users.
     */
    public function show(Role $role, Request $request): View
    {
        $role->loadCount('users');

        $usersQuery = $role->users()->with(['profile', 'roles'])->latest('users.created_at');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $usersQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        $users = $usersQuery->paginate(25)->withQueryString();
        $permissionGroups = Role::availablePermissionGroups();

        return view('admin.roles.show', compact('role', 'users', 'permissionGroups'));
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role): View
    {
        $role->loadCount('users');
        $permissionGroups = Role::availablePermissionGroups();

        return view('admin.roles.edit', compact('role', 'permissionGroups'));
    }

    /**
     * Update role details and permissions.
     */
    public function update(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'color' => ['nullable', 'string', 'in:primary,secondary,success,danger,warning,info,dark,pink'],
            'permissions' => ['nullable', 'array'],
        ]);

        // If not a system core role, allow updating the slug name
        if (! $role->is_system && $request->filled('name')) {
            $nameVal = $request->validate([
                'name' => ['required', 'string', 'max:100', 'unique:roles,name,'.$role->id, 'regex:/^[a-zA-Z0-9_\-]+$/'],
            ]);
            $role->name = strtolower($nameVal['name']);
        }

        $role->display_name = $validated['display_name'];
        $role->description = $validated['description'] ?? null;
        $role->color = $validated['color'] ?? $role->color;
        $role->permissions = $validated['permissions'] ?? [];
        $role->save();

        return to_route('admin.roles.index')->with('success', __('Role ":name" updated successfully.', ['name' => $role->display_name]));
    }

    /**
     * Delete a custom role.
     */
    public function destroy(Role $role): RedirectResponse
    {
        if ($role->is_system) {
            return back()->with('error', __('Core system role ":name" cannot be deleted.', ['name' => $role->display_name]));
        }

        $usersCount = $role->users()->count();
        if ($usersCount > 0) {
            return back()->with('error', __('Cannot delete role ":name" because it is assigned to :count active user(s). Reassign users before deleting.', [
                'name' => $role->display_name,
                'count' => $usersCount,
            ]));
        }

        $name = $role->display_name;
        $role->delete();

        return to_route('admin.roles.index')->with('success', __('Role ":name" deleted successfully.', ['name' => $name]));
    }

    /**
     * Update permissions for a specific role directly.
     */
    public function updatePermissions(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
        ]);

        $role->update([
            'permissions' => $validated['permissions'] ?? [],
        ]);

        return back()->with('success', __('Permissions for role ":name" updated successfully.', ['name' => $role->display_name]));
    }

    /**
     * Universal export handler for roles, users, and permissions matrices.
     */
    public function export(Request $request): BinaryFileResponse
    {
        $type = $request->input('type', 'roles'); // roles, role_users, user_matrix, permissions
        $format = $request->input('format', 'xlsx') === 'csv' ? 'csv' : 'xlsx';
        $timestamp = date('Y_m_d_His');

        switch ($type) {
            case 'role_users':
                $roleId = $request->input('role_id');
                $role = Role::findOrFail($roleId);
                $filename = 'role_users_'.Str::slug($role->name).'_'.$timestamp.'.'.$format;
                $tempPath = storage_path('app/temp_'.$filename);
                $this->exportService->exportRoleUsers($tempPath, $role, $format);
                break;

            case 'user_matrix':
                $filename = 'users_role_matrix_'.$timestamp.'.'.$format;
                $tempPath = storage_path('app/temp_'.$filename);
                $this->exportService->exportUserRoleMatrix($tempPath, $format);
                break;

            case 'permissions':
                $filename = 'role_permissions_matrix_'.$timestamp.'.'.$format;
                $tempPath = storage_path('app/temp_'.$filename);
                $this->exportService->exportPermissionsMatrix($tempPath, $format);
                break;

            case 'roles':
            default:
                $filename = 'roles_overview_'.$timestamp.'.'.$format;
                $tempPath = storage_path('app/temp_'.$filename);
                $this->exportService->exportRoles($tempPath, $format);
                break;
        }

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }
}
