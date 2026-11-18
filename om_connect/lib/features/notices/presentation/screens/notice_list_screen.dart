import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../../../core/api/api_client.dart';
import '../../../../core/api/api_endpoints.dart';
import '../../../../core/constants/app_theme.dart';

class NoticeListScreen extends StatefulWidget {
  const NoticeListScreen({super.key});

  @override
  State<NoticeListScreen> createState() => _NoticeListScreenState();
}

class _NoticeListScreenState extends State<NoticeListScreen> {
  // Filter types: All (null), General ('general'), AGM ('agm'), Financial Report ('financial_report')
  final List<Map<String, String?>> _filterOptions = [
    {'label': 'All', 'value': null},
    {'label': 'General', 'value': 'general'},
    {'label': 'AGM', 'value': 'agm'},
    {'label': 'Financial Report', 'value': 'financial_report'},
  ];

  String? _selectedFilter;
  bool _isLoading = true;
  String? _errorMessage;
  List<Map<String, dynamic>> _notices = [];

  @override
  void initState() {
    super.initState();
    _fetchNotices();
  }

  Future<void> _fetchNotices() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final queryParams = <String, dynamic>{};
      if (_selectedFilter != null && _selectedFilter!.isNotEmpty) {
        queryParams['notice_type'] = _selectedFilter;
      }

      final response = await ApiClient.instance.get(
        ApiEndpoints.notices,
        queryParameters: queryParams,
      );

