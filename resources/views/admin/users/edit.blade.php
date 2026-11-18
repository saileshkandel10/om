@extends('layouts.admin')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                    <h4 class="page-title">Edit User</h4>
                    <div class="">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">User Management</a></li>
                            <li class="breadcrumb-item active">Edit User</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Please fix the following errors:</strong>
                <ul class="mb-0 mt-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <!-- Left Column — User Info Card -->
            <div class="col-xl-4 col-lg-5">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="position-relative d-inline-block mt-3">
                            @if ($user->avatar)
                                <img src="{{ $user->avatar }}" alt="{{ $user->name }}"
                                    class="rounded-circle img-fluid" width="120" height="120"
                                    style="object-fit: cover;">
                            @else
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto"
                                    style="width:120px;height:120px;font-size:42px;font-weight:600;">
                                    {{ $user->initials() }}
                                </div>
                            @endif
                            <span class="badge bg-{{ $user->status === 'active' ? 'success' : ($user->status === 'suspended' ? 'danger' : 'warning') }} position-absolute top-100 start-50 translate-middle mt-1">
                                {{ ucfirst($user->status ?? 'pending') }}
                            </span>
                        </div>
                        <h5 class="mt-4 mb-1 fw-semibold">{{ $user->name }}</h5>
                        <p class="text-muted mb-2">{{ $user->email }}</p>

                        @if ($user->mobile)
                            <p class="text-muted mb-3"><i class="iconoir-phone me-1"></i>{{ $user->mobile }}</p>
                        @endif

                        <div class="mb-3">
                            @forelse ($user->roles as $role)
                                <span class="badge bg-{{ match($role->name) { 'Admin' => 'danger', 'Staff' => 'primary', 'Shareholder' => 'secondary', 'Pharmacy' => 'success', 'Doctor' => 'info', 'Customer' => 'warning', default => 'secondary' } }} me-1">
                                    {{ $role->name }}
                                </span>
                            @empty
                                <span class="badge bg-light text-muted">No roles assigned</span>
                            @endforelse
                        </div>

                        <hr>

                        <div class="text-start">
                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">Joined</span>
                                <span class="fw-medium">{{ $user->created_at->format('M d, Y') }}</span>
                            </div>
                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">Email Verified</span>
                                <span class="fw-medium">
                                    @if ($user->email_verified_at)
                                        <span class="text-success">{{ $user->email_verified_at->format('M d, Y') }}</span>
                                    @else
                                        <span class="text-warning">Not verified</span>
                                    @endif
                                </span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Last Updated</span>
                                <span class="fw-medium">{{ $user->updated_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column — Edit Form -->
            <div class="col-xl-8 col-lg-7">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h4 class="card-title">Account Information</h4>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="mt-3">
                            @csrf
                            @method('PUT')

                            <!-- Name -->
                            <div class="mb-3 row">
                                <label for="name" class="col-sm-3 col-form-label text-end">Full Name <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <input type="text"
                                        class="form-control @error('name') is-invalid @enderror"
                                        id="name" name="name"
                                        value="{{ old('name', $user->name) }}"
                                        placeholder="Enter full name" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            

                            <!-- Mobile -->
                            <div class="mb-3 row">
                                <label for="mobile" class="col-sm-3 col-form-label text-end">Mobile</label>
                                <div class="col-sm-9">
                                    <input type="tel"
                                        class="form-control @error('mobile') is-invalid @enderror"
                                        id="mobile" name="mobile"
                                        value="{{ old('mobile', $user->mobile) }}"
                                        placeholder="Enter mobile number">
                                    @error('mobile')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="mb-3 row">
                                <label for="status" class="col-sm-3 col-form-label text-end">Status</label>
                                <div class="col-sm-9">
                                    <select class="form-select @error('status') is-invalid @enderror"
                                        id="status" name="status">
                                        <option value="active" @selected(old('status', $user->status) === 'active')>Active</option>
                                        <option value="inactive" @selected(old('status', $user->status) === 'inactive')>Inactive</option>
                                        <option value="suspended" @selected(old('status', $user->status) === 'suspended')>Suspended</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Roles -->
                            <div class="mb-3 row">
                                <label class="col-sm-3 col-form-label text-end pt-0">Roles</label>
                                <div class="col-sm-9">
                                    <div class="row">
                                        @foreach ($roles as $role)
                                            <div class="col-md-4 col-6 mb-2">
                                                <div class="form-check">
                                                    <input type="checkbox"
                                                        class="form-check-input @error('roles') is-invalid @enderror"
                                                        id="role_{{ $role->id }}"
                                                        name="roles[]"
                                                        value="{{ $role->id }}"
                                                        @checked(in_array($role->id, old('roles', $user->roles->pluck('id')->toArray())))>
                                                    <label class="form-check-label" for="role_{{ $role->id }}">
                                                        <span class="badge bg-{{ match($role->name) { 'Admin' => 'danger', 'Staff' => 'primary', 'Shareholder' => 'secondary', 'Pharmacy' => 'success', 'Doctor' => 'info', 'Customer' => 'warning', default => 'secondary' } }}">{{ $role->name }}</span>
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    @error('roles')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Assign one or more roles to this user.</small>
                                </div>
                            </div>

                            <!-- Registered Date (Read-only) -->
                            <div class="mb-3 row">
                                <label class="col-sm-3 col-form-label text-end">Registered</label>
                                <div class="col-sm-9">
                                    <div class="form-control-plaintext pt-1 pb-0">
                                        {{ $user->created_at->format('F d, Y \a\t h:i A') }}
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="row">
                                <div class="col-sm-9 ms-auto">
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="las la-save me-1"></i> Update User
                                    </button>
                                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary ms-2">
                                        Cancel
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Danger Zone — Delete Account -->
                <div class="card border border-danger">
                    <div class="card-header bg-danger-subtle">
                        <div class="row align-items-center">
                            <div class="col">
                                <h4 class="card-title text-danger mb-0">Danger Zone</h4>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="fw-semibold mb-1">Delete this user</h6>
                                <p class="text-muted mb-0 small">Once deleted, all of the user's data will be permanently removed.</p>
                            </div>
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                onsubmit="return confirm('Are you sure you want to delete {{ $user->name }}? This action cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger px-4">
                                    <i class="las la-trash-alt me-1"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

