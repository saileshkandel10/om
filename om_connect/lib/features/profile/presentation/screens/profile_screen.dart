import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:image_picker/image_picker.dart';
import '../../../../core/api/api_client.dart';
import '../../../../core/api/api_endpoints.dart';
import '../../../../core/config/app_config.dart';
import '../../../../core/constants/app_theme.dart';
import '../../../auth/domain/auth_provider.dart';

class ProfileScreen extends ConsumerStatefulWidget {
  const ProfileScreen({super.key});

  @override
  ConsumerState<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends ConsumerState<ProfileScreen> {
  bool _isUploadingAvatar = false;
  final ImagePicker _picker = ImagePicker();

  String _resolveAvatarUrl(String? path) {
    if (path == null || path.isEmpty) return '';
    if (path.startsWith('http')) return path;
    final base = AppConfig.baseUrl.replaceAll('/api/v1', '');
    return '$base/storage/$path';
  }

  Future<void> _pickAndUploadAvatar() async {
    try {
      final XFile? image = await _picker.pickImage(
        source: ImageSource.gallery,
        maxWidth: 1024,
        maxHeight: 1024,
        imageQuality: 85,
      );

      if (image == null) return;
      if (!mounted) return;

      setState(() => _isUploadingAvatar = true);

      final messenger = ScaffoldMessenger.of(context);

      final formData = FormData.fromMap({
        'avatar': await MultipartFile.fromFile(
          image.path,
          filename: image.name,
        ),
      });

      final response = await ApiClient.instance.post(
        ApiEndpoints.profileAvatar,
        data: formData,
      );

      if (mounted) {
        setState(() => _isUploadingAvatar = false);
        await ref.read(authProvider.notifier).refreshUser();

        final msg = response.data?['message'] ?? 'Avatar updated successfully.';
        messenger.showSnackBar(
          SnackBar(
            content: Text(msg),
            backgroundColor: AppColors.secondary,
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isUploadingAvatar = false);
        final err = e is ApiException ? e.message : 'Failed to upload avatar image.';
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(err),
            backgroundColor: AppColors.danger,
          ),
        );
      }
    }
  }

