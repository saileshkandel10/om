import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../../core/api/api_client.dart';
import '../../../../core/config/app_config.dart';
import '../../../../core/constants/app_theme.dart';
import '../../domain/auth_provider.dart';

class LoginScreen extends ConsumerStatefulWidget {
  const LoginScreen({super.key});

  @override
  ConsumerState<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends ConsumerState<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController(text: 'customer@omconnect.com');
  final _passwordController = TextEditingController(text: 'Password123!');
  bool _obscurePassword = true;
  String _selectedRoleKey = 'Patient';
  bool _isAutoDetecting = false;

  final List<Map<String, dynamic>> _demoAccounts = [
    {
      'role': 'Patient',
      'email': 'customer@omconnect.com',
      'icon': Icons.favorite_rounded,
      'color': AppColors.danger,
      'description': 'Patient & Loyalty User',
    },
    {
      'role': 'Shareholder',
      'email': 'shareholder@omconnect.com',
      'icon': Icons.trending_up_rounded,
      'color': AppColors.primary,
      'description': 'Verified Shareholder',
    },
    {
      'role': 'Pharmacy',
      'email': 'pharmacy@omconnect.com',
      'icon': Icons.local_pharmacy_rounded,
      'color': AppColors.secondary,
      'description': 'Retail Pharmacy Partner',
    },
    {
      'role': 'Doctor',
      'email': 'doctor@omconnect.com',
      'icon': Icons.medical_services_rounded,
      'color': AppColors.info,
      'description': 'Verified Physician',
    },
    {
      'role': 'Admin',
      'email': 'admin@omconnect.com',
      'icon': Icons.admin_panel_settings_rounded,
      'color': AppColors.accent,
      'description': 'System Administrator',
    },
  ];

  @override
  void initState() {
    super.initState();
    _probeServerOnStart();
  }

  Future<void> _probeServerOnStart() async {
    final detected = await AppConfig.autoDetectServer();
    if (detected != null && mounted) {
      ApiClient.setBaseUrl(detected);
      setState(() {});
    }
  }

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  void _handleLogin() {
    if (!_formKey.currentState!.validate()) return;
    FocusScope.of(context).unfocus();

    ref.read(authProvider.notifier).login(
          email: _emailController.text.trim(),
          password: _passwordController.text,
        );
  }

  void _selectDemoAccount(Map<String, dynamic> acc, {bool submit = false}) {
    setState(() {
      _selectedRoleKey = acc['role'] as String;
      _emailController.text = acc['email'] as String;
      _passwordController.text = 'Password123!';
    });

    if (submit) {
      _handleLogin();
    }
  }

