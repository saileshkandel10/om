import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:intl/intl.dart';
import '../../../../core/api/api_client.dart';
import '../../../../core/api/api_endpoints.dart';
import '../../../../core/constants/app_theme.dart';

class RewardWalletScreen extends StatefulWidget {
  const RewardWalletScreen({super.key});

  @override
  State<RewardWalletScreen> createState() => _RewardWalletScreenState();
}

class _RewardWalletScreenState extends State<RewardWalletScreen> {
  bool _isLoading = true;
  String? _errorMessage;

  Map<String, dynamic>? _wallet;
  List<Map<String, dynamic>> _transactions = [];
  List<Map<String, dynamic>> _redemptions = [];
  int _selectedTab = 0; // 0 = Lucky Draw Tickets, 1 = Points History

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      await Future.wait([
        _fetchWallet(),
        _fetchTransactions(),
        _fetchRedemptions(),
      ]);
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _isLoading = false;
          _errorMessage = e is ApiException
              ? e.message
              : 'Failed to load wallet data. Please try again.';
        });
      }
    }
  }

  Future<void> _fetchWallet() async {
    final response = await ApiClient.instance.get(ApiEndpoints.rewardWallet);
    if (response.statusCode == 200 && response.data != null) {
      final data = response.data;
      if (data is Map<String, dynamic>) {
        if (data.containsKey('wallet') && data['wallet'] is Map<String, dynamic>) {
          _wallet = data['wallet'] as Map<String, dynamic>;
        } else if (data.containsKey('data') && data['data'] is Map<String, dynamic>) {
          _wallet = data['data'] as Map<String, dynamic>;
        } else {
          _wallet = data;
        }
      }
    } else {
      throw ApiException(message: 'Failed to fetch wallet balance.');
    }
  }

  Future<void> _fetchTransactions() async {
    final response = await ApiClient.instance.get(ApiEndpoints.rewardTransactions);
    if (response.statusCode == 200 && response.data != null) {
      final data = response.data;
      List<dynamic> list = [];
      if (data is Map<String, dynamic> && data['data'] is List) {
        list = data['data'] as List<dynamic>;
      } else if (data is List) {
        list = data;
      }
      _transactions = list
          .whereType<Map<String, dynamic>>()
          .toList();
    } else {
      throw ApiException(message: 'Failed to fetch transactions.');
    }
  }

  Future<void> _fetchRedemptions() async {
    try {
      final response = await ApiClient.instance.get(ApiEndpoints.rewardRedemptions);
      if (response.statusCode == 200 && response.data != null) {
        final data = response.data;
        List<dynamic> list = [];
        if (data is Map<String, dynamic> && data['data'] is List) {
          list = data['data'] as List<dynamic>;
        } else if (data is List) {
          list = data;
        }
        _redemptions = list
            .whereType<Map<String, dynamic>>()
            .toList();
      }
    } catch (_) {}
  }

  void _showRedemptionSuccessDialog(String code, double points, String type) {
    showDialog(
      context: context,
      builder: (ctx) {
        return AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 64,
                height: 64,
                decoration: BoxDecoration(
                  color: Colors.purple.shade50,
                  shape: BoxShape.circle,
                ),
                child: const Icon(Icons.celebration_rounded, color: Colors.purple, size: 36),
              ),
              const SizedBox(height: 16),
              const Text(
                'Redemption Successful!',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 8),
              Text(
                'You redeemed ${points.toStringAsFixed(0)} points for ${type.toUpperCase()}.',
                textAlign: TextAlign.center,
                style: const TextStyle(color: AppColors.textSecondary, fontSize: 13),
              ),
              const SizedBox(height: 16),
              Container(
                width: double.infinity,
                padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 16),
                decoration: BoxDecoration(
                  color: Colors.purple.shade50,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: Colors.purple.shade200),
                ),
                child: Column(
                  children: [
                    const Text(
                      'YOUR LUCKY DRAW & REDEMPTION CODE',
                      style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Colors.purple, letterSpacing: 0.5),
                    ),
                    const SizedBox(height: 8),
                    SelectableText(
                      code,
                      style: const TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.w900,
                        fontFamily: 'monospace',
                        color: Colors.purple,
                      ),
                    ),
                    const SizedBox(height: 6),
                    const Text(
                      'Keep this code safe for upcoming Lucky Draws!',
                      style: TextStyle(fontSize: 11, color: AppColors.textSecondary),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 20),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.purple,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  onPressed: () => Navigator.pop(ctx),
                  child: const Text('Done'),
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  void _showRedeemDialog() {
    final totalPoints = double.tryParse(_wallet?['total_points']?.toString() ?? '0') ?? 0.0;

    final formKey = GlobalKey<FormState>();
    final pointsController = TextEditingController();
    String selectedType = 'cash';
    bool isSubmitting = false;

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
                      Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.all(8),
                            decoration: BoxDecoration(
                              color: AppColors.secondary.withValues(alpha: 0.1),
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: const Icon(
                              Icons.redeem_rounded,
                              color: AppColors.secondary,
                            ),
                          ),
                          const SizedBox(width: 12),
                          const Text(
                            'Redeem Points',
                            style: TextStyle(
                              fontSize: 20,
                              fontWeight: FontWeight.bold,
                              color: AppColors.textPrimary,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      Text(
                        'Available Balance: ${totalPoints.toStringAsFixed(0)} Points',
                        style: const TextStyle(
                          fontSize: 14,
                          color: AppColors.textSecondary,
                        ),
                      ),
                      const SizedBox(height: 20),
                      TextFormField(
                        controller: pointsController,
                        keyboardType: TextInputType.number,
                        decoration: const InputDecoration(
                          labelText: 'Points to Redeem',
                          hintText: 'Enter amount (e.g. 100)',
                          prefixIcon: Icon(Icons.stars_rounded),
                        ),
                        validator: (val) {
                          if (val == null || val.trim().isEmpty) {
                            return 'Please enter points to redeem';
                          }
                          final parsed = double.tryParse(val.trim());
                          if (parsed == null || parsed <= 0) {
                            return 'Enter a valid positive number';
                          }
                          if (parsed > totalPoints) {
                            return 'Amount exceeds current points balance';
                          }
                          return null;
                        },
                      ),
                      const SizedBox(height: 16),
                      const Text(
                        'Redemption Method',
                        style: TextStyle(
                          fontWeight: FontWeight.w600,
                          fontSize: 14,
                          color: AppColors.textPrimary,
                        ),
                      ),
                      const SizedBox(height: 8),
                      Row(
                        children: [
                          Expanded(
                            child: ChoiceChip(
                              label: const Center(child: Text('Cash')),
                              selected: selectedType == 'cash',
                              selectedColor: AppColors.primary,
                              labelStyle: TextStyle(
                                color: selectedType == 'cash'
                                    ? Colors.white
                                    : AppColors.textPrimary,
                                fontWeight: FontWeight.w600,
                              ),
                              onSelected: (val) {
                                if (val) setModalState(() => selectedType = 'cash');
                              },
                            ),
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: ChoiceChip(
                              label: const Center(child: Text('Discount')),
                              selected: selectedType == 'discount',
                              selectedColor: AppColors.primary,
                              labelStyle: TextStyle(
                                color: selectedType == 'discount'
                                    ? Colors.white
                                    : AppColors.textPrimary,
                                fontWeight: FontWeight.w600,
                              ),
                              onSelected: (val) {
                                if (val) setModalState(() => selectedType = 'discount');
                              },
                            ),
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: ChoiceChip(
                              label: const Center(child: Text('Gift')),
                              selected: selectedType == 'gift',
                              selectedColor: AppColors.primary,
                              labelStyle: TextStyle(
                                color: selectedType == 'gift'
                                    ? Colors.white
                                    : AppColors.textPrimary,
                                fontWeight: FontWeight.w600,
                              ),
                              onSelected: (val) {
                                if (val) setModalState(() => selectedType = 'gift');
                              },
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 24),
                      ElevatedButton(
                        onPressed: isSubmitting
                            ? null
                            : () async {
                                if (!formKey.currentState!.validate()) return;
                                setModalState(() => isSubmitting = true);
                                final points = double.parse(pointsController.text.trim());

                                final messenger = ScaffoldMessenger.of(context);
                                final nav = Navigator.of(bottomSheetContext);

                                try {
                                  final response = await ApiClient.instance.post(
                                    ApiEndpoints.rewardRedeem,
                                    data: {
                                      'points_used': points,
                                      'redemption_type': selectedType,
                                    },
                                  );

                                  if (mounted) {
                                    nav.pop();
                                    final rdmCode = response.data?['redemption_code'] ?? response.data?['lucky_draw_code'] ?? response.data?['redemption']?['redemption_code'];
                                    _loadData();
                                    if (rdmCode != null) {
                                      _showRedemptionSuccessDialog(rdmCode.toString(), points, selectedType);
                                    } else {
                                      final msg = response.data?['message'] ??
                                          'Redemption request submitted successfully!';
                                      messenger.showSnackBar(
                                        SnackBar(
                                          content: Text(msg),
                                          backgroundColor: AppColors.secondary,
                                        ),
                                      );
                                    }
                                  }
                                } catch (e) {
                                  setModalState(() => isSubmitting = false);
                                  final err = e is ApiException
                                      ? e.message
                                      : 'Failed to process redemption.';
                                  messenger.showSnackBar(
                                    SnackBar(
                                      content: Text(err),
                                      backgroundColor: AppColors.danger,
                                    ),
                                  );
                                }
                              },
                        child: isSubmitting
                            ? const SizedBox(
                                height: 20,
                                width: 20,
                                child: CircularProgressIndicator(
                                  strokeWidth: 2,
                                  color: Colors.white,
                                ),
                              )
                            : const Text('Confirm Redemption'),
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

  String _formatDate(String? raw) {
    if (raw == null || raw.isEmpty) return 'Recent';
    try {
      final dt = DateTime.parse(raw).toLocal();
      return DateFormat('MMM dd, yyyy • hh:mm a').format(dt);
    } catch (_) {
      return raw;
    }
  }

  Color _getTransactionBadgeColor(String type) {
    switch (type.toLowerCase()) {
      case 'bill_scan':
      case 'purchase':
      case 'earn':
        return AppColors.secondary;
      case 'bonus':
        return AppColors.accent;
      case 'royalty':
        return AppColors.primaryLight;
      case 'redemption':
      case 'redeem':
        return AppColors.danger;
      default:
        return AppColors.info;
    }
  }

  String _formatTransactionType(String type) {
    switch (type.toLowerCase()) {
      case 'bill_scan':
        return 'Bill Scan';
      case 'purchase':
        return 'Purchase Earn';
      case 'bonus':
        return 'Bonus';
      case 'royalty':
        return 'Royalty';
      case 'redemption':
        return 'Redemption';
      default:
        return type.replaceAll('_', ' ').toUpperCase();
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.surface,
      appBar: AppBar(
        title: const Text('My Rewards'),
        elevation: 0,
      ),
      body: RefreshIndicator(
        onRefresh: _loadData,
        color: AppColors.primary,
        child: _isLoading
            ? const Center(child: CircularProgressIndicator())
            : _errorMessage != null && _wallet == null
                ? _buildErrorView()
                : _buildContent(),
      ),
    );
  }

  Widget _buildErrorView() {
    return ListView(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.all(24),
      children: [
        const SizedBox(height: 80),
        Icon(Icons.error_outline_rounded, size: 64, color: AppColors.danger),
        const SizedBox(height: 16),
        Text(
          _errorMessage ?? 'Something went wrong',
          textAlign: TextAlign.center,
          style: const TextStyle(
            fontSize: 16,
            color: AppColors.textPrimary,
            fontWeight: FontWeight.w600,
          ),
        ),
        const SizedBox(height: 24),
        Center(
          child: ElevatedButton.icon(
            onPressed: _loadData,
            icon: const Icon(Icons.refresh_rounded),
            label: const Text('Try Again'),
            style: ElevatedButton.styleFrom(
              minimumSize: const Size(160, 48),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildContent() {
    final totalPoints = _wallet?['total_points']?.toString() ?? '0.00';
    final totalBonus = _wallet?['total_bonus']?.toString() ?? '0.00';
    final totalRoyalty = _wallet?['total_royalty']?.toString() ?? '0.00';
    final totalValue = _wallet?['total_value']?.toString() ?? totalPoints;

    return CustomScrollView(
      physics: const AlwaysScrollableScrollPhysics(),
      slivers: [
        // Balance Card Section
        SliverToBoxAdapter(
          child: Padding(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
            child: _buildBalanceCard(
              totalPoints: totalPoints,
              totalBonus: totalBonus,
              totalRoyalty: totalRoyalty,
              totalValue: totalValue,
            ),
          ),
        ),

        // Tabs Section: Lucky Draw Tickets vs Transaction History
        SliverToBoxAdapter(
          child: Padding(
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
            child: Container(
              decoration: BoxDecoration(
                color: Colors.grey.shade100,
                borderRadius: BorderRadius.circular(14),
              ),
              padding: const EdgeInsets.all(4),
              child: Row(
                children: [
                  Expanded(
                    child: GestureDetector(
                      onTap: () => setState(() => _selectedTab = 0),
                      child: Container(
                        padding: const EdgeInsets.symmetric(vertical: 10),
                        decoration: BoxDecoration(
                          color: _selectedTab == 0 ? Colors.white : Colors.transparent,
                          borderRadius: BorderRadius.circular(10),
                          boxShadow: _selectedTab == 0
                              ? [BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 4, offset: const Offset(0, 2))]
                              : null,
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(
                              Icons.confirmation_number_rounded,
                              size: 16,
                              color: _selectedTab == 0 ? Colors.purple : AppColors.textSecondary,
                            ),
                            const SizedBox(width: 6),
                            Text(
                              'Lucky Draw (${_redemptions.length})',
                              style: TextStyle(
                                fontSize: 13,
                                fontWeight: _selectedTab == 0 ? FontWeight.bold : FontWeight.w500,
                                color: _selectedTab == 0 ? Colors.purple : AppColors.textSecondary,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                  Expanded(
                    child: GestureDetector(
                      onTap: () => setState(() => _selectedTab = 1),
                      child: Container(
                        padding: const EdgeInsets.symmetric(vertical: 10),
                        decoration: BoxDecoration(
                          color: _selectedTab == 1 ? Colors.white : Colors.transparent,
                          borderRadius: BorderRadius.circular(10),
                          boxShadow: _selectedTab == 1
                              ? [BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 4, offset: const Offset(0, 2))]
                              : null,
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(
                              Icons.history_rounded,
                              size: 16,
                              color: _selectedTab == 1 ? AppColors.primary : AppColors.textSecondary,
                            ),
                            const SizedBox(width: 6),
                            Text(
                              'History (${_transactions.length})',
                              style: TextStyle(
                                fontSize: 13,
                                fontWeight: _selectedTab == 1 ? FontWeight.bold : FontWeight.w500,
                                color: _selectedTab == 1 ? AppColors.primary : AppColors.textSecondary,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),

        // Selected List: Lucky Draw / Redemptions vs Transactions
        if (_selectedTab == 0)
          _redemptions.isEmpty
              ? SliverToBoxAdapter(
                  child: Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 24),
                    child: Card(
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(16),
                      ),
                      elevation: 0,
                      color: Colors.white,
                      child: Padding(
                        padding: const EdgeInsets.all(32),
                        child: Column(
                          children: [
                            Icon(
                              Icons.confirmation_number_outlined,
                              size: 56,
                              color: Colors.purple.shade200,
                            ),
                            const SizedBox(height: 12),
                            const Text(
                              'No Lucky Draw Tickets Yet',
                              style: TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.bold,
                                color: AppColors.textPrimary,
                              ),
                            ),
                            const SizedBox(height: 6),
                            const Text(
                              'Redeem your reward points to generate your unique Lucky Draw code and enter upcoming draws!',
                              textAlign: TextAlign.center,
                              style: TextStyle(
                                fontSize: 13,
                                color: AppColors.textSecondary,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                )
              : SliverPadding(
                  padding: const EdgeInsets.fromLTRB(16, 4, 16, 24),
                  sliver: SliverList(
                    delegate: SliverChildBuilderDelegate(
                      (context, index) {
                        final item = _redemptions[index];
                        return _buildRedemptionCard(item);
                      },
                      childCount: _redemptions.length,
                    ),
                  ),
                )
        else
          _transactions.isEmpty
              ? SliverToBoxAdapter(
                  child: Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 32),
                    child: Card(
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(16),
                      ),
                      elevation: 0,
                      color: Colors.white,
                      child: Padding(
                        padding: const EdgeInsets.all(32),
                        child: Column(
                          children: [
                            Icon(
                              Icons.receipt_long_outlined,
                              size: 56,
                              color: Colors.grey.shade400,
                            ),
                            const SizedBox(height: 12),
                            const Text(
                              'No Transactions Found',
                              style: TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.bold,
                                color: AppColors.textPrimary,
                              ),
                            ),
                            const SizedBox(height: 6),
                            const Text(
                              'Scan pharmacy bills to start accumulating reward points!',
                              textAlign: TextAlign.center,
                              style: TextStyle(
                                fontSize: 13,
                                color: AppColors.textSecondary,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                )
              : SliverPadding(
                  padding: const EdgeInsets.fromLTRB(16, 4, 16, 24),
                  sliver: SliverList(
                    delegate: SliverChildBuilderDelegate(
                      (context, index) {
                        final item = _transactions[index];
                        return _buildTransactionCard(item);
                      },
                      childCount: _transactions.length,
                    ),
                  ),
                ),
      ],
    );
  }

  Widget _buildRedemptionCard(Map<String, dynamic> item) {
    final code = item['redemption_code'] ?? item['lucky_draw_code'] ?? 'RDM-${item['id']}';
    final type = item['redemption_type']?.toString().toUpperCase() ?? 'REWARD';
    final points = item['points_used']?.toString() ?? '0';
    final status = item['status']?.toString() ?? 'pending';
    final dateStr = item['requested_at']?.toString() ?? item['created_at']?.toString();

    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      elevation: 1,
      color: Colors.white,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
        side: BorderSide(color: Colors.purple.shade100, width: 1),
      ),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: Colors.purple.shade50,
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: const Icon(Icons.confirmation_number_rounded, color: Colors.purple, size: 20),
                    ),
                    const SizedBox(width: 10),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'LUCKY DRAW TICKET',
                          style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Colors.purple, letterSpacing: 0.5),
                        ),
                        Text(
                          _formatDate(dateStr),
                          style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
                        ),
                      ],
                    ),
                  ],
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: status == 'approved' || status == 'completed'
                        ? Colors.green.shade50
                        : Colors.orange.shade50,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    status.toUpperCase(),
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.bold,
                      color: status == 'approved' || status == 'completed'
                          ? Colors.green.shade700
                          : Colors.orange.shade700,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
              decoration: BoxDecoration(
                color: Colors.purple.shade50.withValues(alpha: 0.5),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: Colors.purple.shade200),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'TICKET CODE',
                        style: TextStyle(fontSize: 9, fontWeight: FontWeight.bold, color: AppColors.textSecondary),
                      ),
                      Text(
                        code.toString(),
                        style: const TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.w900,
                          fontFamily: 'monospace',
                          color: Colors.purple,
                        ),
                      ),
                    ],
                  ),
                  IconButton(
                    icon: const Icon(Icons.copy_rounded, color: Colors.purple, size: 20),
                    tooltip: 'Copy Code',
                    onPressed: () {
                      Clipboard.setData(ClipboardData(text: code.toString()));
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(
                          content: Text('Lucky Draw Code "$code" copied to clipboard!'),
                          backgroundColor: Colors.purple,
                          duration: const Duration(seconds: 2),
                        ),
                      );
                    },
                  ),
                ],
              ),
            ),
            const SizedBox(height: 10),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'Method: $type',
                  style: const TextStyle(fontSize: 12, color: AppColors.textSecondary, fontWeight: FontWeight.w500),
                ),
                Text(
                  'Points Used: $points pts',
                  style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildBalanceCard({
    required String totalPoints,
    required String totalBonus,
    required String totalRoyalty,
    required String totalValue,
  }) {
    return Card(
      elevation: 4,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
      ),
      margin: EdgeInsets.zero,
      clipBehavior: Clip.antiAlias,
      child: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            colors: [
              AppColors.primary,
              Color(0xFF132B45),
            ],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
        ),
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.15),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: const Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(Icons.account_balance_wallet_rounded,
                          size: 14, color: Colors.white),
                      SizedBox(width: 6),
                      Text(
                        'WALLET BALANCE',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 11,
                          fontWeight: FontWeight.w600,
                          letterSpacing: 0.5,
                        ),
                      ),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: AppColors.accent.withValues(alpha: 0.2),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.monetization_on,
                          size: 14, color: AppColors.accent),
                      const SizedBox(width: 4),
                      Text(
                        'NPR $totalValue',
                        style: const TextStyle(
                          color: AppColors.accent,
                          fontSize: 12,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            const Text(
              'Total Points',
              style: TextStyle(
                color: Colors.white70,
                fontSize: 13,
                fontWeight: FontWeight.w500,
              ),
            ),
            const SizedBox(height: 4),
            Row(
              crossAxisAlignment: CrossAxisAlignment.baseline,
              textBaseline: TextBaseline.alphabetic,
              children: [
                Text(
                  totalPoints,
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 34,
                    fontWeight: FontWeight.bold,
                    letterSpacing: -0.5,
                  ),
                ),
                const SizedBox(width: 8),
                const Text(
                  'PTS',
                  style: TextStyle(
                    color: AppColors.accent,
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 20),
            // Metrics Row: Bonus & Royalty
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: 0.08),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(
                  color: Colors.white.withValues(alpha: 0.1),
                ),
              ),
              child: Row(
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Row(
                          children: [
                            Icon(Icons.bolt_rounded,
                                size: 14, color: AppColors.accent),
                            SizedBox(width: 4),
                            Text(
                              'Total Bonus',
                              style: TextStyle(
                                color: Colors.white70,
                                fontSize: 12,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 4),
                        Text(
                          '$totalBonus pts',
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 16,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                      ],
                    ),
                  ),
                  Container(
                    height: 32,
                    width: 1,
                    color: Colors.white.withValues(alpha: 0.2),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Row(
                          children: [
                            Icon(Icons.workspace_premium_rounded,
                                size: 14, color: AppColors.primaryLight),
                            SizedBox(width: 4),
                            Text(
                              'Total Royalty',
                              style: TextStyle(
                                color: Colors.white70,
                                fontSize: 12,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 4),
                        Text(
                          '$totalRoyalty pts',
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 16,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),
            // Redeem Button
            SizedBox(
              width: double.infinity,
              height: 48,
              child: ElevatedButton.icon(
                onPressed: _showRedeemDialog,
                icon: const Icon(Icons.redeem_rounded, size: 20),
                label: const Text(
                  'Redeem Points',
                  style: TextStyle(
                    fontSize: 15,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.secondary,
                  foregroundColor: Colors.white,
                  elevation: 0,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildTransactionCard(Map<String, dynamic> item) {
    final type = item['transaction_type']?.toString() ?? 'earn';
    final points = item['points_earned']?.toString() ??
        item['points_used']?.toString() ??
        '0';
    final dateStr = item['created_at']?.toString();
    final badgeColor = _getTransactionBadgeColor(type);
    final isNegative = type.toLowerCase().contains('redeem');

    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      elevation: 0.5,
      color: Colors.white,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
        side: const BorderSide(color: AppColors.border, width: 0.5),
      ),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(
          children: [
            Container(
              width: 44,
              height: 44,
              decoration: BoxDecoration(
                color: badgeColor.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Icon(
                isNegative ? Icons.arrow_downward_rounded : Icons.arrow_upward_rounded,
                color: badgeColor,
                size: 22,
              ),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 8, vertical: 3),
                        decoration: BoxDecoration(
                          color: badgeColor.withValues(alpha: 0.12),
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Text(
                          _formatTransactionType(type),
                          style: TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.w700,
                            color: badgeColor,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 6),
                  Text(
                    _formatDate(dateStr),
                    style: const TextStyle(
                      fontSize: 12,
                      color: AppColors.textSecondary,
                    ),
                  ),
                ],
              ),
            ),
            Column(
              crossAxisAlignment: CrossAxisAlignment.end,
              children: [
                Text(
                  '${isNegative ? '-' : '+'}$points',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                    color: isNegative ? AppColors.danger : AppColors.secondary,
                  ),
                ),
                const SizedBox(height: 2),
                const Text(
                  'Points',
                  style: TextStyle(
                    fontSize: 11,
                    color: AppColors.textSecondary,
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
