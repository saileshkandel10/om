@extends('layouts.admin')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                    <h4 class="page-title">Reward Wallets</h4>
                    <div class="">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">OmConnect</a></li>
                            <li class="breadcrumb-item"><a href="#">Rewards & Loyalty</a></li>
                            <li class="breadcrumb-item active">Wallets</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h4 class="card-title">All Reward Wallets</h4>
                            </div>
                            <div class="col-auto">
                                <span class="text-muted small">{{ $wallets->count() }} total</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <form method="GET" action="{{ route('admin.reward-wallets.index') }}" class="row gy-2 gx-3 mb-3 align-items-end">
                            <div class="col-auto">
                                <input type="search" name="search" class="form-control form-control-sm" placeholder="Search user name or email..." value="{{ request('search') }}">
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
                                <a href="{{ route('admin.reward-wallets.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                            </div>
                        </form>
                        <div class="table-responsive">
                            <table class="table datatable" id="datatable_1">
                                <thead class="table-light">
                                    <tr>
                                        <th>User</th>
                                        <th>Total Points</th>
                                        <th>Total Bonus</th>
                                        <th>Total Royalty</th>
                                        <th>Last Updated</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($wallets as $wallet)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width:32px;height:32px;font-size:13px">
                                                        {{ $wallet->user?->name ? substr($wallet->user->name, 0, 2) : '??' }}
                                                    </div>
                                                    <div>
                                                        <span class="fw-medium">{{ $wallet->user?->name ?? '—' }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><span class="fw-bold fs-5">{{ number_format($wallet->total_points, 2) }}</span></td>
                                            <td>{{ number_format($wallet->total_bonus, 2) }}</td>
                                            <td>{{ number_format($wallet->total_royalty, 2) }}</td>
                                            <td data-sort="{{ $wallet->updated_at->format('Y/m/d') }}">{{ $wallet->updated_at->format('M d, Y') }}</td>
                                            <td class="text-end">
                                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.reward-wallets.show', $wallet) }}">View</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">No wallets found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-2">
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

