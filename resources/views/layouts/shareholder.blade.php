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
        .receptionist-theme {
            --teacher-primary: #CD0B6F;
            --teacher-dark: #CD0B6F;
            --teacher-soft: #fde8f2;
            --teacher-border: #f8b4d8;
            --teacher-topbar-icon-bg: rgba(255, 255, 255, 0.14);
            --teacher-topbar-icon-border: rgba(255, 255, 255, 0.22);
            --teacher-topbar-icon-hover: rgba(255, 255, 255, 0.22);
            --teacher-dropdown-bg: #ffffff;
            --teacher-dropdown-color: #1f2937;
            --teacher-dropdown-muted: #64748b;
        }

        .receptionist-theme .topbar {
            background: var(--teacher-primary);
        }

        .receptionist-theme .topbar .nav-link,
        .receptionist-theme .topbar h5,
        .receptionist-theme .topbar i,
        .receptionist-theme .topbar .form-control::placeholder {
            color: #b5b3b3 !important;
        }

        .receptionist-theme .topbar .top-search {
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #030303;
        }

        .receptionist-theme .topbar .top-search:focus {
            background: rgba(255, 255, 255, 0.18);
            border-color: rgba(255, 255, 255, 0.35);
            box-shadow: none;
        }

        .receptionist-theme .topbar .nav-icon,
        .receptionist-theme .topbar .nav-link.nav-icon,
        .receptionist-theme .topbar .mobile-menu-btn {
            background: var(--teacher-topbar-icon-bg);
            border: 1px solid var(--teacher-topbar-icon-border);
            color: #fff !important;
        }

        .receptionist-theme .topbar .nav-icon:hover,
        .receptionist-theme .topbar .nav-link.nav-icon:hover,
        .receptionist-theme .topbar .mobile-menu-btn:hover,
        .receptionist-theme .topbar .nav-icon:focus,
        .receptionist-theme .topbar .nav-link.nav-icon:focus,
        .receptionist-theme .topbar .mobile-menu-btn:focus {
            background: var(--teacher-topbar-icon-hover);
            border-color: rgba(255, 255, 255, 0.32);
            box-shadow: none;
            color: #fff !important;
        }

        .receptionist-theme .topbar .alert-badge {
            background: #fff;
            box-shadow: 0 0 0 2px var(--teacher-primary);
        }

        .receptionist-theme .dropdown-menu {
            background: var(--teacher-dropdown-bg);
            border: 1px solid var(--teacher-border);
        }

        .receptionist-theme .dropdown-menu,
        .receptionist-theme .dropdown-menu .dropdown-item,
        .receptionist-theme .dropdown-menu .dropdown-item-text,
        .receptionist-theme .dropdown-menu .nav-link,
        .receptionist-theme .dropdown-menu h5,
        .receptionist-theme .dropdown-menu h6 {
            color: var(--teacher-dropdown-color);
        }

        .receptionist-theme .dropdown-menu i,
        .receptionist-theme .dropdown-menu .nav-link i,
        .receptionist-theme .dropdown-menu .dropdown-item i {
            color: inherit !important;
        }

        .receptionist-theme .dropdown-menu .text-muted,
        .receptionist-theme .dropdown-menu small,
        .receptionist-theme .dropdown-menu .text-body-tertiary {
            color: var(--teacher-dropdown-muted) !important;
        }

        .receptionist-theme .dropdown-menu .bg-secondary-subtle {
            background: var(--teacher-soft) !important;
        }

        .receptionist-theme .nav-tabs-custom.nav-success .nav-link {
            color: var(--teacher-dropdown-muted);
        }

        .receptionist-theme .nav-tabs-custom.nav-success .nav-link.active,
        .receptionist-theme .nav-tabs-custom.nav-success .nav-link:hover {
            color: var(--teacher-primary);
        }

        .receptionist-theme .nav-tabs-custom.nav-success .nav-link.active::after,
        .receptionist-theme .nav-tabs-custom.nav-success .nav-link:hover::after {
            background: var(--teacher-primary);
        }

        .receptionist-theme .bg-primary-subtle {
            background: var(--teacher-soft) !important;
        }

        .receptionist-theme .text-primary {
            color: var(--teacher-primary) !important;
        }

        .receptionist-theme .startbar {
            background: #ffffff;
            border-right: 1px solid var(--teacher-border);
        }

        .receptionist-theme .brand {
            background: var(--teacher-dark);
        }

        .receptionist-theme .navbar-nav .nav-link {
            color: #334155;
            border-radius: 10px;
            margin: 2px 10px;
        }

        .receptionist-theme .navbar-nav .menu-icon,
        .receptionist-theme .menu-icon,
        .receptionist-theme .nav-link .menu-icon {
            color: #64748b;
        }

        .receptionist-theme .navbar-nav .nav-link:hover,
        .receptionist-theme .navbar-nav .nav-link.active {
            background: var(--teacher-soft);
            color: var(--teacher-primary);
        }

        .receptionist-theme .navbar-nav .nav-link:hover .menu-icon,
        .receptionist-theme .navbar-nav .nav-link.active .menu-icon {
            color: var(--teacher-primary);
        }

        .receptionist-theme .menu-label span {
            color: var(--teacher-primary);
            font-weight: 700;
        }

        .receptionist-theme .update-msg {
            background: var(--teacher-soft);
            border: 1px solid var(--teacher-border);
            border-radius: 16px;
            margin: 12px;
            padding: 20px 15px;
        }

        .receptionist-theme .update-msg .btn {
            background: var(--teacher-primary) !important;
            border-color: var(--teacher-primary) !important;
            color: #fff !important;
        }

        .receptionist-theme .teacher-avatar {
            background: var(--teacher-primary) !important;
            color: #fff !important;
        }

        .receptionist-theme .teacher-role-badge {
            background: var(--teacher-soft);
            color: var(--teacher-primary);
            border: 1px solid var(--teacher-border);
            font-size: 12px;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 999px;
        }

        .receptionist-theme .dropdown-menu .dropdown-item:active,
        .receptionist-theme .dropdown-menu .dropdown-item:hover {
            background: var(--teacher-soft);
            color: var(--teacher-primary);
        }

        .receptionist-theme .badge.text-bg-warning {
            background-color: var(--teacher-primary) !important;
            color: white !important;
        }

        html[data-bs-theme="dark"] .receptionist-theme {
            --teacher-primary: #CD0B6F;
            --teacher-dark: #CD0B6F;
            --teacher-soft: rgba(205, 11, 111, 0.16);
            --teacher-border: rgba(240, 100, 180, 0.22);
            --teacher-topbar-icon-bg: rgba(255, 255, 255, 0.08);
            --teacher-topbar-icon-border: rgba(255, 255, 255, 0.12);
            --teacher-topbar-icon-hover: rgba(255, 255, 255, 0.16);
            --teacher-dropdown-bg: #111827;
            --teacher-dropdown-color: #e5e7eb;
            --teacher-dropdown-muted: #9ca3af;
        }

        html[data-bs-theme="dark"] .receptionist-theme .topbar {
            background: #CD0B6F;
        }

        html[data-bs-theme="dark"] .receptionist-theme .startbar {
            background: #0f172a;
            border-right-color: var(--teacher-border);
        }

        html[data-bs-theme="dark"] .receptionist-theme .navbar-nav .nav-link {
            color: #cbd5e1;
        }

        html[data-bs-theme="dark"] .receptionist-theme .menu-label span {
            color: #86efac;
        }

        html[data-bs-theme="dark"] .receptionist-theme .update-msg {
            background: rgba(15, 23, 42, 0.92);
        }

        html[data-bs-theme="dark"] .receptionist-theme .dropdown-menu .text-dark,
        html[data-bs-theme="dark"] .receptionist-theme .dropdown-menu .text-body-tertiary {
            color: var(--teacher-dropdown-color) !important;
        }

        html[data-bs-theme="dark"] .receptionist-theme .nav-tabs-custom.nav-success .nav-link {
            color: #cbd5e1;
        }

        html[data-bs-theme="dark"] .receptionist-theme .navbar-nav .menu-icon,
        html[data-bs-theme="dark"] .receptionist-theme .menu-icon,
        html[data-bs-theme="dark"] .receptionist-theme .nav-link .menu-icon {
            color: #86a3b8;
        }
    </style>
    @stack('styles')