      if (response.statusCode == 200 && response.data != null) {
        final data = response.data;
        List<dynamic> list = [];
        if (data is Map<String, dynamic> && data['data'] is List) {
          list = data['data'] as List<dynamic>;
        } else if (data is List) {
          list = data;
        }

        if (mounted) {
          setState(() {
            _notices = list.whereType<Map<String, dynamic>>().toList();
            _isLoading = false;
          });
        }
      } else {
        throw ApiException(message: 'Failed to retrieve notices.');
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _isLoading = false;
          _errorMessage = e is ApiException
              ? e.message
              : 'Unable to load notices. Please check your network connection.';
        });
      }
    }
  }

  void _onFilterSelected(String? filterValue) {
    if (_selectedFilter == filterValue) return;
    setState(() {
      _selectedFilter = filterValue;
    });
    _fetchNotices();
  }

  String _formatDate(String? raw) {
    if (raw == null || raw.isEmpty) return 'Recent';
    try {
      final dt = DateTime.parse(raw).toLocal();
      return DateFormat('MMM dd, yyyy').format(dt);
    } catch (_) {
      return raw;
    }
  }

  Color _getTypeColor(String? type) {
    switch (type?.toLowerCase()) {
      case 'agm':
        return AppColors.accent;
      case 'financial_report':
        return AppColors.secondary;
      case 'general':
        return AppColors.primary;
      case 'announcement':
        return AppColors.info;
      default:
        return AppColors.primaryLight;
    }
  }

  String _formatType(String? type) {
    switch (type?.toLowerCase()) {
      case 'agm':
        return 'AGM';
      case 'financial_report':
        return 'Financial Report';
      case 'general':
        return 'General';
      case 'announcement':
        return 'Announcement';
      default:
        return type ?? 'Notice';
    }
  }

  void _showNoticeDetail(Map<String, dynamic> notice) {
    final title = notice['title'] ?? 'Notice';
    final body = notice['body'] ?? 'No additional details provided.';
    final date = _formatDate(notice['published_at']);
    final type = _formatType(notice['notice_type']);
    final typeColor = _getTypeColor(notice['notice_type']);

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) {
        return DraggableScrollableSheet(
          initialChildSize: 0.75,
          minChildSize: 0.5,
          maxChildSize: 0.95,
          builder: (_, scrollController) {
            return Container(
              decoration: const BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
              ),
              child: Column(
                children: [
                  Center(
                    child: Container(
                      width: 40,
                      height: 4,
                      margin: const EdgeInsets.symmetric(vertical: 12),
                      decoration: BoxDecoration(
                        color: Colors.grey.shade300,
                        borderRadius: BorderRadius.circular(2),
                      ),
                    ),
                  ),
                  Expanded(
                    child: ListView(
                      controller: scrollController,
                      padding: const EdgeInsets.fromLTRB(24, 8, 24, 24),
                      children: [
                        Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 10,
                                vertical: 4,
                              ),
                              decoration: BoxDecoration(
                                color: typeColor.withValues(alpha: 0.12),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Text(
                                type,
                                style: TextStyle(
                                  color: typeColor,
                                  fontSize: 12,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ),
                            const Spacer(),
                            const Icon(
                              Icons.calendar_today_rounded,
                              size: 14,
                              color: AppColors.textSecondary,
                            ),
                            const SizedBox(width: 4),
                            Text(
                              date,
                              style: const TextStyle(
                                fontSize: 13,
                                color: AppColors.textSecondary,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 16),
                        Text(
                          title,
                          style: const TextStyle(
                            fontSize: 22,
                            fontWeight: FontWeight.bold,
                            color: AppColors.textPrimary,
                            height: 1.3,
                          ),
                        ),
                        const SizedBox(height: 16),
                        const Divider(),
                        const SizedBox(height: 16),
                        Text(
                          body,
                          style: const TextStyle(
                            fontSize: 15,
                            height: 1.6,
                            color: AppColors.textPrimary,
                          ),
                        ),
                        const SizedBox(height: 32),
                        SizedBox(
                          width: double.infinity,
                          child: ElevatedButton(
                            onPressed: () => Navigator.pop(context),
                            child: const Text('Close'),
                          ),
                        ),
                      ],
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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.surface,
      appBar: AppBar(
        title: const Text('Notices & Announcements'),
        elevation: 0,
      ),
      body: Column(
        children: [
          // Filter Chips Section
          Container(
            color: Colors.white,
            padding: const EdgeInsets.symmetric(vertical: 12),
            child: SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: Row(
                children: _filterOptions.map((option) {
                  final label = option['label']!;
                  final value = option['value'];
                  final isSelected = _selectedFilter == value;

                  return Padding(
                    padding: const EdgeInsets.only(right: 8),
                    child: FilterChip(
                      label: Text(label),
                      selected: isSelected,
                      onSelected: (_) => _onFilterSelected(value),
                      selectedColor: AppColors.primary,
                      checkmarkColor: Colors.white,
                      backgroundColor: AppColors.surface,
                      labelStyle: TextStyle(
                        color: isSelected ? Colors.white : AppColors.textPrimary,
                        fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
                        fontSize: 13,
                      ),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(20),
                        side: BorderSide(
                          color: isSelected ? AppColors.primary : AppColors.border,
                        ),
                      ),
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    ),
                  );
                }).toList(),
              ),
            ),
          ),
          const Divider(height: 1, thickness: 1, color: AppColors.border),

          // Notices List
          Expanded(
            child: RefreshIndicator(
              onRefresh: _fetchNotices,
              color: AppColors.primary,
              child: _isLoading
                  ? const Center(child: CircularProgressIndicator())
                  : _errorMessage != null && _notices.isEmpty
                      ? _buildErrorView()
                      : _notices.isEmpty
                          ? _buildEmptyState()
                          : _buildNoticeList(),
            ),
          ),
        ],
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
          _errorMessage ?? 'Failed to load notices.',
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
            onPressed: _fetchNotices,
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

  Widget _buildEmptyState() {
    return ListView(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.all(32),
      children: [
        const SizedBox(height: 80),
        Center(
          child: Container(
            width: 90,
            height: 90,
            decoration: BoxDecoration(
              color: AppColors.primary.withValues(alpha: 0.08),
              shape: BoxShape.circle,
            ),
            child: const Icon(
              Icons.campaign_outlined,
              size: 48,
              color: AppColors.primary,
            ),
          ),
        ),
        const SizedBox(height: 24),
        const Text(
          'No Notices Found',
          textAlign: TextAlign.center,
          style: TextStyle(
            fontSize: 20,
            fontWeight: FontWeight.bold,
            color: AppColors.textPrimary,
          ),
        ),
        const SizedBox(height: 8),
        Text(
          _selectedFilter == null
              ? 'There are currently no active announcements published.'
              : 'No notices available under this category.',
          textAlign: TextAlign.center,
          style: const TextStyle(
            fontSize: 14,
            color: AppColors.textSecondary,
          ),
        ),
      ],
    );
  }

  Widget _buildNoticeList() {
    return ListView.builder(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.all(16),
      itemCount: _notices.length,
      itemBuilder: (context, index) {
        final notice = _notices[index];
        final title = notice['title'] ?? 'Notice';
        final body = notice['body'] ?? '';
        final date = _formatDate(notice['published_at']);
        final type = _formatType(notice['notice_type']);
        final typeColor = _getTypeColor(notice['notice_type']);

        return Card(
          margin: const EdgeInsets.only(bottom: 14),
          elevation: 0.8,
          color: Colors.white,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
            side: const BorderSide(color: AppColors.border, width: 0.6),
          ),
          child: InkWell(
            onTap: () => _showNoticeDetail(notice),
            borderRadius: BorderRadius.circular(16),
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Type Badge & Date Row
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 8,
                          vertical: 3,
                        ),
                        decoration: BoxDecoration(
                          color: typeColor.withValues(alpha: 0.12),
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Text(
                          type,
                          style: TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.w700,
                            color: typeColor,
                          ),
                        ),
                      ),
                      const Spacer(),
                      const Icon(
                        Icons.event_outlined,
                        size: 14,
                        color: AppColors.textSecondary,
                      ),
                      const SizedBox(width: 4),
                      Text(
                        date,
                        style: const TextStyle(
                          fontSize: 12,
                          color: AppColors.textSecondary,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  // Title
                  Text(
                    title,
                    style: const TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                      color: AppColors.textPrimary,
                      height: 1.25,
                    ),
                  ),
                  const SizedBox(height: 8),
                  // Body preview
                  Text(
                    body,
                    maxLines: 3,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      fontSize: 13,
                      color: AppColors.textSecondary,
                      height: 1.4,
                    ),
                  ),
                  const SizedBox(height: 12),
                  // Read more link
                  Row(
                    mainAxisAlignment: MainAxisAlignment.end,
                    children: [
                      Text(
                        'Read more',
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.w600,
                          color: AppColors.primary,
                        ),
                      ),
                      const SizedBox(width: 4),
                      Icon(
                        Icons.arrow_forward_rounded,
                        size: 14,
                        color: AppColors.primary,
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        );
      },
    );
  }
}
