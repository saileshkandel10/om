@extends('layouts.admin')

@section('title', __('System Reports & Exports'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                <h4 class="page-title">Executive Reports & Data Exports</h4>
                <div>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Analytics</a></li>
                        <li class="breadcrumb-item active">Reports</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="row">
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-primary-subtle text-primary rounded-3 p-3">
                            <i class="iconoir-dollar-circle fs-3"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1">Approved Revenue</p>
                            <h4 class="mb-0">Rs {{ number_format($stats['total_revenue'] ?? 0, 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-success-subtle text-success rounded-3 p-3">
                            <i class="iconoir-gift fs-3"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1">Loyalty Points Issued</p>
                            <h4 class="mb-0">{{ number_format($stats['total_points_awarded'] ?? 0, 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-info-subtle text-info rounded-3 p-3">
                            <i class="iconoir-receipt fs-3"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1">Total Purchase Bills</p>
                            <h4 class="mb-0">{{ $stats['total_bills'] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-purple-subtle text-purple rounded-3 p-3">
                            <i class="iconoir-user-badge-check fs-3"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1">Verified BOIDs</p>
                            <h4 class="mb-0">{{ $stats['total_shareholders'] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Modules -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Available Export Datasets</h4>
                    <p class="text-muted mb-0">Generate and stream structured CSV datasets for audits, forecasting, and reporting.</p>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <!-- Sales Report -->
                        <div class="col-md-6 col-lg-4">
                            <div class="border rounded-3 p-3 h-100 d-flex flex-column">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="bg-primary text-white rounded p-2 me-3">
                                        <i class="iconoir-wallet fs-4"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-0">Sales & Bills Report</h5>
                                        <small class="text-muted">Purchase bills, invoices & amounts</small>
                                    </div>
                                </div>
                                <p class="text-muted small flex-grow-1">Contains all purchase bills uploaded by shareholders, pharmacies, and patients with validation status and awarded reward values.</p>
                                <a href="{{ route('admin.analytics.export', ['type' => 'sales']) }}" class="btn btn-outline-primary btn-sm mt-auto">
                                    <i class="iconoir-download me-1"></i> Export Sales CSV
                                </a>
                            </div>
                        </div>

                        <!-- Rewards Report -->
                        <div class="col-md-6 col-lg-4">
                            <div class="border rounded-3 p-3 h-100 d-flex flex-column">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="bg-success text-white rounded p-2 me-3">
                                        <i class="iconoir-gift fs-4"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-0">Reward Transactions</h5>
                                        <small class="text-muted">Loyalty points, bonus & royalties</small>
                                    </div>
                                </div>
                                <p class="text-muted small flex-grow-1">Audit log of all credits, redemptions, bonus disbursements, and shareholder royalties calculated by the rules engine.</p>
                                <a href="{{ route('admin.analytics.export', ['type' => 'rewards']) }}" class="btn btn-outline-success btn-sm mt-auto">
                                    <i class="iconoir-download me-1"></i> Export Rewards CSV
                                </a>
                            </div>
                        </div>

                        <!-- Shareholders Report -->
                        <div class="col-md-6 col-lg-4">
                            <div class="border rounded-3 p-3 h-100 d-flex flex-column">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="bg-info text-white rounded p-2 me-3">
                                        <i class="iconoir-group fs-4"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-0">Shareholder Registry</h5>
                                        <small class="text-muted">BOIDs & shareholding data</small>
                                    </div>
                                </div>
                                <p class="text-muted small flex-grow-1">Complete list of shareholder accounts, BOID verification statuses, share units owned, and verified master links.</p>
                                <a href="{{ route('admin.analytics.export', ['type' => 'shareholders']) }}" class="btn btn-outline-info btn-sm mt-auto">
                                    <i class="iconoir-download me-1"></i> Export Shareholders CSV
                                </a>
                            </div>
                        </div>

                        <!-- Prescriptions Report -->
                        <div class="col-md-6 col-lg-4">
                            <div class="border rounded-3 p-3 h-100 d-flex flex-column">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="bg-warning text-white rounded p-2 me-3">
                                        <i class="iconoir-page-edit fs-4"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-0">Doctor Prescriptions</h5>
                                        <small class="text-muted">Clinical records & OCR parses</small>
                                    </div>
                                </div>
                                <p class="text-muted small flex-grow-1">Medical prescription entries logged by verified doctors, OCR scan status, prescribed items, and patient reference codes.</p>
                                <a href="{{ route('admin.analytics.export', ['type' => 'prescriptions']) }}" class="btn btn-outline-warning btn-sm mt-auto">
                                    <i class="iconoir-download me-1"></i> Export Prescriptions CSV
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
