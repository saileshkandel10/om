import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:shimmer/shimmer.dart';
import '../../../../core/api/api_client.dart';
import '../../../../core/api/api_endpoints.dart';
import '../../../../core/constants/app_theme.dart';

class BillListScreen extends StatefulWidget {
  const BillListScreen({super.key});

  @override
  State<BillListScreen> createState() => _BillListScreenState();
}

class _BillListScreenState extends State<BillListScreen> {
  final List<String> _filters = ['All', 'Pending', 'Completed', 'Failed'];
  String _selectedFilter = 'All';

  bool _isLoading = true;
  String? _errorMessage;
  List<Map<String, dynamic>> _bills = [];

  @override
  void initState() {
    super.initState();
    _fetchBills();
  }

  Future<void> _fetchBills() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final queryParams = <String, dynamic>{};
      if (_selectedFilter != 'All') {
        queryParams['ocr_status'] = _selectedFilter.toLowerCase();
      }

      final response = await ApiClient.instance.get(
        ApiEndpoints.bills,
        queryParameters: queryParams.isNotEmpty ? queryParams : null,
      );

      if (response.statusCode == 200 && response.data != null) {
        final data = response.data;
        List<dynamic> items = [];

        if (data is Map<String, dynamic> && data.containsKey('data')) {
          items = data['data'] as List<dynamic>? ?? [];
        } else if (data is List<dynamic>) {
          items = data;
        }

        if (mounted) {
          setState(() {
            _bills = items
                .whereType<Map<String, dynamic>>()
                .toList();
            _isLoading = false;
          });
        }
      } else {
        throw ApiException(message: 'Failed to load purchase bills.');
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
              onPressed: _fetchBills,
            ),
          ),
        );
      }
    }
  }

  void _onFilterSelected(String filter) {
    if (_selectedFilter != filter) {
      setState(() {
        _selectedFilter = filter;
      });
      _fetchBills();
    }
  }

  Future<void> _navigateToUpload() async {
    try {
      final result = await context.push<bool>('/bills/upload');
      if (result == true && mounted) {
        _fetchBills();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Upload screen not available'),
            backgroundColor: AppColors.info,
          ),
        );
      }
    }
  }

  String _formatDate(String? rawDate) {
    if (rawDate == null || rawDate.isEmpty) return 'Recent';
    try {
      final parsed = DateTime.parse(rawDate);
      return DateFormat('MMM d, yyyy').format(parsed);
    } catch (_) {
      return rawDate;
    }
  }

  String _formatAmount(dynamic amount) {
    if (amount == null) return 'Rs. 0.00';
    final parsed = double.tryParse(amount.toString());
    if (parsed != null) {
      final formatter = NumberFormat('#,##0.00', 'en_US');
      return 'Rs. ${formatter.format(parsed)}';
    }
    return 'Rs. $amount';
  }

  Color _getOcrStatusColor(String? status) {
    switch (status?.toLowerCase()) {
      case 'completed':
        return AppColors.secondary;
      case 'processing':
        return AppColors.info;
      case 'pending':
        return AppColors.accent;
      case 'failed':
        return AppColors.danger;
      default:
        return AppColors.textSecondary;
    }
  }

  String _getOcrStatusLabel(String? status) {
    switch (status?.toLowerCase()) {
      case 'completed':
        return 'OCR Done';
      case 'processing':
        return 'Processing';
      case 'pending':
        return 'Queued';
      case 'failed':
        return 'OCR Failed';
      default:
        return status ?? 'Pending';
    }
  }

  Color _getValidationColor(String? status) {
    switch (status?.toLowerCase()) {
      case 'approved':
        return AppColors.secondary;
      case 'rejected':
        return AppColors.danger;
      case 'pending':
      default:
        return AppColors.accent;
    }
  }

  String _getValidationLabel(String? status) {
    switch (status?.toLowerCase()) {
      case 'approved':
        return 'Approved';
      case 'rejected':
        return 'Rejected';
      case 'pending':
      default:
        return 'Pending Review';
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.surface,
      appBar: AppBar(
        title: const Text(
          'My Bills',
          style: TextStyle(fontWeight: FontWeight.w700),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            tooltip: 'Refresh',
            onPressed: _fetchBills,
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _navigateToUpload,
        backgroundColor: AppColors.primary,
        foregroundColor: Colors.white,
        icon: const Icon(Icons.cloud_upload_outlined),
        label: const Text(
          'Upload Receipt',
          style: TextStyle(fontWeight: FontWeight.bold),
        ),
      ),
      body: Column(
        children: [
          _buildFilterChipsBar(),
          Expanded(
            child: RefreshIndicator(
              color: AppColors.primary,
              onRefresh: _fetchBills,
              child: _isLoading
                  ? _buildShimmerList()
                  : _errorMessage != null && _bills.isEmpty
                      ? _buildErrorView()
                      : _bills.isEmpty
                          ? _buildEmptyView()
                          : _buildBillsList(),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFilterChipsBar() {
    return Container(
      width: double.infinity,
      color: Colors.white,
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
      child: SingleChildScrollView(
        scrollDirection: Axis.horizontal,
        child: Row(
          children: _filters.map((filter) {
            final isSelected = _selectedFilter == filter;
            return Padding(
              padding: const EdgeInsets.only(right: 8),
              child: FilterChip(
                selected: isSelected,
                label: Text(filter),
                labelStyle: TextStyle(
                  color: isSelected ? Colors.white : AppColors.textPrimary,
                  fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
                  fontSize: 13,
                ),
                backgroundColor: AppColors.surface,
                selectedColor: AppColors.primary,
                checkmarkColor: Colors.white,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(20),
                  side: BorderSide(
                    color: isSelected
                        ? AppColors.primary
                        : AppColors.border,
                  ),
                ),
                onSelected: (_) => _onFilterSelected(filter),
              ),
            );
          }).toList(),
        ),
      ),
    );
  }

  Widget _buildBillsList() {
    return ListView.builder(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      itemCount: _bills.length,
      itemBuilder: (context, index) {
        final bill = _bills[index];
        return _buildBillCard(bill);
      },
    );
  }

  Widget _buildBillCard(Map<String, dynamic> bill) {
    final invoiceNumber = bill['invoice_number'] as String?;
    final billId = bill['id']?.toString() ?? '-';
    final title = invoiceNumber != null && invoiceNumber.isNotEmpty
        ? invoiceNumber
        : 'Bill #$billId';

    final invoiceDate = bill['invoice_date'] as String? ?? bill['created_at'] as String?;
    final amount = bill['total_amount'];
    final ocrStatus = bill['ocr_status'] as String?;
    final validationStatus = bill['validation_status'] as String?;

    final ocrColor = _getOcrStatusColor(ocrStatus);
    final ocrLabel = _getOcrStatusLabel(ocrStatus);
    final validationColor = _getValidationColor(validationStatus);
    final validationLabel = _getValidationLabel(validationStatus);

    final pharmacy = bill['pharmacy'] as Map<String, dynamic>?;
    final pharmacyName = pharmacy?['store_name'] as String?;

    return Card(
      elevation: 1,
      margin: const EdgeInsets.only(bottom: 12),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
        side: BorderSide(color: AppColors.border.withValues(alpha: 0.6)),
      ),
      child: InkWell(
        onTap: () => _showBillDetailsModal(bill),
        borderRadius: BorderRadius.circular(16),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Expanded(
                    child: Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(8),
                          decoration: BoxDecoration(
                            color: AppColors.primary.withValues(alpha: 0.1),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: const Icon(
                            Icons.receipt_rounded,
                            color: AppColors.primary,
                            size: 20,
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                title,
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: const TextStyle(
                                  fontSize: 15,
                                  fontWeight: FontWeight.bold,
                                  color: AppColors.textPrimary,
                                ),
                              ),
                              if (pharmacyName != null && pharmacyName.isNotEmpty)
                                Text(
                                  pharmacyName,
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                  style: const TextStyle(
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
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: ocrColor.withValues(alpha: 0.12),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: ocrColor.withValues(alpha: 0.5)),
                    ),
                    child: Text(
                      ocrLabel,
                      style: TextStyle(
                        color: ocrColor,
                        fontSize: 11,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                ],
              ),
              const Padding(
                padding: EdgeInsets.symmetric(vertical: 10),
                child: Divider(color: AppColors.divider, height: 1),
              ),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Row(
                    children: [
                      const Icon(
                        Icons.calendar_today_outlined,
                        size: 14,
                        color: AppColors.textSecondary,
                      ),
                      const SizedBox(width: 6),
                      Text(
                        _formatDate(invoiceDate),
                        style: const TextStyle(
                          fontSize: 13,
                          color: AppColors.textSecondary,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ],
                  ),
                  Text(
                    _formatAmount(amount),
                    style: const TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                      color: AppColors.primary,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                    decoration: BoxDecoration(
                      color: validationColor.withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Container(
                          width: 6,
                          height: 6,
                          decoration: BoxDecoration(
                            color: validationColor,
                            shape: BoxShape.circle,
                          ),
                        ),
                        const SizedBox(width: 6),
                        Text(
                          validationLabel,
                          style: TextStyle(
                            color: validationColor,
                            fontSize: 11,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ],
                    ),
                  ),
                  const Row(
                    children: [
                      Text(
                        'Details',
                        style: TextStyle(
                          fontSize: 12,
                          color: AppColors.primary,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      Icon(
                        Icons.chevron_right,
                        size: 16,
                        color: AppColors.primary,
                      ),
                    ],
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _showBillDetailsModal(Map<String, dynamic> bill) {
    final invoiceNumber = bill['invoice_number'] as String? ?? 'Pending OCR';
    final billId = bill['id']?.toString() ?? '-';
    final invoiceDate = bill['invoice_date'] as String? ?? bill['created_at'] as String?;
    final amount = bill['total_amount'];
    final ocrStatus = bill['ocr_status'] as String?;
    final validationStatus = bill['validation_status'] as String?;
    final confidenceScore = bill['confidence_score'];
    final receiptUrl = bill['receipt_url'] as String?;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) {
        return DraggableScrollableSheet(
          initialChildSize: 0.6,
          maxChildSize: 0.85,
          minChildSize: 0.4,
          expand: false,
          builder: (_, scrollController) {
            return SingleChildScrollView(
              controller: scrollController,
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Center(
                    child: Container(
                      width: 40,
                      height: 4,
                      decoration: BoxDecoration(
                        color: AppColors.border,
                        borderRadius: BorderRadius.circular(2),
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        'Bill Details (#$billId)',
                        style: const TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                          color: AppColors.textPrimary,
                        ),
                      ),
                      IconButton(
                        icon: const Icon(Icons.close),
                        onPressed: () => Navigator.pop(ctx),
                      ),
                    ],
                  ),
                  const Divider(color: AppColors.divider),
                  const SizedBox(height: 8),
                  _buildDetailRow('Invoice Number', invoiceNumber),
                  _buildDetailRow('Date', _formatDate(invoiceDate)),
                  _buildDetailRow('Total Amount', _formatAmount(amount)),
                  _buildDetailRow('OCR Status', _getOcrStatusLabel(ocrStatus)),
                  _buildDetailRow('Validation', _getValidationLabel(validationStatus)),
                  if (confidenceScore != null)
                    _buildDetailRow('OCR Confidence', '$confidenceScore%'),
                  if ((bill['items'] as List<dynamic>?)?.isNotEmpty == true) ...[
                    const SizedBox(height: 14),
                    const Text(
                      'Extracted Medicines',
                      style: TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.bold,
                        color: AppColors.textPrimary,
                      ),
                    ),
                    const SizedBox(height: 8),
                    ...((bill['items'] as List<dynamic>)).map((item) {
                      final name = item['product_name_raw'] ?? item['product']?['brand_name'] ?? item['name'] ?? 'Medicine';
                      final batch = item['batch_no'];
                      final qty = item['quantity']?.toString() ?? '1';
                      final price = item['unit_price'] != null ? 'Rs. ${item['unit_price']}' : null;
                      final total = item['amount'] != null ? 'Rs. ${item['amount']}' : null;

                      return Container(
                        margin: const EdgeInsets.only(bottom: 6),
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                        decoration: BoxDecoration(
                          color: AppColors.surface,
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(color: AppColors.border),
                        ),
                        child: Row(
                          children: [
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    name.toString(),
                                    style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
                                  ),
                                  if (batch != null && batch.toString().isNotEmpty)
                                    Text(
                                      'Batch: $batch',
                                      style: const TextStyle(color: AppColors.textSecondary, fontSize: 11),
                                    ),
                                ],
                              ),
                            ),
                            Column(
                              crossAxisAlignment: CrossAxisAlignment.end,
                              children: [
                                if (total != null)
                                  Text(
                                    total,
                                    style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.primary, fontSize: 13),
                                  ),
                                Text(
                                  'Qty: $qty${price != null ? ' • $price' : ''}',
                                  style: const TextStyle(color: AppColors.textSecondary, fontSize: 11),
                                ),
                              ],
                            ),
                          ],
                        ),
                      );
                    }),
                  ],
                  if (receiptUrl != null && receiptUrl.isNotEmpty) ...[
                    const SizedBox(height: 12),
                    const Text(
                      'Receipt Image',
                      style: TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.bold,
                        color: AppColors.textPrimary,
                      ),
                    ),
                    const SizedBox(height: 8),
                    ClipRRect(
                      borderRadius: BorderRadius.circular(12),
                      child: Image.network(
                        receiptUrl,
                        height: 180,
                        width: double.infinity,
                        fit: BoxFit.cover,
                        errorBuilder: (_, __, ___) => Container(
                          height: 100,
                          color: AppColors.surface,
                          alignment: Alignment.center,
                          child: const Text('Receipt preview not available'),
                        ),
                      ),
                    ),
                  ],
                  const SizedBox(height: 24),
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton(
                      onPressed: () => Navigator.pop(ctx),
                      child: const Text('Close'),
                    ),
                  ),
                ],
              ),
            );
          },
        );
      },
    );
  }

  Widget _buildDetailRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            label,
            style: const TextStyle(
              fontSize: 13,
              color: AppColors.textSecondary,
            ),
          ),
          Text(
            value,
            style: const TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.w600,
              color: AppColors.textPrimary,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildEmptyView() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(
                color: AppColors.primary.withValues(alpha: 0.08),
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.receipt_long_outlined,
                size: 56,
                color: AppColors.primary,
              ),
            ),
            const SizedBox(height: 20),
            Text(
              _selectedFilter == 'All'
                  ? 'No Bills Uploaded Yet'
                  : 'No $_selectedFilter Bills',
              style: const TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
                color: AppColors.textPrimary,
              ),
            ),
            const SizedBox(height: 8),
            const Text(
              'Upload your OM Pharmaceuticals purchase receipt to earn rewards and track purchases.',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 13,
                color: AppColors.textSecondary,
                height: 1.4,
              ),
            ),
            const SizedBox(height: 20),
            ElevatedButton.icon(
              onPressed: _navigateToUpload,
              icon: const Icon(Icons.cloud_upload_outlined, size: 20),
              label: const Text('Upload Receipt Now'),
              style: ElevatedButton.styleFrom(
                minimumSize: const Size(200, 48),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildErrorView() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(
              Icons.error_outline_rounded,
              color: AppColors.danger,
              size: 54,
            ),
            const SizedBox(height: 16),
            const Text(
              'Failed to Load Bills',
              style: TextStyle(
                fontSize: 17,
                fontWeight: FontWeight.bold,
                color: AppColors.textPrimary,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              _errorMessage ?? 'Unable to retrieve your bills list.',
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontSize: 13,
                color: AppColors.textSecondary,
              ),
            ),
            const SizedBox(height: 20),
            ElevatedButton.icon(
              onPressed: _fetchBills,
              icon: const Icon(Icons.refresh, size: 18),
              label: const Text('Try Again'),
              style: ElevatedButton.styleFrom(
                minimumSize: const Size(160, 44),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildShimmerList() {
    return Shimmer.fromColors(
      baseColor: Colors.grey.shade300,
      highlightColor: Colors.grey.shade100,
      child: ListView.builder(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        itemCount: 5,
        itemBuilder: (_, __) {
          return Card(
            elevation: 1,
            margin: const EdgeInsets.only(bottom: 12),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(16),
            ),
            child: Container(
              height: 110,
              padding: const EdgeInsets.all(16),
              color: Colors.white,
            ),
          );
        },
      ),
    );
  }
}
