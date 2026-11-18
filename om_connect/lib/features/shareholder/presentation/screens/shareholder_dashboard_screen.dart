import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:shimmer/shimmer.dart';
import '../../../../core/api/api_client.dart';
import '../../../../core/api/api_endpoints.dart';
import '../../../../core/constants/app_theme.dart';
import '../../../advertisements/presentation/widgets/role_advertisement_banner.dart';
import '../../../auth/domain/auth_provider.dart';

class ShareholderDashboardScreen extends ConsumerStatefulWidget {
  const ShareholderDashboardScreen({super.key});

  @override
  ConsumerState<ShareholderDashboardScreen> createState() =>
      _ShareholderDashboardScreenState();
}

class _ShareholderDashboardScreenState
    extends ConsumerState<ShareholderDashboardScreen> {
  bool _isLoading = true;
  String? _errorMessage;
  Map<String, dynamic>? _dashboardData;

  @override
  void initState() {
    super.initState();
    _fetchDashboard();
  }

  Future<void> _fetchDashboard() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final response = await ApiClient.instance.get(
        ApiEndpoints.shareholderDashboard,
      );

      if (response.statusCode == 200 && response.data != null) {
        if (mounted) {
          setState(() {
            _dashboardData = response.data is Map<String, dynamic>
                ? response.data as Map<String, dynamic>
                : null;
            _isLoading = false;
          });
        }
      } else {
        throw ApiException(message: 'Failed to load dashboard data.');
      }
    } catch (e) {
      final message = e is ApiException
          ? e.message
          : 'Unable to connect to the server. Please check your connection.';
      if (mounted) {
        setState(() {
          _isLoading = false;
          _errorMessage = message;
        });

        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(message),
            backgroundColor: AppColors.danger,
            action: SnackBarAction(
              label: 'Retry',
              textColor: Colors.white,
              onPressed: _fetchDashboard,
            ),
          ),
        );
      }
    }
  }

  void _navigateTo(String path) {
    try {
      context.push(path);
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Screen not available ($path)'),
          backgroundColor: AppColors.info,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authProvider);
    final user = authState.user;

    return Scaffold(
      backgroundColor: AppColors.surface,
      appBar: AppBar(
        title: const Text(
          'Dashboard',
          style: TextStyle(fontWeight: FontWeight.w700),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            tooltip: 'Refresh',
            onPressed: _fetchDashboard,
          ),
        ],
      ),
      body: RefreshIndicator(
        color: AppColors.primary,
        onRefresh: _fetchDashboard,
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 16.0),
          child: _isLoading
              ? _buildShimmerLoading()
              : Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    if (_errorMessage != null) ...[
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                        margin: const EdgeInsets.only(bottom: 12),
                        decoration: BoxDecoration(
                          color: AppColors.warning.withValues(alpha: 0.15),
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(color: AppColors.warning.withValues(alpha: 0.3)),
                        ),
                        child: Row(
                          children: [
                            const Icon(Icons.info_outline, color: AppColors.accent, size: 16),
                            const SizedBox(width: 8),
                            Expanded(
                              child: Text(
                                _errorMessage!,
                                style: const TextStyle(fontSize: 12, color: AppColors.textPrimary),
                              ),
                            ),
                            TextButton(
                              onPressed: _fetchDashboard,
                              child: const Text('Retry', style: TextStyle(fontSize: 12)),
                            ),
                          ],
                        ),
                      ),
                    ],
                    _buildGreetingHeader(user?.name),
                    const SizedBox(height: 16),
                    _buildKpiCardsRow(),
                    const SizedBox(height: 16),
                    _buildRewardSummaryCard(),
                    const SizedBox(height: 16),
                    const RoleAdvertisementBanner(),
                    const SizedBox(height: 16),
                    _buildBoidStatusCard(user?.shareholderAccount?.isVerified),
                    const SizedBox(height: 16),
                    _buildQuickActionsSection(),
                    const SizedBox(height: 24),
                  ],
                ),
        ),
      ),
    );
  }

  Widget _buildGreetingHeader(String? userName) {
    final displayName = userName != null && userName.isNotEmpty
        ? userName
        : 'Shareholder';

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [AppColors.primary, Color(0xFF2C5384)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: AppColors.primary.withValues(alpha: 0.25),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: const Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      Icons.workspace_premium,
                      color: AppColors.accent,
                      size: 16,
                    ),
                    SizedBox(width: 4),
                    Text(
                      'Shareholder Zone',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ],
                ),
              ),
              const Icon(
                Icons.trending_up,
                color: Colors.white70,
                size: 24,
              ),
            ],
          ),
          const SizedBox(height: 12),
          const Text(
            'Welcome back,',
            style: TextStyle(
              color: Colors.white70,
              fontSize: 14,
            ),
          ),
          const SizedBox(height: 2),
          Text(
            displayName,
            style: const TextStyle(
              color: Colors.white,
              fontSize: 22,
              fontWeight: FontWeight.bold,
              letterSpacing: -0.3,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildKpiCardsRow() {
    final totalBills = _dashboardData?['total_bills'] ?? 0;
    final approvedBills = _dashboardData?['approved_bills'] ?? 0;
    final pendingBills = _dashboardData?['pending_bills'] ?? 0;

    return Row(
      children: [
        Expanded(
          child: _buildKpiCard(
            title: 'Total Bills',
            value: totalBills.toString(),
            icon: Icons.receipt_long,
            color: AppColors.primary,
          ),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: _buildKpiCard(
            title: 'Approved',
            value: approvedBills.toString(),
            icon: Icons.check_circle_rounded,
            color: AppColors.secondary,
          ),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: _buildKpiCard(
            title: 'Pending',
            value: pendingBills.toString(),
            icon: Icons.pending_actions_rounded,
            color: AppColors.accent,
          ),
        ),
      ],
    );
  }

  Widget _buildKpiCard({
    required String title,
    required String value,
    required IconData icon,
    required Color color,
  }) {
    return Card(
      elevation: 1,
      margin: EdgeInsets.zero,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
        side: BorderSide(color: AppColors.border.withValues(alpha: 0.6)),
      ),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 14),
        child: Column(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: color.withValues(alpha: 0.12),
                shape: BoxShape.circle,
              ),
              child: Icon(icon, color: color, size: 20),
            ),
            const SizedBox(height: 8),
            Text(
              value,
              style: TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.bold,
                color: color,
              ),
            ),
            const SizedBox(height: 2),
            Text(
              title,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: const TextStyle(
                fontSize: 12,
                color: AppColors.textSecondary,
                fontWeight: FontWeight.w500,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildRewardSummaryCard() {
    final points = _dashboardData?['total_points']?.toString() ?? '0.00';
    final bonus = _dashboardData?['total_bonus']?.toString() ?? '0.00';
    final royalty = _dashboardData?['total_royalty']?.toString() ?? '0.00';

    return Card(
      elevation: 2,
      margin: EdgeInsets.zero,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
        side: BorderSide(color: AppColors.accent.withValues(alpha: 0.3)),
      ),
      child: Container(
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(16),
          gradient: LinearGradient(
            colors: [
              Colors.white,
              AppColors.accent.withValues(alpha: 0.04),
            ],
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
          ),
        ),
        padding: const EdgeInsets.all(18),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Row(
                  children: [
                    Icon(
                      Icons.stars_rounded,
                      color: AppColors.accent,
                      size: 26,
                    ),
                    SizedBox(width: 8),
                    Text(
                      'Reward Summary',
                      style: TextStyle(
                        fontSize: 17,
                        fontWeight: FontWeight.bold,
                        color: AppColors.textPrimary,
                      ),
                    ),
                  ],
                ),
                InkWell(
                  onTap: () => _navigateTo('/rewards'),
                  borderRadius: BorderRadius.circular(8),
                  child: const Padding(
                    padding: EdgeInsets.symmetric(horizontal: 6, vertical: 4),
                    child: Row(
                      children: [
                        Text(
                          'Wallet',
                          style: TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.w600,
                            color: AppColors.primary,
                          ),
                        ),
                        Icon(
                          Icons.arrow_forward_ios,
                          size: 11,
                          color: AppColors.primary,
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
            const Padding(
              padding: EdgeInsets.symmetric(vertical: 12),
              child: Divider(color: AppColors.divider, height: 1),
            ),
            Row(
              children: [
                Expanded(
                  child: _buildRewardMetric(
                    label: 'Reward Points',
                    value: points,
                    color: AppColors.primary,
                  ),
                ),
                Container(
                  width: 1,
                  height: 38,
                  color: AppColors.divider,
                ),
                Expanded(
                  child: _buildRewardMetric(
                    label: 'Bonus Pool',
                    value: bonus,
                    color: AppColors.secondary,
                  ),
                ),
                Container(
                  width: 1,
                  height: 38,
                  color: AppColors.divider,
                ),
                Expanded(
                  child: _buildRewardMetric(
                    label: 'Royalty',
                    value: royalty,
                    color: AppColors.accent,
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildRewardMetric({
    required String label,
    required String value,
    required Color color,
  }) {
    return Column(
      children: [
        Text(
          value,
          style: TextStyle(
            fontSize: 17,
            fontWeight: FontWeight.bold,
            color: color,
          ),
        ),
        const SizedBox(height: 2),
        Text(
          label,
          textAlign: TextAlign.center,
          style: const TextStyle(
            fontSize: 11,
            color: AppColors.textSecondary,
            fontWeight: FontWeight.w500,
          ),
        ),
      ],
    );
  }

  Widget _buildBoidStatusCard(bool? accountVerified) {
    final isVerified = (_dashboardData?['boid_verified'] == true) ||
        (accountVerified == true);

    if (isVerified) {
      return Card(
        elevation: 1,
        margin: EdgeInsets.zero,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(16),
          side: BorderSide(
            color: AppColors.secondary.withValues(alpha: 0.3),
          ),
        ),
        color: AppColors.secondary.withValues(alpha: 0.06),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(10),
                decoration: const BoxDecoration(
                  color: AppColors.secondary,
                  shape: BoxShape.circle,
                ),
                child: const Icon(
                  Icons.check,
                  color: Colors.white,
                  size: 20,
                ),
              ),
              const SizedBox(width: 14),
              const Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'BOID Verified',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                        color: AppColors.secondary,
                      ),
                    ),
                    SizedBox(height: 2),
                    Text(
                      'Your Demat account is linked. Dividends and shareholder benefits are active.',
                      style: TextStyle(
                        fontSize: 12,
                        color: AppColors.textSecondary,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      );
    }

    return Card(
      elevation: 1,
      margin: EdgeInsets.zero,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
        side: BorderSide(
          color: AppColors.accent.withValues(alpha: 0.4),
        ),
      ),
      color: AppColors.accent.withValues(alpha: 0.07),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: AppColors.accent.withValues(alpha: 0.2),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(
                    Icons.warning_amber_rounded,
                    color: AppColors.accent,
                    size: 22,
                  ),
                ),
                const SizedBox(width: 12),
                const Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'BOID Not Verified',
                        style: TextStyle(
                          fontSize: 15,
                          fontWeight: FontWeight.bold,
                          color: AppColors.textPrimary,
                        ),
                      ),
                      Text(
                        'Link your 16-digit Demat BOID to receive rewards.',
                        style: TextStyle(
                          fontSize: 12,
                          color: AppColors.textSecondary,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            SizedBox(
              width: double.infinity,
              height: 40,
              child: ElevatedButton.icon(
                onPressed: () => _navigateTo('/shareholder/verify-boid'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.accent,
                  foregroundColor: Colors.white,
                  elevation: 0,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(10),
                  ),
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                ),
                icon: const Icon(Icons.verified_outlined, size: 18),
                label: const Text(
                  'Verify BOID Now',
                  style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildQuickActionsSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Quick Actions',
          style: TextStyle(
            fontSize: 17,
            fontWeight: FontWeight.bold,
            color: AppColors.textPrimary,
          ),
        ),
        const SizedBox(height: 12),
        Row(
          children: [
            Expanded(
              child: _buildQuickActionButton(
                label: 'Upload Bill',
                icon: Icons.cloud_upload_outlined,
                color: AppColors.primary,
                onTap: () => _navigateTo('/bills/upload'),
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: _buildQuickActionButton(
                label: 'View Rewards',
                icon: Icons.account_balance_wallet_outlined,
                color: AppColors.secondary,
                onTap: () => _navigateTo('/rewards'),
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: _buildQuickActionButton(
                label: 'Verify BOID',
                icon: Icons.badge_outlined,
                color: AppColors.accent,
                onTap: () => _navigateTo('/shareholder/verify-boid'),
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildQuickActionButton({
    required String label,
    required IconData icon,
    required Color color,
    required VoidCallback onTap,
  }) {
    return Card(
      elevation: 1,
      margin: EdgeInsets.zero,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
        side: BorderSide(color: AppColors.border.withValues(alpha: 0.6)),
      ),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(16),
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 8),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: color.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(icon, color: color, size: 24),
              ),
              const SizedBox(height: 10),
              Text(
                label,
                textAlign: TextAlign.center,
                maxLines: 2,
                style: const TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w600,
                  color: AppColors.textPrimary,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildShimmerLoading() {
    return Shimmer.fromColors(
      baseColor: Colors.grey.shade300,
      highlightColor: Colors.grey.shade100,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            height: 120,
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
            ),
          ),
          const SizedBox(height: 16),
          Row(
            children: List.generate(
              3,
              (index) => Expanded(
                child: Container(
                  height: 95,
                  margin: EdgeInsets.only(
                    left: index == 0 ? 0 : 5,
                    right: index == 2 ? 0 : 5,
                  ),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(16),
                  ),
                ),
              ),
            ),
          ),
          const SizedBox(height: 16),
          Container(
            height: 110,
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
            ),
          ),
          const SizedBox(height: 16),
          Container(
            height: 80,
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
            ),
          ),
          const SizedBox(height: 24),
          Container(
            width: 120,
            height: 20,
            color: Colors.white,
          ),
          const SizedBox(height: 12),
          Row(
            children: List.generate(
              3,
              (index) => Expanded(
                child: Container(
                  height: 95,
                  margin: EdgeInsets.only(
                    left: index == 0 ? 0 : 5,
                    right: index == 2 ? 0 : 5,
                  ),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(16),
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
