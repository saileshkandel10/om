<!DOCTYPE html>
<html lang="en" dir="ltr" data-startbar="light" data-bs-theme="light">

<head>
    <meta charset="utf-8" />
    <title>Sign In | OM Connect - Om Megashree Pharmaceuticals</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta content="OM Connect Pharmaceutical Rewards & Management Platform" name="description" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="https://ommegashreepharmaceuticals.com/wp-content/uploads/2020/12/cropped-imageedit_1_6169141058-180x180.jpg">

    <!-- App css -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />

    <style>
        :root {
            --om-brand-pink: #CD0B6F;
            --om-brand-dark-pink: #A30958;
            --om-brand-soft-pink: #FDE8F3;
            --om-brand-navy: #1E3A5F;
            --om-dark-bg: #0B1528;
        }
        body {
            background: linear-gradient(135deg, #091424 0%, #11233B 50%, #1A0D20 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            padding: 1.25rem;
            margin: 0;
        }
        .login-card {
            max-width: 440px;
            width: 100%;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.45), 0 0 0 1px rgba(255, 255, 255, 0.08);
            background: #ffffff;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .login-header-banner {
            background: linear-gradient(135deg, #1E3A5F 0%, #142842 60%, #CD0B6F 100%);
            padding: 2rem 1.75rem 1.75rem;
            text-align: center;
            position: relative;
        }
        .login-header-banner::after {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: radial-gradient(circle at top right, rgba(205, 11, 111, 0.35), transparent 70%);
            pointer-events: none;
        }
        .logo-box {
            background: #ffffff;
            padding: 10px 16px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
            margin-bottom: 0.85rem;
            position: relative;
            z-index: 1;
        }
        .logo-box img {
            max-height: 42px;
            object-fit: contain;
        }
        .btn-brand-login {
            background: var(--om-brand-pink);
            border-color: var(--om-brand-pink);
            color: #ffffff;
            padding: 0.75rem 1rem;
            font-weight: 600;
            font-size: 0.95rem;
            letter-spacing: 0.3px;
            border-radius: 12px;
            transition: all 0.25s ease;
            box-shadow: 0 4px 14px rgba(205, 11, 111, 0.35);
        }
        .btn-brand-login:hover, .btn-brand-login:focus, .btn-brand-login:active {
            background: var(--om-brand-dark-pink) !important;
            border-color: var(--om-brand-dark-pink) !important;
            color: #ffffff !important;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(205, 11, 111, 0.45);
        }
        .form-control-compact {
            border-radius: 10px;
            padding: 0.65rem 0.9rem;
            border: 1.5px solid #e2e8f0;
            font-size: 0.9rem;
            background-color: #f8fafc;
            transition: all 0.2s ease;
        }
        .form-control-compact:focus {
            border-color: var(--om-brand-pink);
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(205, 11, 111, 0.12);
        }
        .input-group-text-compact {
            background-color: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-right: none;
            border-radius: 10px 0 0 10px;
            color: #64748b;
            padding: 0.65rem 0.85rem;
        }
        .demo-pill {
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            padding: 5px 10px;
            margin: 3px;
            font-size: 11px;
            font-weight: 600;
            border-radius: 8px;
            background: #fdf2f8;
            color: var(--om-brand-pink);
            border: 1px solid #fbcfe8;
            transition: all 0.15s ease;
            user-select: none;
        }
        .demo-pill:hover {
            background: var(--om-brand-pink);
            color: #ffffff;
            border-color: var(--om-brand-pink);
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(205, 11, 111, 0.25);
        }
        .password-toggle {
            cursor: pointer;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-left: none;
            border-radius: 0 10px 10px 0;
            color: #94a3b8;
            padding: 0.65rem 0.85rem;
            transition: color 0.2s ease;
        }
        .password-toggle:hover {
            color: var(--om-brand-pink);
        }
    </style>
</head>

<body>
    <div class="login-card">
        <!-- Compact Header -->
        <div class="login-header-banner">
            <div class="logo-box">
                <img src="{{ asset('images/oms-web-logo.png') }}" 
                     onerror="this.src='https://ommegashreepharmaceuticals.com/wp-content/uploads/2024/12/oms-web-logo.png'" 
                     alt="OM Connect Logo">
            </div>
            <h5 class="fw-bold text-white mb-0 fs-5" style="letter-spacing: 0.3px;">OM Connect</h5>
            <p class="text-white-50 small mb-0 fs-12">Om Megashree Pharmaceuticals Limited</p>
        </div>

        <!-- Form Body -->
        <div class="p-4">
            <div class="mb-3 text-center">
                <h6 class="fw-bold text-dark mb-1 fs-16">Sign in to your account</h6>
                <p class="text-muted fs-12 mb-0">Enter your credentials to access the portal</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger p-2 mb-3 rounded-3 small">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label text-dark fs-12 fw-bold mb-1">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text input-group-text-compact">
                            <i class="las la-envelope fs-16"></i>
                        </span>
                        <input type="email" 
                               class="form-control form-control-compact border-start-0" 
                               id="email" 
                               name="email" 
                               value="{{ old('email', 'admin@omconnect.com') }}" 
                               required 
                               autofocus 
                               placeholder="name@example.com"
                               style="border-radius: 0 10px 10px 0;">
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label for="password" class="form-label text-dark fs-12 fw-bold mb-0">Password</label>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text input-group-text-compact">
                            <i class="las la-lock fs-16"></i>
                        </span>
                        <input type="password" 
                               class="form-control form-control-compact border-start-0 border-end-0" 
                               id="password" 
                               name="password" 
                               value="Password123!" 
                               required 
                               placeholder="••••••••"
                               style="border-radius: 0;">
                        <span class="input-group-text password-toggle" onclick="togglePasswordVisibility()">
                            <i class="las la-eye" id="togglePasswordIcon"></i>
                        </span>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" checked style="border-radius: 4px;">
                        <label class="form-check-label text-muted fs-12 fw-medium" for="remember">
                            Remember me
                        </label>
                    </div>
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-brand-login">
                        Sign In <i class="las la-arrow-right ms-1"></i>
                    </button>
                </div>
            </form>

            <!-- Quick Demo Credentials Section -->
            <div class="pt-2 border-top">
                <p class="text-muted fs-11 fw-bold mb-2 text-center text-uppercase" style="letter-spacing: 0.5px;">Quick Demo Login:</p>
                <div class="d-flex flex-wrap justify-content-center">
                    <span class="demo-pill" onclick="fillDemo('admin@omconnect.com', 'Password123!')">
                        <i class="las la-user-shield me-1"></i> Admin
                    </span>
                    <span class="demo-pill" onclick="fillDemo('customer@omconnect.com', 'Password123!')">
                        <i class="las la-heartbeat me-1"></i> Patient
                    </span>
                    <span class="demo-pill" onclick="fillDemo('shareholder@omconnect.com', 'Password123!')">
                        <i class="las la-chart-pie me-1"></i> Shareholder
                    </span>
                    <span class="demo-pill" onclick="fillDemo('pharmacy@omconnect.com', 'Password123!')">
                        <i class="las la-clinic-medical me-1"></i> Pharmacy
                    </span>
                    <span class="demo-pill" onclick="fillDemo('doctor@omconnect.com', 'Password123!')">
                        <i class="las la-user-md me-1"></i> Doctor
                    </span>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script>
        function fillDemo(email, password) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
        }

        function togglePasswordVisibility() {
            const pwdInput = document.getElementById('password');
            const icon = document.getElementById('togglePasswordIcon');
            if (pwdInput.type === 'password') {
                pwdInput.type = 'text';
                icon.className = 'las la-eye-slash';
            } else {
                pwdInput.type = 'password';
                icon.className = 'las la-eye';
            }
        }
    </script>
</body>

</html>
