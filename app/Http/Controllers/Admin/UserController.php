<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Pharmacy;
use App\Models\Role;
use App\Models\ShareholderAccount;
use App\Models\ShareholderMaster;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::with('roles')->withCount('purchaseBills');

        if ($request->filled('role')) {
            $query->whereHas('roles', fn ($q) => $q->whereRaw('LOWER(name) = ?', [strtolower($request->role)]));
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        $users = $query->latest()->get();
        $roles = Role::orderBy('name')->get();

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function create(): View
    {
        $roles = Role::orderBy('name')->get();

        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'string', 'in:active,inactive,suspended'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'mobile' => $validated['mobile'] ?? null,
            'status' => $validated['status'],
        ]);

        if ($request->has('roles')) {
            $user->roles()->sync($request->roles);

            $shareholderRole = Role::whereRaw('LOWER(name) = ?', ['shareholder'])->first();
            if ($shareholderRole && in_array($shareholderRole->id, $request->roles)) {
                $master = ShareholderMaster::create([
                    'boid' => 'AUTO-'.$user->id,
                    'shareholder_name' => $user->name,
                    'mobile' => $user->mobile,
                    'email' => $user->email,
                    'status' => 'active',
                    'source_note' => 'Auto-created from admin user creation',
                ]);

                ShareholderAccount::create([
                    'user_id' => $user->id,
                    'shareholder_master_id' => $master->id,
                    'boid' => $master->boid,
                    'verification_status' => 'pending',
                ]);
            }

            $pharmacyRole = Role::whereRaw('LOWER(name) = ?', ['pharmacy'])->first();
            if ($pharmacyRole && in_array($pharmacyRole->id, $request->roles)) {
                Pharmacy::create([
                    'user_id' => $user->id,
                    'store_name' => $user->name,
                    'status' => 'pending',
                ]);
            }

            $doctorRole = Role::whereRaw('LOWER(name) = ?', ['doctor'])->first();
            if ($doctorRole && in_array($doctorRole->id, $request->roles)) {
                Doctor::create([
                    'user_id' => $user->id,
                    'status' => 'pending',
                ]);
            }
        }

        return to_route('admin.users.index')->with('success', __('User created successfully.'));
    }

    public function show(User $user): View
    {
        $user->load(['roles', 'userProfile', 'rewardWallet', 'purchaseBills' => fn ($q) => $q->latest()->limit(10)]);

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        $user->load('roles');

        $roles = Role::orderBy('name')->get();

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'status' => ['nullable', 'string', 'in:active,inactive,suspended'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        $user->update($validated);

        if ($request->has('roles')) {
            $user->roles()->sync($request->roles);

            $shareholderRole = Role::whereRaw('LOWER(name) = ?', ['shareholder'])->first();
            if ($shareholderRole && in_array($shareholderRole->id, $request->roles) && ! $user->shareholderAccount) {
                $master = ShareholderMaster::create([
                    'boid' => 'AUTO-'.$user->id,
                    'shareholder_name' => $user->name,
                    'mobile' => $user->mobile,
                    'email' => $user->email,
                    'status' => 'active',
                    'source_note' => 'Auto-created from user role assignment',
                ]);

                ShareholderAccount::create([
                    'user_id' => $user->id,
                    'shareholder_master_id' => $master->id,
                    'boid' => $master->boid,
                    'shares_owned' => null,
                    'verification_status' => 'pending',
                    'verified_at' => null,
                ]);
            }

            $pharmacyRole = Role::whereRaw('LOWER(name) = ?', ['pharmacy'])->first();
            if ($pharmacyRole && in_array($pharmacyRole->id, $request->roles) && ! $user->pharmacy) {
                Pharmacy::create([
                    'user_id' => $user->id,
                    'store_name' => $user->name,
                    'status' => 'pending',
                ]);
            }

            $doctorRole = Role::whereRaw('LOWER(name) = ?', ['doctor'])->first();
            if ($doctorRole && in_array($doctorRole->id, $request->roles) && ! $user->doctor) {
                Doctor::create([
                    'user_id' => $user->id,
                    'status' => 'pending',
                ]);
            }
        } else {
            $user->roles()->detach();
        }

        return to_route('admin.users.index')->with('success', __('User updated successfully.'));
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return to_route('admin.users.index')->with('success', __('User deleted successfully.'));
    }
}