  void _showServerSettingsDialog() {
    final urlController = TextEditingController(text: AppConfig.baseUrl);

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) {
        return StatefulBuilder(
          builder: (bottomContext, setModalState) {
            return Padding(
              padding: EdgeInsets.only(
                left: 20,
                right: 20,
                top: 20,
                bottom: MediaQuery.of(ctx).viewInsets.bottom + 24,
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Center(
                    child: Container(
                      width: 40,
                      height: 4,
                      margin: const EdgeInsets.only(bottom: 16),
                      decoration: BoxDecoration(
                        color: Colors.grey.shade300,
                        borderRadius: BorderRadius.circular(2),
                      ),
                    ),
                  ),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Row(
                        children: [
                          Icon(Icons.dns_rounded, color: AppColors.primary),
                          SizedBox(width: 8),
                          Text('Server Configuration', style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold)),
                        ],
                      ),
                      TextButton.icon(
                        onPressed: _isAutoDetecting
                            ? null
                            : () async {
                                setModalState(() => _isAutoDetecting = true);
                                final found = await AppConfig.autoDetectServer();
                                setModalState(() => _isAutoDetecting = false);
                                if (found != null) {
                                  urlController.text = found;
                                  ApiClient.setBaseUrl(found);
                                  if (mounted) setState(() {});
                                }
                              },
                        icon: _isAutoDetecting
                            ? const SizedBox(width: 12, height: 12, child: CircularProgressIndicator(strokeWidth: 2))
                            : const Icon(Icons.autorenew, size: 16),
                        label: const Text('Auto-Detect', style: TextStyle(fontSize: 12)),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  const Text(
                    'Select or enter the server API URL reachable from this device:',
                    style: TextStyle(fontSize: 13, color: AppColors.textSecondary),
                  ),
                  const SizedBox(height: 14),
                  TextField(
                    controller: urlController,
                    decoration: InputDecoration(
                      labelText: 'API Base URL',
                      hintText: 'http://192.168.1.118:8000/api/v1',
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                      prefixIcon: const Icon(Icons.link),
                      isDense: true,
                    ),
                  ),
                  const SizedBox(height: 14),
                  const Text('Quick Presets:', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                  const SizedBox(height: 8),
                  Wrap(
                    spacing: 8,
                    runSpacing: 6,
                    children: [
                      ActionChip(
                        avatar: const Icon(Icons.wifi, size: 14, color: AppColors.primary),
                        label: const Text('Wi-Fi (192.168.1.118)', style: TextStyle(fontSize: 11)),
                        onPressed: () => urlController.text = 'http://192.168.1.118:8000/api/v1',
                      ),
                      ActionChip(
                        avatar: const Icon(Icons.phone_android, size: 14, color: AppColors.info),
                        label: const Text('Emulator (10.0.2.2)', style: TextStyle(fontSize: 11)),
                        onPressed: () => urlController.text = 'http://10.0.2.2:8000/api/v1',
                      ),
                      ActionChip(
                        avatar: const Icon(Icons.computer, size: 14, color: AppColors.secondary),
                        label: const Text('Localhost (127.0.0.1)', style: TextStyle(fontSize: 11)),
                        onPressed: () => urlController.text = 'http://127.0.0.1:8000/api/v1',
                      ),
                      ActionChip(
                        avatar: const Icon(Icons.cloud, size: 14, color: AppColors.accent),
                        label: const Text('Production', style: TextStyle(fontSize: 11)),
                        onPressed: () => urlController.text = 'https://om.lims.com.np/api/v1',
                      ),
                    ],
                  ),
                  const SizedBox(height: 20),
                  ElevatedButton(
                    onPressed: () async {
                      final messenger = ScaffoldMessenger.of(context);
                      final nav = Navigator.of(ctx);
                      final newUrl = urlController.text.trim();
                      if (newUrl.isNotEmpty) {
                        await AppConfig.saveBaseUrl(newUrl);
                        ApiClient.setBaseUrl(newUrl);
                        if (mounted) {
                          setState(() {});
                          nav.pop();
                          messenger.showSnackBar(
                            SnackBar(content: Text('Server connected: $newUrl'), backgroundColor: AppColors.secondary),
                          );
                        }
                      }
                    },
                    child: const Text('Save & Apply Server'),
                  ),
                ],
              ),
            );
          },
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authProvider);

    ref.listen<AuthState>(authProvider, (_, state) {
      if (state.status == AuthStatus.error && state.error != null) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(state.error!),
            backgroundColor: AppColors.danger,
            action: SnackBarAction(
              label: 'Fix Server',
              textColor: Colors.white,
              onPressed: _showServerSettingsDialog,
            ),
          ),
        );
      }
    });

    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.dns_rounded, color: AppColors.primary),
            tooltip: 'Server Settings',
            onPressed: _showServerSettingsDialog,
          ),
        ],
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 24),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Logo
              Center(
                child: Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    shape: BoxShape.circle,
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withValues(alpha: 0.06),
                        blurRadius: 15,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(35),
                    child: Image.asset(
                      'assets/images/oms-web-logo.png',
                      height: 65,
                      width: 65,
                      fit: BoxFit.contain,
                      errorBuilder: (_, __, ___) => Container(
                        width: 65,
                        height: 65,
                        decoration: BoxDecoration(
                          color: AppColors.primary,
                          borderRadius: BorderRadius.circular(16),
                        ),
                        child: const Icon(
                          Icons.medical_services_rounded,
                          size: 36,
                          color: Colors.white,
                        ),
                      ),
                    ),
                  ),
                ),
              ),
              const SizedBox(height: 12),
              const Text(
                'OM Connect',
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 26,
                  fontWeight: FontWeight.bold,
                  color: AppColors.primary,
                  letterSpacing: 0.5,
                ),
              ),
              const SizedBox(height: 4),
              const Text(
                'Om Megashree Pharmaceuticals Limited',
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.w500,
                  color: AppColors.textSecondary,
                ),
              ),
              const SizedBox(height: 12),

              // Server indicator banner
              Center(
                child: GestureDetector(
                  onTap: _showServerSettingsDialog,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                    decoration: BoxDecoration(
                      color: AppColors.surface,
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: AppColors.border),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Container(
                          width: 8,
                          height: 8,
                          decoration: const BoxDecoration(
                            color: AppColors.secondary,
                            shape: BoxShape.circle,
                          ),
                        ),
                        const SizedBox(width: 6),
                        Flexible(
                          child: Text(
                            AppConfig.baseUrl.replaceAll('http://', '').replaceAll('/api/v1', ''),
                            overflow: TextOverflow.ellipsis,
                            style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: AppColors.textPrimary),
                          ),
                        ),
                        const SizedBox(width: 4),
                        const Icon(Icons.arrow_drop_down, size: 16, color: AppColors.textSecondary),
                      ],
                    ),
                  ),
                ),
              ),

              const SizedBox(height: 20),

              // Role Selector Tabs / Pills
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Select Account Role:',
                    style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.textSecondary),
                  ),
                  const SizedBox(height: 8),
                  SingleChildScrollView(
                    scrollDirection: Axis.horizontal,
                    child: Row(
                      children: _demoAccounts.map((acc) {
                        final isSelected = _selectedRoleKey == acc['role'];
                        final color = acc['color'] as Color;
                        return Padding(
                          padding: const EdgeInsets.only(right: 8),
                          child: ChoiceChip(
                            avatar: Icon(acc['icon'] as IconData, size: 14, color: isSelected ? Colors.white : color),
                            label: Text(acc['role'] as String),
                            selected: isSelected,
                            selectedColor: color,
                            labelStyle: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.bold,
                              color: isSelected ? Colors.white : AppColors.textPrimary,
                            ),
                            backgroundColor: AppColors.surface,
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(14),
                              side: BorderSide(
                                color: isSelected ? color : AppColors.border,
                              ),
                            ),
                            onSelected: (selected) {
                              if (selected) {
                                _selectDemoAccount(acc, submit: false);
                              }
                            },
                          ),
                        );
                      }).toList(),
                    ),
                  ),
                ],
              ),

              const SizedBox(height: 16),

              // Form
              Form(
                key: _formKey,
                child: Column(
                  children: [
                    TextFormField(
                      controller: _emailController,
                      keyboardType: TextInputType.emailAddress,
                      textInputAction: TextInputAction.next,
                      decoration: InputDecoration(
                        labelText: 'Email Address',
                        prefixIcon: const Icon(Icons.email_outlined),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                      validator: (value) {
                        if (value == null || value.isEmpty) {
                          return 'Email is required';
                        }
                        if (!value.contains('@')) {
                          return 'Enter a valid email';
                        }
                        return null;
                      },
                    ),
                    const SizedBox(height: 14),
                    TextFormField(
                      controller: _passwordController,
                      obscureText: _obscurePassword,
                      textInputAction: TextInputAction.done,
                      onFieldSubmitted: (_) => _handleLogin(),
                      decoration: InputDecoration(
                        labelText: 'Password',
                        prefixIcon: const Icon(Icons.lock_outlined),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                        suffixIcon: IconButton(
                          icon: Icon(_obscurePassword
                              ? Icons.visibility_off
                              : Icons.visibility),
                          onPressed: () {
                            setState(() => _obscurePassword = !_obscurePassword);
                          },
                        ),
                      ),
                      validator: (value) {
                        if (value == null || value.isEmpty) {
                          return 'Password is required';
                        }
                        return null;
                      },
                    ),
                    const SizedBox(height: 20),
                    ElevatedButton(
                      onPressed: authState.isLoading ? null : _handleLogin,
                      child: authState.isLoading
                          ? const SizedBox(
                              height: 20,
                              width: 20,
                              child: CircularProgressIndicator(
                                strokeWidth: 2,
                                color: Colors.white,
                              ),
                            )
                          : Text('Sign In as $_selectedRoleKey'),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 16),

              Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Text(
                    "Don't have an account? ",
                    style: TextStyle(color: AppColors.textSecondary),
                  ),
                  TextButton(
                    onPressed: () => context.go('/register'),
                    child: const Text('Register', style: TextStyle(fontWeight: FontWeight.bold)),
                  ),
                ],
              ),
              const SizedBox(height: 24),
            ],
          ),
        ),
      ),
    );
  }
}