  void _showEditProfileSheet(String currentName, String? currentMobile) {
    final formKey = GlobalKey<FormState>();
    final nameController = TextEditingController(text: currentName);
    final mobileController = TextEditingController(text: currentMobile ?? '');
    bool isSaving = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (bottomSheetContext) {
        return StatefulBuilder(
          builder: (context, setModalState) {
            return Padding(
              padding: EdgeInsets.only(
                bottom: MediaQuery.of(context).viewInsets.bottom,
              ),
              child: Container(
                decoration: const BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
                ),
                padding: const EdgeInsets.all(24),
                child: Form(
                  key: formKey,
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    crossAxisAlignment: CrossAxisAlignment.stretch,
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
                      const Text(
                        'Edit Profile',
                        style: TextStyle(
                          fontSize: 20,
                          fontWeight: FontWeight.bold,
                          color: AppColors.textPrimary,
                        ),
                      ),
                      const SizedBox(height: 16),
                      TextFormField(
                        controller: nameController,
                        decoration: const InputDecoration(
                          labelText: 'Full Name',
                          prefixIcon: Icon(Icons.person_outline),
                        ),
                        validator: (value) {
                          if (value == null || value.trim().isEmpty) {
                            return 'Name cannot be empty';
                          }
                          return null;
                        },
                      ),
                      const SizedBox(height: 16),
                      TextFormField(
                        controller: mobileController,
                        keyboardType: TextInputType.phone,
                        decoration: const InputDecoration(
                          labelText: 'Mobile Number',
                          prefixIcon: Icon(Icons.phone_outlined),
                        ),
                        validator: (value) {
                          if (value != null && value.trim().isNotEmpty && value.trim().length < 7) {
                            return 'Enter a valid phone number';
                          }
                          return null;
                        },
                      ),
                      const SizedBox(height: 24),
                      ElevatedButton(
                        onPressed: isSaving
                            ? null
                            : () async {
                                if (!formKey.currentState!.validate()) return;
                                setModalState(() => isSaving = true);

                                final messenger = ScaffoldMessenger.of(context);
                                final nav = Navigator.of(bottomSheetContext);

                                try {
                                  await ApiClient.instance.put(
                                    ApiEndpoints.profile,
                                    data: {
                                      'name': nameController.text.trim(),
                                      'mobile': mobileController.text.trim(),
                                    },
                                  );

                                  if (mounted) {
                                    await ref.read(authProvider.notifier).refreshUser();
                                    nav.pop();
                                    messenger.showSnackBar(
                                      const SnackBar(
                                        content: Text('Profile updated successfully!'),
                                        backgroundColor: AppColors.secondary,
                                      ),
                                    );
                                  }
                                } catch (e) {
                                  setModalState(() => isSaving = false);
                                  final err = e is ApiException
                                      ? e.message
                                      : 'Failed to update profile.';
                                  messenger.showSnackBar(
                                    SnackBar(
                                      content: Text(err),
                                      backgroundColor: AppColors.danger,
                                    ),
                                  );
                                }
                              },
                        child: isSaving
                            ? const SizedBox(
                                height: 20,
                                width: 20,
                                child: CircularProgressIndicator(
                                  strokeWidth: 2,
                                  color: Colors.white,
                                ),
                              )
                            : const Text('Save Changes'),
                      ),
                      const SizedBox(height: 12),
                    ],
                  ),
                ),
              ),
            );
          },
        );
      },
    );
  }

  void _showSettingsDialog() {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      backgroundColor: Colors.white,
      builder: (context) {
        return Padding(
          padding: const EdgeInsets.all(24),
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
              const Text(
                'Settings & About',
                style: TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.bold,
                  color: AppColors.textPrimary,
                ),
              ),
              const SizedBox(height: 16),
              ListTile(
                contentPadding: EdgeInsets.zero,
                leading: const Icon(Icons.info_outline, color: AppColors.primary),
                title: const Text('App Version'),
                subtitle: const Text('OM Connect v1.0.0 (Build 1)'),
              ),
              const Divider(),
              ListTile(
                contentPadding: EdgeInsets.zero,
                leading: const Icon(Icons.shield_outlined, color: AppColors.primary),
                title: const Text('Privacy Policy & Terms'),
                trailing: const Icon(Icons.chevron_right),
                onTap: () => Navigator.pop(context),
              ),
              const Divider(),
              ListTile(
                contentPadding: EdgeInsets.zero,
                leading: const Icon(Icons.help_outline, color: AppColors.primary),
                title: const Text('Help & Support'),
                subtitle: const Text('support@om.lims.com.np'),
                trailing: const Icon(Icons.chevron_right),
                onTap: () => Navigator.pop(context),
              ),
              const SizedBox(height: 16),
            ],
          ),
        );
      },
    );
  }

  void _handleLogout() {
    showDialog(
      context: context,
      builder: (dialogContext) {
        return AlertDialog(
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
          ),
          title: const Text(
            'Confirm Logout',
            style: TextStyle(
              fontWeight: FontWeight.bold,
              color: AppColors.textPrimary,
            ),
          ),
          content: const Text(
            'Are you sure you want to log out of your OM Connect account?',
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(dialogContext),
              child: const Text('Cancel'),
            ),
            ElevatedButton(
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.danger,
                foregroundColor: Colors.white,
                minimumSize: const Size(90, 40),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
              ),
              onPressed: () async {
                Navigator.pop(dialogContext);
                await ref.read(authProvider.notifier).logout();
              },
              child: const Text('Logout'),
            ),
          ],
        );
      },
    );
  }

  String _formatRole(String role) {
    switch (role.toLowerCase()) {
      case 'shareholder':
        return 'Shareholder';
      case 'pharmacy':
        return 'Pharmacy Partner';
      case 'doctor':
        return 'Medical Doctor';
      case 'admin':
        return 'Administrator';
      default:
        return role.toUpperCase();
    }
  }

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authProvider);
    final user = authState.user;

    final name = user?.name ?? 'OM Connect User';
    final email = user?.email ?? 'No email available';
    final mobile = user?.mobile;
    final primaryRole = user?.primaryRole ?? 'User';
    final avatarUrl = user?.avatar;

    return Scaffold(
      backgroundColor: AppColors.surface,
      appBar: AppBar(
        title: const Text('Profile'),
        elevation: 0,
      ),
      body: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 20),
        child: Column(
          children: [
            // User Header Card
            Card(
              elevation: 1,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(16),
              ),
              color: Colors.white,
              margin: EdgeInsets.zero,
              child: Padding(
                padding: const EdgeInsets.all(20),
                child: Column(
                  children: [
                    // Avatar with camera icon
                    Center(
                      child: Stack(
                        children: [
                          CircleAvatar(
                            radius: 46,
                            backgroundColor: AppColors.primaryLight.withValues(alpha: 0.2),
                            backgroundImage: (avatarUrl != null && avatarUrl.isNotEmpty)
                                ? NetworkImage(_resolveAvatarUrl(avatarUrl))
                                : null,
                            child: (avatarUrl == null || avatarUrl.isEmpty)
                                ? Text(
                                    name.isNotEmpty ? name[0].toUpperCase() : 'U',
                                    style: const TextStyle(
                                      fontSize: 34,
                                      fontWeight: FontWeight.bold,
                                      color: AppColors.primary,
                                    ),
                                  )
                                : null,
                          ),
                          if (_isUploadingAvatar)
                            Positioned.fill(
                              child: Container(
                                decoration: const BoxDecoration(
                                  color: Colors.black45,
                                  shape: BoxShape.circle,
                                ),
                                child: const Center(
                                  child: SizedBox(
                                    width: 24,
                                    height: 24,
                                    child: CircularProgressIndicator(
                                      strokeWidth: 2.5,
                                      color: Colors.white,
                                    ),
                                  ),
                                ),
                              ),
                            ),
                          Positioned(
                            bottom: 0,
                            right: 0,
                            child: GestureDetector(
                              onTap: _isUploadingAvatar ? null : _pickAndUploadAvatar,
                              child: Container(
                                padding: const EdgeInsets.all(7),
                                decoration: BoxDecoration(
                                  color: AppColors.primary,
                                  shape: BoxShape.circle,
                                  border: Border.all(color: Colors.white, width: 2),
                                ),
                                child: const Icon(
                                  Icons.camera_alt_rounded,
                                  size: 16,
                                  color: Colors.white,
                                ),
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 14),

                    // User Name
                    Text(
                      name,
                      style: const TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                        color: AppColors.textPrimary,
                      ),
                    ),
                    const SizedBox(height: 4),

                    // Email
                    Text(
                      email,
                      style: const TextStyle(
                        fontSize: 14,
                        color: AppColors.textSecondary,
                      ),
                    ),
                    const SizedBox(height: 10),

                    // Role Badge
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                      decoration: BoxDecoration(
                        color: AppColors.primary.withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(16),
                      ),
                      child: Text(
                        _formatRole(primaryRole),
                        style: const TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w700,
                          color: AppColors.primary,
                          letterSpacing: 0.3,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),

            // Profile Information & Edit Section
            Card(
              elevation: 1,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(16),
              ),
              color: Colors.white,
              margin: EdgeInsets.zero,
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text(
                          'Personal Details',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                            color: AppColors.textPrimary,
                          ),
                        ),
                        TextButton.icon(
                          onPressed: () => _showEditProfileSheet(name, mobile),
                          icon: const Icon(Icons.edit_outlined, size: 16),
                          label: const Text('Edit'),
                          style: TextButton.styleFrom(
                            foregroundColor: AppColors.primary,
                          ),
                        ),
                      ],
                    ),
                    const Divider(height: 12),
                    ListTile(
                      contentPadding: EdgeInsets.zero,
                      leading: const Icon(Icons.badge_outlined, color: AppColors.primary),
                      title: const Text('Full Name', style: TextStyle(fontSize: 13, color: AppColors.textSecondary)),
                      subtitle: Text(
                        name,
                        style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w600, color: AppColors.textPrimary),
                      ),
                    ),
                    ListTile(
                      contentPadding: EdgeInsets.zero,
                      leading: const Icon(Icons.email_outlined, color: AppColors.primary),
                      title: const Text('Email Address', style: TextStyle(fontSize: 13, color: AppColors.textSecondary)),
                      subtitle: Text(
                        email,
                        style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w600, color: AppColors.textPrimary),
                      ),
                    ),
                    ListTile(
                      contentPadding: EdgeInsets.zero,
                      leading: const Icon(Icons.phone_outlined, color: AppColors.primary),
                      title: const Text('Mobile Number', style: TextStyle(fontSize: 13, color: AppColors.textSecondary)),
                      subtitle: Text(
                        (mobile != null && mobile.isNotEmpty) ? mobile : 'Not provided',
                        style: TextStyle(
                          fontSize: 15,
                          fontWeight: FontWeight.w600,
                          color: (mobile != null && mobile.isNotEmpty)
                              ? AppColors.textPrimary
                              : AppColors.textSecondary,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),

            // Navigation Menu Card
            Card(
              elevation: 1,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(16),
              ),
              color: Colors.white,
              margin: EdgeInsets.zero,
              child: Column(
                children: [
                  _buildMenuItem(
                    icon: Icons.receipt_long_rounded,
                    iconColor: AppColors.primary,
                    title: 'My Bills',
                    subtitle: 'View submitted pharmacy bills & receipts',
                    onTap: () {
                      try {
                        context.push('/bills');
                      } catch (_) {
                        Navigator.pushNamed(context, '/bills');
                      }
                    },
                  ),
                  const Divider(height: 1, indent: 56, endIndent: 16),
                  _buildMenuItem(
                    icon: Icons.stars_rounded,
                    iconColor: AppColors.accent,
                    title: 'Rewards',
                    subtitle: 'Check points wallet and redeem benefits',
                    onTap: () {
                      try {
                        context.push('/rewards');
                      } catch (_) {
                        Navigator.pushNamed(context, '/rewards');
                      }
                    },
                  ),
                  const Divider(height: 1, indent: 56, endIndent: 16),
                  _buildMenuItem(
                    icon: Icons.campaign_rounded,
                    iconColor: AppColors.secondary,
                    title: 'Notices',
                    subtitle: 'Announcements, AGM & financial reports',
                    onTap: () {
                      try {
                        context.push('/notices');
                      } catch (_) {
                        Navigator.pushNamed(context, '/notices');
                      }
                    },
                  ),
                  const Divider(height: 1, indent: 56, endIndent: 16),
                  _buildMenuItem(
                    icon: Icons.settings_outlined,
                    iconColor: AppColors.textSecondary,
                    title: 'Settings',
                    subtitle: 'App version, support & security',
                    onTap: _showSettingsDialog,
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),

            // Logout Button
            SizedBox(
              width: double.infinity,
              height: 52,
              child: ElevatedButton.icon(
                onPressed: _handleLogout,
                icon: const Icon(Icons.logout_rounded, size: 20),
                label: const Text(
                  'Log Out',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.danger,
                  foregroundColor: Colors.white,
                  elevation: 0,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(16),
                  ),
                ),
              ),
            ),
            const SizedBox(height: 24),
          ],
        ),
      ),
    );
  }

  Widget _buildMenuItem({
    required IconData icon,
    required Color iconColor,
    required String title,
    required String subtitle,
    required VoidCallback onTap,
  }) {
    return ListTile(
      onTap: onTap,
      leading: Container(
        padding: const EdgeInsets.all(8),
        decoration: BoxDecoration(
          color: iconColor.withValues(alpha: 0.1),
          borderRadius: BorderRadius.circular(10),
        ),
        child: Icon(icon, color: iconColor, size: 22),
      ),
      title: Text(
        title,
        style: const TextStyle(
          fontSize: 15,
          fontWeight: FontWeight.w600,
          color: AppColors.textPrimary,
        ),
      ),
      subtitle: Text(
        subtitle,
        style: const TextStyle(
          fontSize: 12,
          color: AppColors.textSecondary,
        ),
      ),
      trailing: const Icon(
        Icons.chevron_right_rounded,
        color: AppColors.textSecondary,
      ),
    );
  }
}
