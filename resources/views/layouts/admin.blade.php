<!DOCTYPE html>
<html lang="en" dir="ltr" data-startbar="light" data-bs-theme="light">

<head>


    <meta charset="utf-8" />
    <title>Dashboard | OM Connect</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta content="Premium Multipurpose Teacher & Dashboard Template" name="description" />
    <meta content="" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <!-- App favicon -->
    <link rel="shortcut icon"
        href="https://ommegashreepharmaceuticals.com/wp-content/uploads/2020/12/cropped-imageedit_1_6169141058-180x180.jpg">


    <link href="{{ asset('assets/libs/simple-datatables/style.css') }}" rel="stylesheet" type="text/css" />

    <!-- Sweet Alert -->
    <link href="{{ asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/libs/animate.css/animate.min.css') }}" rel="stylesheet" type="text/css">
    <!-- App css -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />

    <style>
        :root {
            --om-brand-pink: #CD0B6F;
            --om-brand-dark-pink: #A30958;
            --om-brand-soft-pink: #FDE8F3;
            --om-brand-light-pink: #FFF5F9;
            --om-brand-navy: #1E3A5F;
            --om-brand-border: #F8B4D9;
            --teacher-primary: #CD0B6F;
            --teacher-dark: #CD0B6F;
            --teacher-soft: #fde8f2;
            --teacher-border: #f8b4d8;
        }
        .receptionist-theme {
            --teacher-primary: #CD0B6F;
            --teacher-dark: #CD0B6F;
            --teacher-soft: #fde8f2;
            --teacher-border: #f8b4d8;
        }
        .receptionist-theme .topbar {
            background: var(--teacher-primary);
        }

        /* Enhanced Brand Nav-Pills & Nav-Tabs across Admin */
        .nav-pills .nav-link {
            color: var(--om-brand-navy);
            font-weight: 600;
            border-radius: 10px;
            padding: 8px 16px;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }
        .nav-pills .nav-link:hover:not(.active) {
            color: var(--om-brand-pink);
            background-color: var(--om-brand-soft-pink);
            transform: translateY(-1px);
        }
        .nav-pills .nav-link.active,
        .nav-pills .show > .nav-link {
            background-color: var(--om-brand-pink) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(205, 11, 111, 0.25);
            transform: translateY(-1px);
        }

        /* Nav Tabs & Custom Header Tabs */
        .nav-tabs {
            border-bottom: 2px solid #e2e8f0;
        }
        .nav-tabs .nav-link {
            color: #64748b;
            font-weight: 600;
            border: none;
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            padding: 10px 18px;
            transition: all 0.2s ease;
        }
        .nav-tabs .nav-link:hover:not(.active) {
            color: var(--om-brand-pink);
            border-color: var(--om-brand-border);
            background-color: var(--om-brand-light-pink);
        }
        .nav-tabs .nav-link.active {
            color: var(--om-brand-pink) !important;
            border-bottom: 3px solid var(--om-brand-pink) !important;
            background-color: transparent !important;
            font-weight: 700;
        }

        /* Brand Button Accents */
        .btn-brand, .btn-primary {
            background-color: var(--om-brand-pink) !important;
            border-color: var(--om-brand-pink) !important;
            color: #ffffff !important;
            box-shadow: 0 2px 4px rgba(205, 11, 111, 0.15);
            transition: all 0.2s ease;
        }
        .btn-brand:hover, .btn-brand:focus, .btn-brand:active,
        .btn-primary:hover, .btn-primary:focus, .btn-primary:active {
            background-color: var(--om-brand-dark-pink) !important;
            border-color: var(--om-brand-dark-pink) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(205, 11, 111, 0.3) !important;
            transform: translateY(-1px);
        }
        .btn-outline-brand, .btn-outline-primary {
            color: var(--om-brand-pink) !important;
            border-color: var(--om-brand-pink) !important;
            transition: all 0.2s ease;
        }
        .btn-outline-brand:hover, .btn-outline-brand:focus, .btn-outline-brand:active,
        .btn-outline-primary:hover, .btn-outline-primary:focus, .btn-outline-primary:active {
            background-color: var(--om-brand-pink) !important;
            border-color: var(--om-brand-pink) !important;
            color: #ffffff !important;
            transform: translateY(-1px);
        }

        /* Form Checks & Brand Toggles */
        .form-check-input:checked {
            background-color: var(--om-brand-pink) !important;
            border-color: var(--om-brand-pink) !important;
        }
        .form-check-input:focus {
            border-color: var(--om-brand-border);
            box-shadow: 0 0 0 0.25rem rgba(205, 11, 111, 0.2);
        }

        /* Brand Links & Badges */
        a.text-brand { color: var(--om-brand-pink) !important; }
        a.text-brand:hover { color: var(--om-brand-dark-pink) !important; }
        .badge.bg-brand, .badge.bg-primary {
            background-color: var(--om-brand-pink) !important;
            color: #ffffff !important;
        }
        .badge-soft-pink, .badge.bg-primary-subtle {
            background-color: var(--om-brand-soft-pink) !important;
            color: var(--om-brand-pink) !important;
            border: 1px solid var(--om-brand-border);
        }

        /* Collapsible Sidebar Submenus & Dropdown styling */
        .startbar .nav-item .nav-link.has-arrow,
        .startbar .nav-item .nav-link[data-bs-toggle="collapse"] {
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            cursor: pointer !important;
            width: 100%;
            user-select: none;
        }
        .startbar .nav-item .nav-link.has-arrow[data-bs-toggle="collapse"]::after {
            display: none !important;
        }
        .startbar .nav-item .nav-link .menu-arrow {
            font-size: 13px;
            color: #94a3b8;
            transition: transform 0.25s ease;
            margin-left: auto;
            pointer-events: none !important;
        }
        .startbar .nav-item .nav-link[aria-expanded="true"] .menu-arrow,
        .startbar .nav-item .nav-link:not(.collapsed) .menu-arrow {
            transform: rotate(180deg);
            color: var(--om-brand-pink, #CD0B6F);
        }
        .startbar .nav-item .nav-link[aria-expanded="true"],
        .startbar .nav-item .nav-link:not(.collapsed) {
            color: var(--om-brand-pink, #CD0B6F);
            font-weight: 600;
        }
        .startbar-sub-nav {
            padding-left: 0.8rem;
            list-style: none;
            margin: 4px 0 8px 1.4rem;
            border-left: 2px solid #e2e8f0;
        }
        .startbar-sub-nav .nav-item {
            margin: 2px 0;
        }
        .startbar-sub-nav .nav-link {
            font-size: 13px;
            padding: 6px 12px;
            color: #64748b;
            border-radius: 6px;
            display: flex;
            align-items: center;
            transition: all 0.15s ease;
            position: relative;
            z-index: 2;
        }
        .startbar-sub-nav .nav-link:hover {
            color: var(--om-brand-pink, #CD0B6F);
            background-color: var(--om-brand-soft-pink, #fdf2f8);
            padding-left: 15px;
        }
        .startbar-sub-nav .nav-link.active {
            color: var(--om-brand-pink, #CD0B6F);
            background-color: var(--om-brand-soft-pink, #fce7f3);
            font-weight: 600;
        }
        .startbar-sub-nav .nav-link.active::before {
            content: "•";
            color: var(--om-brand-pink, #CD0B6F);
            font-size: 16px;
            margin-right: 6px;
            line-height: 1;
        }
    </style>

    @stack('styles')
</head>

<body class="receptionist-theme">

    <!-- Top Bar Start -->
    <div class="topbar d-print-none shadow-sm" style="border-bottom: 2px solid var(--om-brand-soft-pink); background: #ffffff;">
        <div class="container-fluid">
            <nav class="topbar-custom d-flex justify-content-between align-items-center" id="topbar-custom">

                <ul class="topbar-item list-unstyled d-inline-flex align-items-center mb-0">
                    <li>
                        <button class="nav-link mobile-menu-btn nav-icon" id="togglemenu" style="color: var(--om-brand-pink);">
                            <i class="iconoir-menu fs-20"></i>
                        </button>
                    </li>
                    <li class="mx-2 welcome-text">
                        <div class="d-flex align-items-center">
                            <h5 class="mb-0 fw-bold text-dark fs-14 text-truncate">
                                Welcome, <span class="text-brand">{{ auth()->user()->name }}</span>
                            </h5>
                            @if(auth()->user()->roles->isNotEmpty())
                                <span class="badge bg-brand-soft text-brand border border-brand-subtle ms-2 fs-11 px-2 py-1">
                                    {{ auth()->user()->roles->first()->display_name ?: ucfirst(auth()->user()->roles->first()->name) }}
                                </span>
                            @endif
                        </div>
                    </li>
                </ul>

                <ul class="topbar-item list-unstyled d-inline-flex align-items-center mb-0 gap-2">
                    {{-- Quick Action Shortcut --}}
                    <li class="d-none d-lg-inline-block">
                        <a href="{{ route('admin.purchase-bills.index') }}" class="btn btn-sm btn-outline-brand rounded-pill px-3 py-1 fs-12 fw-semibold">
                            <i class="las la-receipt me-1"></i> Bills Queue
                        </a>
                    </li>

                    <li class="topbar-item">
                        <a class="nav-link nav-icon" href="javascript:void(0);" id="light-dark-mode" title="Toggle Theme">
                            <i class="iconoir-half-moon dark-mode"></i>
                            <i class="iconoir-sun-light light-mode text-warning"></i>
                        </a>
                    </li>

                    {{-- User Profile Dropdown --}}
                    <li class="dropdown topbar-item">
                        <a class="nav-link dropdown-toggle arrow-none nav-icon p-1" data-bs-toggle="dropdown"
                            href="#" role="button" aria-haspopup="false" aria-expanded="false"
                            data-bs-offset="0,12">
                            <div class="thumb-md rounded-circle text-white d-flex align-items-center justify-content-center fw-bold shadow-sm"
                                 style="background: linear-gradient(135deg, var(--om-brand-pink) 0%, var(--om-brand-dark-pink) 100%); width: 36px; height: 36px; font-size: 13px;">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1) . substr(strrchr(auth()->user()->name, ' ') ?: ' ', 1, 1)) }}
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 py-0" style="min-width: 240px; overflow: hidden;">
                            <div class="p-3 bg-brand-soft border-bottom border-brand-subtle">
                                <div class="d-flex align-items-center">
                                    <div class="thumb-md rounded-circle text-white d-flex align-items-center justify-content-center fw-bold shadow-sm me-2"
                                         style="background: var(--om-brand-pink); width: 38px; height: 38px;">
                                        {{ strtoupper(substr(auth()->user()->name, 0, 1) . substr(strrchr(auth()->user()->name, ' ') ?: ' ', 1, 1)) }}
                                    </div>
                                    <div class="flex-grow-1 text-truncate">
                                        <h6 class="my-0 fw-bold text-dark fs-13 text-truncate">{{ auth()->user()->name }}</h6>
                                        <small class="text-muted d-block text-truncate fs-11">{{ auth()->user()->email }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="p-2">
                                <a class="dropdown-item rounded-2 py-2 fs-13" href="{{ route('admin.profile') }}">
                                    <i class="las la-user-circle text-brand fs-16 me-2 align-middle"></i> My Profile
                                </a>
                                <a class="dropdown-item rounded-2 py-2 fs-13" href="{{ route('admin.roles.index') }}">
                                    <i class="las la-user-shield text-brand fs-16 me-2 align-middle"></i> Roles &amp; Permissions
                                </a>
                                <a class="dropdown-item rounded-2 py-2 fs-13" href="{{ route('admin.settings.system') }}">
                                    <i class="las la-sliders-h text-brand fs-16 me-2 align-middle"></i> Verification Rules
                                </a>
                                <a class="dropdown-item rounded-2 py-2 fs-13" href="{{ route('admin.audit-logs.index') }}">
                                    <i class="las la-history text-brand fs-16 me-2 align-middle"></i> Audit Trail Logs
                                </a>
                                <div class="dropdown-divider my-1"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item rounded-2 py-2 fs-13 text-danger fw-semibold">
                                        <i class="las la-power-off fs-16 me-2 align-middle"></i> Sign out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </li>
                </ul><!--end topbar-nav-->
            </nav>
            <!-- end navbar-->
        </div>
    </div>
    <!-- Top Bar End -->
    <!-- leftbar-tab-menu -->
    <div class="startbar d-print-none">
        <!--start brand-->
        <div class="brand">
            <a href="/" class="logo">
                <span>
                    <img src="https://ommegashreepharmaceuticals.com/wp-content/uploads/2020/12/cropped-imageedit_1_6169141058-180x180.jpg"
                        alt="logo-small" class="logo-sm">
                </span>
                <span>
                    <img src="{{ asset('images/oms-web-logo.png') }}" alt="logo-large"
                        class="logo-lg logo-light">
                    <img src="{{ asset('images/oms-web-logo.png') }}" alt="logo-large"
                        class="logo-lg logo-dark">
                </span>
            </a>
        </div>
        <!--end brand-->
        <!--start startbar-menu-->
        <div class="startbar-menu">
            <div class="startbar-collapse" id="startbarCollapse" data-simplebar>
                <div class="d-flex align-items-start flex-column w-100">
                    <!-- Navigation -->
                    <ul class="navbar-nav mb-auto w-100">
                        <li class="menu-label mt-2">
                            <span>Main</span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                                <i class="iconoir-dashboard menu-icon"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.profile') ? 'active' : '' }}" href="{{ route('admin.profile') }}">
                                <i class="iconoir-user menu-icon"></i>
                                <span>My Profile</span>
                            </a>
                        </li>

                        <li class="menu-label mt-2">
                            <span>Modules &amp; Management</span>
                        </li>

                        {{-- User Management Dropdown --}}
                        @php($isUserActive = request()->routeIs('admin.users.*', 'admin.shareholders.*', 'admin.pharmacies.*', 'admin.doctors.*', 'admin.roles.*'))
                        <li class="nav-item">
                            <a class="nav-link has-arrow {{ $isUserActive ? 'active' : '' }}" href="#sidebarUsers" data-bs-toggle="collapse" data-bs-target="#sidebarUsers" role="button" aria-expanded="{{ $isUserActive ? 'true' : 'false' }}" aria-controls="sidebarUsers">
                                <span class="d-flex align-items-center">
                                    <i class="iconoir-group menu-icon"></i>
                                    <span>User Management</span>
                                </span>
                                <i class="las la-angle-down menu-arrow"></i>
                            </a>
                            <div class="collapse {{ $isUserActive ? 'show' : '' }}" id="sidebarUsers">
                                <ul class="startbar-sub-nav">
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">Users Directory</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.shareholders.*') ? 'active' : '' }}" href="{{ route('admin.shareholders.index') }}">Shareholders</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.pharmacies.*') ? 'active' : '' }}" href="{{ route('admin.pharmacies.index') }}">Pharmacies</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.doctors.*') ? 'active' : '' }}" href="{{ route('admin.doctors.index') }}">Doctors</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}" href="{{ route('admin.roles.index') }}">Roles &amp; Permissions</a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                        {{-- Product Management Dropdown --}}
                        @php($isProductActive = request()->routeIs('admin.products.*', 'admin.other-products.*', 'admin.product-categories.*', 'admin.illness-categories.*', 'admin.product-batches.*'))
                        <li class="nav-item">
                            <a class="nav-link has-arrow {{ $isProductActive ? 'active' : '' }}" href="#sidebarProducts" data-bs-toggle="collapse" data-bs-target="#sidebarProducts" role="button" aria-expanded="{{ $isProductActive ? 'true' : 'false' }}" aria-controls="sidebarProducts">
                                <span class="d-flex align-items-center">
                                    <i class="iconoir-package menu-icon"></i>
                                    <span>Product Management</span>
                                </span>
                                <i class="las la-angle-down menu-arrow"></i>
                            </a>
                            <div class="collapse {{ $isProductActive ? 'show' : '' }}" id="sidebarProducts">
                                <ul class="startbar-sub-nav">
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}" href="{{ route('admin.products.index') }}">Products Catalog</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.other-products.*') ? 'active' : '' }}" href="{{ route('admin.other-products.index') }}">Other Products</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.product-categories.*') ? 'active' : '' }}" href="{{ route('admin.product-categories.index') }}">Product Categories</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.illness-categories.*') ? 'active' : '' }}" href="{{ route('admin.illness-categories.index') }}">Illness Categories</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.product-batches.*') ? 'active' : '' }}" href="{{ route('admin.product-batches.index') }}">Product Batches</a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                        {{-- OCR & Bills Dropdown --}}
                        @php($isBillsActive = request()->routeIs('admin.purchase-bills.*', 'admin.fraud-detection', 'admin.ocr-jobs.*', 'admin.ocr-review-queue.*'))
                        <li class="nav-item">
                            <a class="nav-link has-arrow {{ $isBillsActive ? 'active' : '' }}" href="#sidebarBills" data-bs-toggle="collapse" data-bs-target="#sidebarBills" role="button" aria-expanded="{{ $isBillsActive ? 'true' : 'false' }}" aria-controls="sidebarBills">
                                <span class="d-flex align-items-center">
                                    <i class="iconoir-paste-clipboard menu-icon"></i>
                                    <span>OCR &amp; Bills</span>
                                </span>
                                <i class="las la-angle-down menu-arrow"></i>
                            </a>
                            <div class="collapse {{ $isBillsActive ? 'show' : '' }}" id="sidebarBills">
                                <ul class="startbar-sub-nav">
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.purchase-bills.*') ? 'active' : '' }}" href="{{ route('admin.purchase-bills.index') }}">Purchase Bills</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.fraud-detection') ? 'active' : '' }}" href="{{ route('admin.fraud-detection') }}">Fraud Detection</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.ocr-jobs.*') ? 'active' : '' }}" href="{{ route('admin.ocr-jobs.index') }}">OCR Processing Jobs</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.ocr-review-queue.*') ? 'active' : '' }}" href="{{ route('admin.ocr-review-queue.index') }}">OCR Review Queue</a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                        {{-- Rewards & Loyalty Dropdown --}}
                        @php($isRewardsActive = request()->routeIs('admin.reward-rules.*', 'admin.reward-transactions.*', 'admin.reward-wallets.*', 'admin.redemptions.*'))
                        <li class="nav-item">
                            <a class="nav-link has-arrow {{ $isRewardsActive ? 'active' : '' }}" href="#sidebarRewards" data-bs-toggle="collapse" data-bs-target="#sidebarRewards" role="button" aria-expanded="{{ $isRewardsActive ? 'true' : 'false' }}" aria-controls="sidebarRewards">
                                <span class="d-flex align-items-center">
                                    <i class="iconoir-coins menu-icon"></i>
                                    <span>Rewards &amp; Loyalty</span>
                                </span>
                                <i class="las la-angle-down menu-arrow"></i>
                            </a>
                            <div class="collapse {{ $isRewardsActive ? 'show' : '' }}" id="sidebarRewards">
                                <ul class="startbar-sub-nav">
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.reward-rules.*') ? 'active' : '' }}" href="{{ route('admin.reward-rules.index') }}">Reward Rules</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.reward-transactions.*') ? 'active' : '' }}" href="{{ route('admin.reward-transactions.index') }}">Reward Transactions</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.reward-wallets.*') ? 'active' : '' }}" href="{{ route('admin.reward-wallets.index') }}">Reward Wallets</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.redemptions.*') ? 'active' : '' }}" href="{{ route('admin.redemptions.index') }}">Redemptions</a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                        {{-- Campaigns, Advertisements & Notices Dropdown --}}
                        @php($isCampaignActive = request()->routeIs('admin.campaigns.*', 'admin.advertisements.*', 'admin.notices.*', 'admin.notifications.*'))
                        <li class="nav-item">
                            <a class="nav-link has-arrow {{ $isCampaignActive ? 'active' : '' }}" href="#sidebarCampaigns" data-bs-toggle="collapse" data-bs-target="#sidebarCampaigns" role="button" aria-expanded="{{ $isCampaignActive ? 'true' : 'false' }}" aria-controls="sidebarCampaigns">
                                <span class="d-flex align-items-center">
                                    <i class="iconoir-megaphone menu-icon"></i>
                                    <span>Campaigns &amp; Ads</span>
                                </span>
                                <i class="las la-angle-down menu-arrow"></i>
                            </a>
                            <div class="collapse {{ $isCampaignActive ? 'show' : '' }}" id="sidebarCampaigns">
                                <ul class="startbar-sub-nav">
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.advertisements.*') ? 'active' : '' }}" href="{{ route('admin.advertisements.index') }}">
                                            <i class="las la-ad me-1 text-brand"></i> Advertisements / Banners
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.campaigns.*') ? 'active' : '' }}" href="{{ route('admin.campaigns.index') }}">Campaigns</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.notices.*') ? 'active' : '' }}" href="{{ route('admin.notices.index') }}">Notices</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}" href="{{ route('admin.notifications.index') }}">Notifications</a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                        {{-- Shareholder Zone Dropdown --}}
                        @php($isShareholderZone = request()->routeIs('admin.agm-notices', 'admin.financial-reports'))
                        <li class="nav-item">
                            <a class="nav-link has-arrow {{ $isShareholderZone ? 'active' : '' }}" href="#sidebarShareholderZone" data-bs-toggle="collapse" data-bs-target="#sidebarShareholderZone" role="button" aria-expanded="{{ $isShareholderZone ? 'true' : 'false' }}" aria-controls="sidebarShareholderZone">
                                <span class="d-flex align-items-center">
                                    <i class="iconoir-star menu-icon"></i>
                                    <span>Shareholder Zone</span>
                                </span>
                                <i class="las la-angle-down menu-arrow"></i>
                            </a>
                            <div class="collapse {{ $isShareholderZone ? 'show' : '' }}" id="sidebarShareholderZone">
                                <ul class="startbar-sub-nav">
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.agm-notices') ? 'active' : '' }}" href="{{ route('admin.agm-notices') }}">AGM Notices</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.financial-reports') ? 'active' : '' }}" href="{{ route('admin.financial-reports') }}">Financial Reports</a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                        {{-- Pharmacy & Prescriptions Dropdown --}}
                        @php($isRxActive = request()->routeIs('admin.pharmacist-quiz.*', 'admin.sourcing-records.*', 'admin.prescriptions.*', 'admin.prescription-items.*'))
                        <li class="nav-item">
                            <a class="nav-link has-arrow {{ $isRxActive ? 'active' : '' }}" href="#sidebarPharmacyRx" data-bs-toggle="collapse" data-bs-target="#sidebarPharmacyRx" role="button" aria-expanded="{{ $isRxActive ? 'true' : 'false' }}" aria-controls="sidebarPharmacyRx">
                                <span class="d-flex align-items-center">
                                    <i class="iconoir-journal menu-icon"></i>
                                    <span>Pharmacy &amp; Prescriptions</span>
                                </span>
                                <i class="las la-angle-down menu-arrow"></i>
                            </a>
                            <div class="collapse {{ $isRxActive ? 'show' : '' }}" id="sidebarPharmacyRx">
                                <ul class="startbar-sub-nav">
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.pharmacist-quiz.index') ? 'active' : '' }}" href="{{ route('admin.pharmacist-quiz.index') }}">
                                            <i class="las la-graduation-cap me-1 text-brand"></i> Pharmacist Quizzes
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.pharmacist-quiz.categories.*') ? 'active' : '' }}" href="{{ route('admin.pharmacist-quiz.categories.index') }}">
                                            <i class="las la-folder me-1 text-brand"></i> Quiz Categories
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.pharmacist-quiz.questions.*') ? 'active' : '' }}" href="{{ route('admin.pharmacist-quiz.questions.index') }}">
                                            <i class="las la-database me-1 text-brand"></i> Question Bank
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.pharmacist-quiz.results.*') ? 'active' : '' }}" href="{{ route('admin.pharmacist-quiz.results.index') }}">
                                            <i class="las la-chart-bar me-1 text-brand"></i> Results &amp; Analytics
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.pharmacist-quiz.settings.*') ? 'active' : '' }}" href="{{ route('admin.pharmacist-quiz.settings.index') }}">
                                            <i class="las la-cog me-1 text-brand"></i> Quiz Settings
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.sourcing-records.*') ? 'active' : '' }}" href="{{ route('admin.sourcing-records.index') }}">Sourcing Records</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.prescriptions.*') ? 'active' : '' }}" href="{{ route('admin.prescriptions.index') }}">Prescriptions</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.prescription-items.*') ? 'active' : '' }}" href="{{ route('admin.prescription-items.index') }}">Prescription Items</a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                        <li class="menu-label mt-2">
                            <span>Analytics &amp; Locations</span>
                        </li>

                        {{-- Analytics & Reports Dropdown --}}
                        @php($isAnalyticsActive = request()->routeIs('admin.reports.product-demand', 'admin.reports.illness-categories', 'admin.analytics.*'))
                        <li class="nav-item">
                            <a class="nav-link has-arrow {{ $isAnalyticsActive ? 'active' : '' }}" href="#sidebarAnalytics" data-bs-toggle="collapse" data-bs-target="#sidebarAnalytics" role="button" aria-expanded="{{ $isAnalyticsActive ? 'true' : 'false' }}" aria-controls="sidebarAnalytics">
                                <span class="d-flex align-items-center">
                                    <i class="iconoir-candlestick-chart menu-icon"></i>
                                    <span>Analytics &amp; Reports</span>
                                </span>
                                <i class="las la-angle-down menu-arrow"></i>
                            </a>
                            <div class="collapse {{ $isAnalyticsActive ? 'show' : '' }}" id="sidebarAnalytics">
                                <ul class="startbar-sub-nav">
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.reports.product-demand', 'admin.analytics.product-demand') ? 'active' : '' }}" href="{{ route('admin.reports.product-demand') }}">Product Demand &amp; Location</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.reports.illness-categories') ? 'active' : '' }}" href="{{ route('admin.reports.illness-categories') }}">Illness Category Report</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.analytics.sales') ? 'active' : '' }}" href="{{ route('admin.analytics.sales') }}">Sales Dashboard</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.analytics.user-behaviour') ? 'active' : '' }}" href="{{ route('admin.analytics.user-behaviour') }}">User Behaviour</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.analytics.reports') ? 'active' : '' }}" href="{{ route('admin.analytics.reports') }}">System Reports</a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                        {{-- Locations & Master Data Dropdown --}}
                        @php($isLocationsActive = request()->routeIs('admin.settings.locations.*'))
                        <li class="nav-item">
                            <a class="nav-link has-arrow {{ $isLocationsActive ? 'active' : '' }}" href="#sidebarLocations" data-bs-toggle="collapse" data-bs-target="#sidebarLocations" role="button" aria-expanded="{{ $isLocationsActive ? 'true' : 'false' }}" aria-controls="sidebarLocations">
                                <span class="d-flex align-items-center">
                                    <i class="iconoir-map-pin menu-icon"></i>
                                    <span>Locations &amp; Districts</span>
                                </span>
                                <i class="las la-angle-down menu-arrow"></i>
                            </a>
                            <div class="collapse {{ $isLocationsActive ? 'show' : '' }}" id="sidebarLocations">
                                <ul class="startbar-sub-nav">
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.settings.locations.districts') && request('tab') !== 'unmapped' ? 'active' : '' }}" href="{{ route('admin.settings.locations.districts') }}">Nepal 77 Districts</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.settings.locations.districts') && request('tab') === 'unmapped' ? 'active' : '' }}" href="{{ route('admin.settings.locations.districts', ['tab' => 'unmapped']) }}">Unmapped Bills Queue</a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                        {{-- System & Security Dropdown --}}
                        @php($isSystemActive = request()->routeIs('admin.roles.*', 'admin.settings.system', 'admin.uploaded-files.*', 'admin.audit-logs.*'))
                        <li class="nav-item">
                            <a class="nav-link has-arrow {{ $isSystemActive ? 'active' : '' }}" href="#sidebarSystem" data-bs-toggle="collapse" data-bs-target="#sidebarSystem" role="button" aria-expanded="{{ $isSystemActive ? 'true' : 'false' }}" aria-controls="sidebarSystem">
                                <span class="d-flex align-items-center">
                                    <i class="iconoir-settings menu-icon"></i>
                                    <span>System &amp; Settings</span>
                                </span>
                                <i class="las la-angle-down menu-arrow"></i>
                            </a>
                            <div class="collapse {{ $isSystemActive ? 'show' : '' }}" id="sidebarSystem">
                                <ul class="startbar-sub-nav">
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}" href="{{ route('admin.roles.index') }}">Roles &amp; Permissions</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.settings.system') ? 'active' : '' }}" href="{{ route('admin.settings.system') }}">Verification Rules</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.uploaded-files.*') ? 'active' : '' }}" href="{{ route('admin.uploaded-files.index') }}">Uploaded Files</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}" href="{{ route('admin.audit-logs.index') }}">Audit Trail Logs</a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    </ul><!--end navbar-nav--->
                        </li>
                    </ul><!--end navbar-nav--->

                </div>
            </div><!--end startbar-collapse-->
        </div><!--end startbar-menu-->
    </div><!--end startbar-->
    <div class="startbar-overlay d-print-none"></div>
    <!-- end leftbar-tab-menu-->
    <div class="page-wrapper">
        <!-- Page Content-->
        <div class="page-content">


            @yield('content')

            <div data-success-message="{{ session('success') }}" data-visitor-error-message="{{ session('error') }}" style="display:none"></div>

            <x-dashboard-footer />
            <!--end footer-->
        </div>
        <!-- end page content -->
    </div>
    <!-- end page-wrapper -->
    <!-- Javascript  -->
    <!-- vendor js -->

    <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>

    <!-- Sweet-Alert  -->
    <script src="{{ asset('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>

    <script src="{{ asset('assets/js/app.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Direct robust click handler for all sidebar collapse dropdowns
            document.querySelectorAll('.startbar .nav-link[data-bs-toggle="collapse"], .startbar .nav-link.has-arrow').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    var targetId = this.getAttribute('data-bs-target') || this.getAttribute('href');
                    if (!targetId || targetId === '#' || targetId === 'javascript:void(0);') return;

                    var targetCollapse = document.querySelector(targetId);
                    if (!targetCollapse) return;

                    if (typeof bootstrap !== 'undefined' && bootstrap.Collapse) {
                        var bsCollapse = bootstrap.Collapse.getOrCreateInstance(targetCollapse, {
                            toggle: false
                        });

                        if (targetCollapse.classList.contains('show')) {
                            bsCollapse.hide();
                            this.setAttribute('aria-expanded', 'false');
                            this.classList.add('collapsed');
                        } else {
                            bsCollapse.show();
                            this.setAttribute('aria-expanded', 'true');
                            this.classList.remove('collapsed');
                        }
                    } else {
                        var isShown = targetCollapse.classList.contains('show');
                        if (isShown) {
                            targetCollapse.classList.remove('show');
                            this.setAttribute('aria-expanded', 'false');
                            this.classList.add('collapsed');
                        } else {
                            targetCollapse.classList.add('show');
                            this.setAttribute('aria-expanded', 'true');
                            this.classList.remove('collapsed');
                        }
                    }
                });
            });

            // Keep aria-expanded and arrow in sync with Bootstrap events
            document.querySelectorAll('.startbar .collapse').forEach(function(col) {
                col.addEventListener('shown.bs.collapse', function() {
                    var trigger = document.querySelector('.startbar [data-bs-target="#' + this.id + '"], .startbar [href="#' + this.id + '"]');
                    if (trigger) {
                        trigger.setAttribute('aria-expanded', 'true');
                        trigger.classList.remove('collapsed');
                    }
                });
                col.addEventListener('hidden.bs.collapse', function() {
                    var trigger = document.querySelector('.startbar [data-bs-target="#' + this.id + '"], .startbar [href="#' + this.id + '"]');
                    if (trigger) {
                        trigger.setAttribute('aria-expanded', 'false');
                        trigger.classList.add('collapsed');
                    }
                });
            });
        });
    </script>
    @stack('scripts')
</body>
<!--end body-->

</html>
