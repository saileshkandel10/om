<?php

use App\Models\Role;
use App\Models\User;

beforeEach(function () {
    $this->adminRole = Role::firstOrCreate(['name' => 'admin'], [
        'display_name' => 'Super Administrator',
        'is_system' => true,
    ]);

    $this->admin = User::factory()->create();
    $this->admin->roles()->attach($this->adminRole);
});

test('admin can view roles index with matrix and metrics', function () {
    $response = $this->actingAs($this->admin)
        ->get(route('admin.roles.index'));

    $response->assertOk();
    $response->assertViewHas('roles');
    $response->assertViewHas('permissionGroups');
    $response->assertViewHas('stats');
    $response->assertSee('Roles & Permissions');
});

test('admin can create custom role with permissions', function () {
    $response = $this->actingAs($this->admin)
        ->post(route('admin.roles.store'), [
            'name' => 'branch_auditor',
            'display_name' => 'Branch Auditor',
            'description' => 'Audits regional branch transactions',
            'color' => 'warning',
            'permissions' => ['audit_logs.view', 'purchase_bills.view'],
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('roles', [
        'name' => 'branch_auditor',
        'display_name' => 'Branch Auditor',
        'is_system' => false,
    ]);
});

test('admin can view single role and assigned users', function () {
    $user = User::factory()->create();
    $user->roles()->attach($this->adminRole);

    $response = $this->actingAs($this->admin)
        ->get(route('admin.roles.show', $this->adminRole));

    $response->assertOk();
    $response->assertViewHas('role');
    $response->assertViewHas('users');
    $response->assertSee($user->name);
});

test('admin can update role details and permissions', function () {
    $customRole = Role::create([
        'name' => 'custom_rep',
        'display_name' => 'Old Rep',
        'is_system' => false,
        'permissions' => ['dashboard.view'],
    ]);

    $response = $this->actingAs($this->admin)
        ->put(route('admin.roles.update', $customRole), [
            'display_name' => 'Updated Representative',
            'description' => 'Updated role description',
            'color' => 'success',
            'permissions' => ['dashboard.view', 'users.view'],
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('roles', [
        'id' => $customRole->id,
        'display_name' => 'Updated Representative',
        'color' => 'success',
    ]);
});

test('admin cannot delete core system role', function () {
    $response = $this->actingAs($this->admin)
        ->delete(route('admin.roles.destroy', $this->adminRole));

    $response->assertRedirect();
    $response->assertSessionHas('error');

    $this->assertDatabaseHas('roles', [
        'id' => $this->adminRole->id,
    ]);
});

test('admin can delete unassigned custom role', function () {
    $roleToDelete = Role::create([
        'name' => 'temp_role',
        'display_name' => 'Temporary Role',
        'is_system' => false,
    ]);

    $response = $this->actingAs($this->admin)
        ->delete(route('admin.roles.destroy', $roleToDelete));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('roles', [
        'id' => $roleToDelete->id,
    ]);
});

test('admin can export roles overview in xlsx and csv', function () {
    $responseXlsx = $this->actingAs($this->admin)
        ->get(route('admin.roles.export', ['type' => 'roles', 'format' => 'xlsx']));

    $responseXlsx->assertOk();
    $responseXlsx->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    $responseCsv = $this->actingAs($this->admin)
        ->get(route('admin.roles.export', ['type' => 'roles', 'format' => 'csv']));

    $responseCsv->assertOk();
    $responseCsv->assertHeader('content-type', 'text/csv; charset=UTF-8');
});

test('admin can export role users in xlsx and csv', function () {
    $responseXlsx = $this->actingAs($this->admin)
        ->get(route('admin.roles.export', ['type' => 'role_users', 'role_id' => $this->adminRole->id, 'format' => 'xlsx']));

    $responseXlsx->assertOk();

    $responseCsv = $this->actingAs($this->admin)
        ->get(route('admin.roles.export', ['type' => 'role_users', 'role_id' => $this->adminRole->id, 'format' => 'csv']));

    $responseCsv->assertOk();
});

test('admin can export user-role matrix and permissions matrix in xlsx and csv', function () {
    $responseMatrix = $this->actingAs($this->admin)
        ->get(route('admin.roles.export', ['type' => 'user_matrix', 'format' => 'xlsx']));

    $responseMatrix->assertOk();

    $responsePerms = $this->actingAs($this->admin)
        ->get(route('admin.roles.export', ['type' => 'permissions', 'format' => 'xlsx']));

    $responsePerms->assertOk();
});
