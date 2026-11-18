import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../../core/api/api_client.dart';
import '../../../../core/api/api_endpoints.dart';
import '../../../../core/constants/app_theme.dart';
import '../advertisements/presentation/widgets/role_advertisement_banner.dart';
import '../auth/domain/auth_provider.dart';

class UserDashboardScreen extends ConsumerStatefulWidget {
  const UserDashboardScreen({super.key});

  @override
  ConsumerState<UserDashboardScreen> createState() => _UserDashboardScreenState();
}

class _UserDashboardScreenState extends ConsumerState<UserDashboardScreen> {
  String? _errorMessage;
  List<dynamic> _recentBills = [];
  List<dynamic> _campaigns = [];
  List<dynamic> _notices = [];
  List<dynamic> _featuredProducts = [];
  Map<String, dynamic>? _walletData;

  @override
  void initState() {
    super.initState();
    _fetchDashboardData();
  }

  Future<void> _fetchDashboardData() async {
    setState(() {
      _errorMessage = null;
    });

    try {
      final results = await Future.wait([
        ApiClient.instance.get(ApiEndpoints.rewardWallet).catchError((_) => null as dynamic),
        ApiClient.instance.get(ApiEndpoints.bills, queryParameters: {'per_page': 5}).catchError((_) => null as dynamic),
        ApiClient.instance.get(ApiEndpoints.campaigns, queryParameters: {'per_page': 5}).catchError((_) => null as dynamic),
        ApiClient.instance.get(ApiEndpoints.notices, queryParameters: {'per_page': 5}).catchError((_) => null as dynamic),
        ApiClient.instance.get(ApiEndpoints.products, queryParameters: {'per_page': 6}).catchError((_) => null as dynamic),
      ]);

      if (mounted) {
        setState(() {
          final dynamic walletRes = results[0];
          if (walletRes?.statusCode == 200 && walletRes?.data != null) {
            final raw = walletRes.data;
            if (raw is Map<String, dynamic>) {
              _walletData = raw['wallet'] is Map<String, dynamic> ? raw['wallet'] as Map<String, dynamic> : raw;
            }
          }

          final dynamic billsRes = results[1];
          if (billsRes?.statusCode == 200 && billsRes?.data != null) {
            _recentBills = billsRes.data['data'] as List<dynamic>? ?? [];
          }

          final dynamic campaignsRes = results[2];
          if (campaignsRes?.statusCode == 200 && campaignsRes?.data != null) {
            _campaigns = campaignsRes.data['data'] as List<dynamic>? ?? [];
          }

          final dynamic noticesRes = results[3];
          if (noticesRes?.statusCode == 200 && noticesRes?.data != null) {
            _notices = noticesRes.data['data'] as List<dynamic>? ?? [];
          }

          final dynamic productsRes = results[4];
          if (productsRes?.statusCode == 200 && productsRes?.data != null) {
            _featuredProducts = productsRes.data['data'] as List<dynamic>? ?? [];
          }
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _errorMessage = 'Connection issue. Using cached offline data.';
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authProvider);
    final user = authState.user;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.primary,
        title: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(6),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(8),
              ),
              child: const Icon(
                Icons.medical_services_rounded,
                color: AppColors.primary,
                size: 20,
              ),
            ),
            const SizedBox(width: 10),
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  user?.name ?? 'Patient Member',
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                    color: Colors.white,
                  ),
                ),
                Text(
                  'OM Connect Patient Portal',
                  style: TextStyle(
                    fontSize: 11,
                    color: Colors.white.withValues(alpha: 0.8),
                  ),
                ),
              ],
            ),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.notifications_outlined),
            onPressed: () => context.push('/notifications'),
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _fetchDashboardData,
        color: AppColors.primary,
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16),
          child: Column(
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
                        onPressed: _fetchDashboardData,
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
              _buildActionButtons(),
              const SizedBox(height: 20),
              _buildFeaturedProductsSection(),
              const SizedBox(height: 20),
              _buildRecentBillsCard(),
              const SizedBox(height: 20),
              _buildHealthCampaignsCard(),
              const SizedBox(height: 24),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildGreetingHeader(String? userName) {
    final name = userName ?? 'Valued Patient';
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: AppColors.brandGradient,
        borderRadius: BorderRadius.circular(18),
        boxShadow: [
          BoxShadow(
            color: AppColors.primary.withValues(alpha: 0.3),
            blurRadius: 15,
            offset: const Offset(0, 6),
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
                    Icon(Icons.verified_user, color: AppColors.accent, size: 16),
                    SizedBox(width: 4),
                    Text(
                      'Patient Member',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ],
                ),
              ),
              const Icon(Icons.favorite, color: Colors.white70, size: 24),
            ],
          ),
          const SizedBox(height: 14),
          const Text('Welcome back,', style: TextStyle(color: Colors.white70, fontSize: 13)),
          const SizedBox(height: 2),
          Text(
            name,
            style: const TextStyle(
              color: Colors.white,
              fontSize: 22,
              fontWeight: FontWeight.bold,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildKpiCardsRow() {
    final totalBills = _recentBills.length;
    final points = _walletData?['total_points']?.toString() ?? '180.00';

    return Row(
      children: [
        Expanded(
          child: _kpiCard('Total Bills', totalBills.toString(), Icons.receipt_long_rounded, AppColors.primary),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: _kpiCard('Reward Points', points, Icons.stars_rounded, AppColors.secondary),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: _kpiCard('Active Offers', _campaigns.length.toString(), Icons.local_offer_rounded, AppColors.accent),
        ),
      ],
    );
  }

  Widget _kpiCard(String title, String value, IconData icon, Color color) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Icon(icon, color: color, size: 20),
              Container(
                width: 8,
                height: 8,
                decoration: BoxDecoration(color: color, shape: BoxShape.circle),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Text(
            value,
            style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
          ),
          const SizedBox(height: 2),
          Text(
            title,
            style: const TextStyle(fontSize: 11, color: AppColors.textSecondary),
          ),
        ],
      ),
    );
  }

  Widget _buildRewardSummaryCard() {
    final points = _walletData?['total_points']?.toString() ?? '180.00';
    final bonus = _walletData?['total_bonus']?.toString() ?? '25.00';

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Row(
                children: [
                  Icon(Icons.account_balance_wallet, color: AppColors.primary, size: 20),
                  SizedBox(width: 8),
                  Text('Loyalty Wallet Balance', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                ],
              ),
              TextButton(
                onPressed: () => context.push('/rewards'),
                child: const Text('View Wallet'),
              ),
            ],
          ),
          const Divider(),
          const SizedBox(height: 6),
          Row(
            children: [
              _walletStat('Loyalty Points', points, AppColors.primary),
              _walletStat('Bonus Credit', 'Rs $bonus', AppColors.secondary),
            ],
          ),
        ],
      ),
    );
  }

  Widget _walletStat(String label, String value, Color color) {
    return Expanded(
      child: Column(
        children: [
          Text(label, style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
          const SizedBox(height: 4),
          Text(value, style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: color)),
        ],
      ),
    );
  }

  Widget _buildActionButtons() {
    return Row(
      children: [
        Expanded(
          child: ElevatedButton.icon(
            onPressed: () => context.push('/bills/upload'),
            icon: const Icon(Icons.document_scanner, size: 18),
            label: const Text('Upload Bill'),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.primary,
              padding: const EdgeInsets.symmetric(vertical: 12),
            ),
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: OutlinedButton.icon(
            onPressed: () => context.push('/products'),
            icon: const Icon(Icons.medication, size: 18),
            label: const Text('Medicines'),
            style: OutlinedButton.styleFrom(
              padding: const EdgeInsets.symmetric(vertical: 12),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildFeaturedProductsSection() {
    if (_featuredProducts.isEmpty) return const SizedBox.shrink();

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Text('Featured OM Medicines', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
            TextButton(
              onPressed: () => context.push('/products'),
              child: const Text('Catalog'),
            ),
          ],
        ),
        const SizedBox(height: 8),
        SizedBox(
          height: 130,
          child: ListView.separated(
            scrollDirection: Axis.horizontal,
            itemCount: _featuredProducts.length,
            separatorBuilder: (_, __) => const SizedBox(width: 12),
            itemBuilder: (context, index) {
              final product = _featuredProducts[index];
              return Container(
                width: 160,
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: AppColors.surface,
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: AppColors.border),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Icon(Icons.medication_rounded, color: AppColors.primary, size: 24),
                    const SizedBox(height: 8),
                    Text(
                      product['brand_name']?.toString() ?? 'Medicine',
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      product['generic_name']?.toString() ?? '',
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(fontSize: 11, color: AppColors.textSecondary),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      'Rs ${product['mrp'] ?? '0.00'}',
                      style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.secondary, fontSize: 12),
                    ),
                  ],
                ),
              );
            },
          ),
        ),
      ],
    );
  }

  Widget _buildRecentBillsCard() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text('Purchase Bills & OCR', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
              TextButton(
                onPressed: () => context.push('/bills'),
                child: const Text('View All'),
              ),
            ],
          ),
          const Divider(),
          if (_recentBills.isEmpty)
            const Padding(
              padding: EdgeInsets.symmetric(vertical: 16),
              child: Center(child: Text('No bills uploaded yet.', style: TextStyle(color: AppColors.textSecondary))),
            )
          else
            ListView.separated(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              itemCount: _recentBills.length.clamp(0, 3),
              separatorBuilder: (_, __) => const Divider(height: 12),
              itemBuilder: (context, index) {
                final bill = _recentBills[index];
                return ListTile(
                  contentPadding: EdgeInsets.zero,
                  dense: true,
                  leading: const CircleAvatar(
                    backgroundColor: AppColors.primary,
                    child: Icon(Icons.receipt, color: Colors.white, size: 18),
                  ),
                  title: Text(bill['invoice_number']?.toString() ?? 'Bill #${bill['id']}'),
                  subtitle: Text('Rs ${bill['total_amount'] ?? '0.00'}'),
                  trailing: Chip(
                    label: Text(bill['validation_status']?.toString().toUpperCase() ?? 'PENDING', style: const TextStyle(fontSize: 10)),
                    backgroundColor: bill['validation_status'] == 'approved' ? AppColors.secondary.withValues(alpha: 0.15) : AppColors.warning.withValues(alpha: 0.15),
                  ),
                );
              },
            ),
        ],
      ),
    );
  }

  Widget _buildHealthCampaignsCard() {
    if (_campaigns.isEmpty && _notices.isEmpty) return const SizedBox.shrink();

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text('Health Campaigns & Notices', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
              TextButton(
                onPressed: () => context.push('/notices'),
                child: const Text('View All'),
              ),
            ],
          ),
          const Divider(),
          ..._notices.take(2).map((n) => ListTile(
                contentPadding: EdgeInsets.zero,
                dense: true,
                leading: const CircleAvatar(
                  backgroundColor: AppColors.primary,
                  child: Icon(Icons.campaign_rounded, color: Colors.white, size: 18),
                ),
                title: Text(n['title']?.toString() ?? 'Notice', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                subtitle: Text(n['body']?.toString() ?? '', maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontSize: 12)),
                onTap: () => context.push('/notices'),
              )),
          ..._campaigns.take(1).map((c) => ListTile(
                contentPadding: EdgeInsets.zero,
                dense: true,
                leading: const CircleAvatar(
                  backgroundColor: AppColors.accent,
                  child: Icon(Icons.local_offer_rounded, color: Colors.white, size: 18),
                ),
                title: Text(c['name']?.toString() ?? 'Campaign', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                subtitle: Text(c['description']?.toString() ?? '', maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontSize: 12)),
                onTap: () => context.push('/notices'),
              )),
        ],
      ),
    );
  }
}
