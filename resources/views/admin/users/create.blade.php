@extends('layouts.admin')

@section('title', __('Create New User'))

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                    <h4 class="page-title">Create New User</h4>
                    <div>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
                            <li class="breadcrumb-item active">Create</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">User Information</h4>
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('admin.users.store') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="name" class="form-label fw-semibold">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required placeholder="Enter full name">
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required placeholder="name@example.com">
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-semibold">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required placeholder="Minimum 6 characters">
                            </div>

                            <div class="mb-3">
                                <label for="mobile" class="form-label fw-semibold">Mobile Number</label>
                                <input type="text" class="form-control" id="mobile" name="mobile" value="{{ old('mobile') }}" placeholder="9841234567">
                            </div>

                            <div class="mb-3">
                                <label for="status" class="form-label fw-semibold">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="active" @selected(old('status') === 'active')>Active</option>
                                    <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                                    <option value="suspended" @selected(old('status') === 'suspended')>Suspended</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold d-block">Assign Roles</label>
                                <div class="row g-2">
                                    @foreach ($roles as $role)
                                        <div class="col-md-4">
                                            <div class="form-check border p-2 rounded">
                                                <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->id }}" id="role_{{ $role->id }}" @checked(in_array($role->id, old('roles', [])))}>
                                                <label class="form-check-label fw-medium text-capitalize" for="role_{{ $role->id }}">
                                                    {{ $role->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary" style="background-color: #CD0B6F; border-color: #CD0B6F;">Create User</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