</head>

<body class="receptionist-theme">

    <!-- Top Bar Start -->
    <div class="topbar d-print-none">
        <div class="container-fluid">
            <nav class="topbar-custom d-flex justify-content-between" id="topbar-custom">


                <ul class="topbar-item list-unstyled d-inline-flex align-items-center mb-0">
                    <li>
                        <button class="nav-link mobile-menu-btn nav-icon" id="togglemenu">
                            <i class="iconoir-menu"></i>
                        </button>
                    </li>
                    <li class="mx-2 welcome-text">
                        <h5 class="mb-0 fw-semibold text-truncate">Good Morning, {{ auth()->user()->name }}!</h5>
                    </li>
                </ul>
                <ul class="topbar-item list-unstyled d-inline-flex align-items-center mb-0">
                    <li class="hide-phone app-search">
                        <form role="search" action="#" method="get">
                            <input type="search" name="search" class="form-control top-search mb-0"
                                placeholder="Search here...">
                            <button type="submit"><i class="iconoir-search"></i></button>
                        </form>
                    </li>

                    <li class="topbar-item">
                        <a class="nav-link nav-icon" href="javascript:void(0);" id="light-dark-mode">
                            <i class="iconoir-half-moon dark-mode"></i>
                            <i class="iconoir-sun-light light-mode"></i>
                        </a>
                    </li>

                    <li class="dropdown topbar-item">
                        <a class="nav-link dropdown-toggle arrow-none nav-icon" data-bs-toggle="dropdown" href="#"
                            role="button" aria-haspopup="false" aria-expanded="false" data-bs-offset="0,19">
                            <i class="iconoir-bell"></i>
                            <span class="alert-badge"></span>
                        </a>
                        <div class="dropdown-menu stop dropdown-menu-end dropdown-lg py-0">

                            <h5 class="dropdown-item-text m-0 py-3 d-flex justify-content-between align-items-center">
                                Notifications <a href="#" class="badge text-body-tertiary badge-pill">
                                    <i class="iconoir-plus-circle fs-4"></i>
                                </a>
                            </h5>
                            <ul class="nav nav-tabs nav-tabs-custom nav-success nav-justified mb-1" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link mx-0 active" data-bs-toggle="tab" href="#All" role="tab"
                                        aria-selected="true">
                                        All <span class="badge bg-primary-subtle text-primary badge-pill ms-1">24</span>
                                    </a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link mx-0" data-bs-toggle="tab" href="#Projects" role="tab"
                                        aria-selected="false" tabindex="-1">
                                        Messages
                                    </a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link mx-0" data-bs-toggle="tab" href="#Teams" role="tab"
                                        aria-selected="false" tabindex="-1">
                                        Announcements
                                    </a>
                                </li>
                            </ul>
                            <div class="ms-0" style="max-height:230px;" data-simplebar>
                                <div class="tab-content" id="myTabContent">
                                    <div class="tab-pane fade show active" id="All" role="tabpanel"
                                        aria-labelledby="all-tab" tabindex="0">
                                        <!-- item-->
                                        <a href="#" class="dropdown-item py-3">
                                            <small class="float-end text-muted ps-2">2 min ago</small>
                                            <div class="d-flex align-items-center">
                                                <div
                                                    class="flex-shrink-0 bg-primary-subtle text-primary thumb-md rounded-circle">
                                                    <i class="iconoir-wolf fs-4"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-2 text-truncate">
                                                    <h6 class="my-0 fw-normal text-dark fs-13">Your order is placed
                                                    </h6>
                                                    <small class="text-muted mb-0">Dummy text of the printing and
                                                        industry.</small>
                                                </div><!--end media-body-->
                                            </div><!--end media-->
                                        </a><!--end-item-->
                                        <!-- item-->
                                        <a href="#" class="dropdown-item py-3">
                                            <small class="float-end text-muted ps-2">10 min ago</small>
                                            <div class="d-flex align-items-center">
                                                <div
                                                    class="flex-shrink-0 bg-primary-subtle text-primary thumb-md rounded-circle">
                                                    <i class="iconoir-apple-swift fs-4"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-2 text-truncate">
                                                    <h6 class="my-0 fw-normal text-dark fs-13">Meeting with designers
                                                    </h6>
                                                    <small class="text-muted mb-0">It is a long established fact that a
                                                        reader.</small>
                                                </div><!--end media-body-->
                                            </div><!--end media-->
                                        </a><!--end-item-->
                                        <!-- item-->
                                        <a href="#" class="dropdown-item py-3">
                                            <small class="float-end text-muted ps-2">40 min ago</small>
                                            <div class="d-flex align-items-center">
                                                <div
                                                    class="flex-shrink-0 bg-primary-subtle text-primary thumb-md rounded-circle">
                                                    <i class="iconoir-birthday-cake fs-4"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-2 text-truncate">
                                                    <h6 class="my-0 fw-normal text-dark fs-13">UX 3 Task complete.</h6>
                                                    <small class="text-muted mb-0">Dummy text of the printing.</small>
                                                </div><!--end media-body-->
                                            </div><!--end media-->
                                        </a><!--end-item-->
                                        <!-- item-->
                                        <a href="#" class="dropdown-item py-3">
                                            <small class="float-end text-muted ps-2">1 hr ago</small>
                                            <div class="d-flex align-items-center">
                                                <div
                                                    class="flex-shrink-0 bg-primary-subtle text-primary thumb-md rounded-circle">
                                                    <i class="iconoir-drone fs-4"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-2 text-truncate">
                                                    <h6 class="my-0 fw-normal text-dark fs-13">Your order is placed
                                                    </h6>
                                                    <small class="text-muted mb-0">It is a long established fact that a
                                                        reader.</small>
                                                </div><!--end media-body-->
                                            </div><!--end media-->
                                        </a><!--end-item-->
                                        <!-- item-->
                                        <a href="#" class="dropdown-item py-3">
                                            <small class="float-end text-muted ps-2">2 hrs ago</small>
                                            <div class="d-flex align-items-center">
                                                <div
                                                    class="flex-shrink-0 bg-primary-subtle text-primary thumb-md rounded-circle">
                                                    <i class="iconoir-user fs-4"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-2 text-truncate">
                                                    <h6 class="my-0 fw-normal text-dark fs-13">Payment Successfull</h6>
                                                    <small class="text-muted mb-0">Dummy text of the printing.</small>
                                                </div><!--end media-body-->
                                            </div><!--end media-->
                                        </a><!--end-item-->
                                    </div>
                                    <div class="tab-pane fade" id="Projects" role="tabpanel"
                                        aria-labelledby="projects-tab" tabindex="0">
                                        <!-- item-->
                                        <a href="#" class="dropdown-item py-3">
                                            <small class="float-end text-muted ps-2">40 min ago</small>
                                            <div class="d-flex align-items-center">
                                                <div
                                                    class="flex-shrink-0 bg-primary-subtle text-primary thumb-md rounded-circle">
                                                    <i class="iconoir-birthday-cake fs-4"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-2 text-truncate">
                                                    <h6 class="my-0 fw-normal text-dark fs-13">UX 3 Task complete.</h6>
                                                    <small class="text-muted mb-0">Dummy text of the printing.</small>
                                                </div><!--end media-body-->
                                            </div><!--end media-->
                                        </a><!--end-item-->
                                        <!-- item-->
                                        <a href="#" class="dropdown-item py-3">
                                            <small class="float-end text-muted ps-2">1 hr ago</small>
                                            <div class="d-flex align-items-center">
                                                <div
                                                    class="flex-shrink-0 bg-primary-subtle text-primary thumb-md rounded-circle">
                                                    <i class="iconoir-drone fs-4"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-2 text-truncate">
                                                    <h6 class="my-0 fw-normal text-dark fs-13">Your order is placed
                                                    </h6>
                                                    <small class="text-muted mb-0">It is a long established fact that a
                                                        reader.</small>
                                                </div><!--end media-body-->
                                            </div><!--end media-->
                                        </a><!--end-item-->
                                        <!-- item-->
                                        <a href="#" class="dropdown-item py-3">
                                            <small class="float-end text-muted ps-2">2 hrs ago</small>
                                            <div class="d-flex align-items-center">
                                                <div
                                                    class="flex-shrink-0 bg-primary-subtle text-primary thumb-md rounded-circle">
                                                    <i class="iconoir-user fs-4"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-2 text-truncate">
                                                    <h6 class="my-0 fw-normal text-dark fs-13">Payment Successfull</h6>
                                                    <small class="text-muted mb-0">Dummy text of the printing.</small>
                                                </div><!--end media-body-->
                                            </div><!--end media-->
                                        </a><!--end-item-->
                                    </div>
                                    <div class="tab-pane fade" id="Teams" role="tabpanel"
                                        aria-labelledby="teams-tab" tabindex="0">
                                        <!-- item-->
                                        <a href="#" class="dropdown-item py-3">
                                            <small class="float-end text-muted ps-2">1 hr ago</small>
                                            <div class="d-flex align-items-center">
                                                <div
                                                    class="flex-shrink-0 bg-primary-subtle text-primary thumb-md rounded-circle">
                                                    <i class="iconoir-drone fs-4"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-2 text-truncate">
                                                    <h6 class="my-0 fw-normal text-dark fs-13">Your order is placed
                                                    </h6>
                                                    <small class="text-muted mb-0">It is a long established fact that a
                                                        reader.</small>
                                                </div><!--end media-body-->
                                            </div><!--end media-->
                                        </a><!--end-item-->
                                        <!-- item-->
                                        <a href="#" class="dropdown-item py-3">
                                            <small class="float-end text-muted ps-2">2 hrs ago</small>
                                            <div class="d-flex align-items-center">
                                                <div
                                                    class="flex-shrink-0 bg-primary-subtle text-primary thumb-md rounded-circle">
                                                    <i class="iconoir-user fs-4"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-2 text-truncate">
                                                    <h6 class="my-0 fw-normal text-dark fs-13">Payment Successfull</h6>
                                                    <small class="text-muted mb-0">Dummy text of the printing.</small>
                                                </div><!--end media-body-->
                                            </div><!--end media-->
                                        </a><!--end-item-->
                                    </div>
                                </div>

                            </div>
                            <!-- All-->
                            <a href="pages-notifications.html" class="dropdown-item text-center text-dark fs-13 py-2">
                                View All <i class="fi-arrow-right"></i>
                            </a>
                        </div>
                    </li>

                    <li class="dropdown topbar-item">
                        <a class="nav-link dropdown-toggle arrow-none nav-icon" data-bs-toggle="dropdown"
                            href="#" role="button" aria-haspopup="false" aria-expanded="false"
                            data-bs-offset="0,19">
                            {{-- <img src="assets/images/users/avatar-1.jpg" alt=""
                                class="thumb-md rounded-circle"> --}}
                            <div
                                class="thumb-md rounded-circle bg-black text-white d-flex align-items-center justify-content-center fw-bold">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1) . substr(strrchr(auth()->user()->name, ' '), 1, 1)) }}
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end py-0">
                            <div class="d-flex align-items-center dropdown-item py-2 bg-secondary-subtle">
                                <div class="flex-shrink-0">
                                    {{-- <img src="assets/images/users/avatar-1.jpg" alt=""
                                        class="thumb-md rounded-circle"> --}}
                                    <div
                                        class="thumb-md rounded-circle bg-black text-white d-flex align-items-center justify-content-center fw-bold">
                                        {{ strtoupper(substr(auth()->user()->name, 0, 1) . substr(strrchr(auth()->user()->name, ' '), 1, 1)) }}
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-2 text-truncate align-self-center">
                                    <h6 class="my-0 fw-medium text-dark fs-13">{{ auth()->user()->name }}</h6>
                                    <small class="text-muted mb-0">{{ auth()->user()->email }}</small>
                                </div><!--end media-body-->
                            </div>
                            <div class="dropdown-divider mt-0"></div>
                            <small class="text-muted px-2 pb-1 d-block">Account</small>
                            <a class="dropdown-item" href="{{ route('shareholder.profile') }}"><i
                                    class="las la-user fs-18 me-1 align-text-bottom"></i> Profile</a>
                            <a class="dropdown-item" href="pages-faq.html"><i
                                    class="las la-wallet fs-18 me-1 align-text-bottom"></i> Earning</a>
                            <small class="text-muted px-2 py-1 d-block">Settings</small>
                            <a class="dropdown-item" href="{{ route('shareholder.profile') }}"><i
                                    class="las la-cog fs-18 me-1 align-text-bottom"></i>Account Settings</a>
                            <a class="dropdown-item" href="{{ route('shareholder.profile') }}"><i
                                    class="las la-lock fs-18 me-1 align-text-bottom"></i> Security</a>
                            <a class="dropdown-item" href="pages-faq.html"><i
                                    class="las la-question-circle fs-18 me-1 align-text-bottom"></i> Help Center</a>
                            <div class="dropdown-divider mb-0"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="las la-power-off fs-18 me-1 align-text-bottom"></i> Sign out
                                </button>
                            </form>
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
                    <img src="{{ asset('images/oms-web-logo.png') }}" alt="logo-large" class="logo-lg logo-light">
                    <img src="{{ asset('images/oms-web-logo.png') }}" alt="logo-large" class="logo-lg logo-dark">
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
                            <span>Navigation</span>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('shareholder.dashboard') }}">
                                <i class="iconoir-report-columns menu-icon"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="#sidebarRewards" data-bs-toggle="collapse" role="button"
                                aria-expanded="false" aria-controls="sidebarRewards">
                                <i class="iconoir-wallet menu-icon"></i>
                                <span>Rewards</span>
                            </a>
                            <div class="collapse" id="sidebarRewards">
                                <ul class="nav flex-column">
                                    <li class="nav-item">
                                        <a href="#" class="nav-link">My Wallet</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#" class="nav-link">Transaction History</a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="#sidebarZone" data-bs-toggle="collapse" role="button"
                                aria-expanded="false" aria-controls="sidebarZone">
                                <i class="iconoir-group menu-icon"></i>
                                <span>Shareholder Zone</span>
                            </a>
                            <div class="collapse" id="sidebarZone">
                                <ul class="nav flex-column">
                                    <li class="nav-item">
                                        <a href="#" class="nav-link">AGM Notices</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#" class="nav-link">Financial Reports</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#" class="nav-link">Announcements</a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('shareholder.bills.index') }}">
                                <i class="iconoir-paste-clipboard menu-icon"></i>
                                <span>My Bills</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="iconoir-megaphone menu-icon"></i>
                                <span>Campaigns</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="iconoir-bell menu-icon"></i>
                                <span>Notifications</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('shareholder.profile') }}">
                                <i class="iconoir-user menu-icon"></i>
                                <span>My Profile</span>
                            </a>
                        </li>

                    </ul>
                    <div class="update-msg text-center">
                        <div
                            class="d-flex justify-content-center align-items-center thumb-lg update-icon-box  rounded-circle mx-auto">
                            <img src="{{ asset('assets/images/extra/party.gif') }}" alt=""
                                class="d-inline-block me-1" height="30">
                        </div>
                        <h5 class="mt-3">{{ config('system.name') }}</h5>
                        <p class="mb-3 text-muted">OmConnect is a high quality web application.</p>
                        <a href="javascript: void(0);" class="btn bg-black text-white shadow-sm rounded-pill">Upgrade
                            your plan</a>
                    </div>
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

    <script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script src="https://apexcharts.com/samples/assets/stock-prices.js"></script>
    <script src="{{ asset('assets/js/pages/index.init.js') }}"></script>

    <script src="{{ asset('assets/libs/simple-datatables/umd/simple-datatables.js') }}"></script>
    <script src="{{ asset('assets/js/pages/datatable.init.js') }}"></script>

    <!-- Sweet-Alert  -->
    <script src="{{ asset('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/sweet-alert.init.js') }}"></script>

    <script src="{{ asset('assets/js/app.js') }}"></script>
    @stack('scripts')
</body>
<!--end body-->

</html>
