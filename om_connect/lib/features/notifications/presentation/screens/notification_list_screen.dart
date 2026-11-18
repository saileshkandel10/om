import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../../../core/api/api_client.dart';
import '../../../../core/api/api_endpoints.dart';
import '../../../../core/constants/app_theme.dart';

class NotificationListScreen extends StatefulWidget {
  const NotificationListScreen({super.key});

  @override
  State<NotificationListScreen> createState() => _NotificationListScreenState();
}

class _NotificationListScreenState extends State<NotificationListScreen> {
  bool _isLoading = true;
  bool _isMarkingAll = false;
  String? _errorMessage;
  int _unreadCount = 0;
  List<Map<String, dynamic>> _notifications = [];

  @override
  void initState() {
    super.initState();
    _loadAll();
  }

  Future<void> _loadAll() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      await Future.wait([
        _fetchUnreadCount(),
        _fetchNotifications(),
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
              : 'Failed to load notifications. Please try again.';
        });
      }
    }
  }

  Future<void> _fetchUnreadCount() async {
    try {
      final response = await ApiClient.instance.get(
        ApiEndpoints.notificationsUnreadCount,
      );
      if (response.statusCode == 200 && response.data != null) {
        final count = response.data['unread_count'];
        if (mounted && count != null) {
          setState(() {
            _unreadCount = (count as num).toInt();
          });
        }
      }
    } catch (_) {
      // Non-critical, fallback quietly
    }
  }

  Future<void> _fetchNotifications() async {
    final response = await ApiClient.instance.get(
      ApiEndpoints.notifications,
    );

    if (response.statusCode == 200 && response.data != null) {
      final data = response.data;
      List<dynamic> list = [];
      if (data is Map<String, dynamic> && data['data'] is List) {
        list = data['data'] as List<dynamic>;
      } else if (data is List) {
        list = data;
      }
      _notifications = list
          .whereType<Map<String, dynamic>>()
          .toList();
    } else {
      throw ApiException(message: 'Failed to retrieve notifications list.');
    }
  }

  Future<void> _markAllAsRead() async {
    if (_unreadCount == 0 && !_notifications.any((n) => !(n['is_read'] ?? false))) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('All notifications are already marked as read.'),
          backgroundColor: AppColors.info,
        ),
      );
      return;
    }

    setState(() => _isMarkingAll = true);

    try {
      final response = await ApiClient.instance.put(
        ApiEndpoints.notificationsReadAll,
      );

      if (mounted) {
        setState(() {
          _isMarkingAll = false;
          _unreadCount = 0;
          for (var item in _notifications) {
            item['is_read'] = true;
          }
        });

        final msg = response.data?['message'] ?? 'All notifications marked as read.';
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(msg),
            backgroundColor: AppColors.secondary,
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isMarkingAll = false);
        final err = e is ApiException ? e.message : 'Failed to mark all as read.';
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(err),
            backgroundColor: AppColors.danger,
          ),
        );
      }
    }
  }

  Future<void> _markSingleAsRead(Map<String, dynamic> notification, int index) async {
    final isRead = notification['is_read'] == true;
    final id = notification['id'];

    if (!isRead && id != null) {
      // Optimistic update
      setState(() {
        _notifications[index]['is_read'] = true;
        if (_unreadCount > 0) _unreadCount--;
      });

      try {
        await ApiClient.instance.put(
          ApiEndpoints.notificationRead(id as int),
        );
      } catch (e) {
        // Rollback on failure
        if (mounted) {
          setState(() {
            _notifications[index]['is_read'] = false;
            _unreadCount++;
          });
        }
      }
    }

    // Show details modal
    if (mounted) {
      _showNotificationDetail(notification);
    }
  }

  void _showNotificationDetail(Map<String, dynamic> notification) {
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
                  margin: const EdgeInsets.only(bottom: 20),
                  decoration: BoxDecoration(
                    color: Colors.grey.shade300,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: AppColors.primary.withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: const Icon(
                      Icons.notifications_active_rounded,
                      color: AppColors.primary,
                      size: 24,
                    ),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          notification['title'] ?? 'Notification',
                          style: const TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                            color: AppColors.textPrimary,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          _timeAgo(notification['created_at']),
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
              const SizedBox(height: 16),
              const Divider(),
              const SizedBox(height: 12),
              Text(
                notification['message'] ?? 'No message content.',
                style: const TextStyle(
                  fontSize: 15,
                  height: 1.5,
                  color: AppColors.textPrimary,
                ),
              ),
              const SizedBox(height: 24),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: () => Navigator.pop(context),
                  child: const Text('Close'),
                ),
              ),
              const SizedBox(height: 8),
            ],
          ),
        );
      },
    );
  }

  String _timeAgo(String? rawDate) {
    if (rawDate == null || rawDate.isEmpty) return 'Just now';
    try {
      final date = DateTime.parse(rawDate).toLocal();
      final difference = DateTime.now().difference(date);

      if (difference.inSeconds < 60) {
        return 'Just now';
      } else if (difference.inMinutes < 60) {
        return '${difference.inMinutes}m ago';
      } else if (difference.inHours < 24) {
        return '${difference.inHours}h ago';
      } else if (difference.inDays < 7) {
        return '${difference.inDays}d ago';
      } else {
        return DateFormat('MMM dd, yyyy').format(date);
      }
    } catch (_) {
      return rawDate;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.surface,
      appBar: AppBar(
        title: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Text('Notifications'),
            if (_unreadCount > 0) ...[
              const SizedBox(width: 8),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                decoration: BoxDecoration(
                  color: AppColors.accent,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  '$_unreadCount',
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 12,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
            ],
          ],
        ),
        actions: [
          if (_isMarkingAll)
            const Center(
              child: Padding(
                padding: EdgeInsets.symmetric(horizontal: 16),
                child: SizedBox(
                  width: 20,
                  height: 20,
                  child: CircularProgressIndicator(
                    strokeWidth: 2,
                    color: Colors.white,
                  ),
                ),
              ),
            )
          else
            TextButton.icon(
              onPressed: _markAllAsRead,
              icon: const Icon(Icons.done_all_rounded, size: 18, color: Colors.white),
              label: const Text(
                'Mark All Read',
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 13,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _loadAll,
        color: AppColors.primary,
        child: _isLoading
            ? const Center(child: CircularProgressIndicator())
            : _errorMessage != null && _notifications.isEmpty
                ? _buildErrorView()
                : _notifications.isEmpty
                    ? _buildEmptyState()
                    : _buildNotificationsList(),
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
          _errorMessage ?? 'Failed to load notifications.',
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
            onPressed: _loadAll,
            icon: const Icon(Icons.refresh_rounded),
            label: const Text('Retry'),
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
            width: 100,
            height: 100,
            decoration: BoxDecoration(
              color: AppColors.primary.withValues(alpha: 0.08),
              shape: BoxShape.circle,
            ),
            child: const Icon(
              Icons.notifications_none_rounded,
              size: 52,
              color: AppColors.primary,
            ),
          ),
        ),
        const SizedBox(height: 24),
        const Text(
          'No Notifications Yet',
          textAlign: TextAlign.center,
          style: TextStyle(
            fontSize: 20,
            fontWeight: FontWeight.bold,
            color: AppColors.textPrimary,
          ),
        ),
        const SizedBox(height: 8),
        const Text(
          'You are all caught up! Updates regarding bills, bonus rewards, and notices will appear here.',
          textAlign: TextAlign.center,
          style: TextStyle(
            fontSize: 14,
            color: AppColors.textSecondary,
            height: 1.4,
          ),
        ),
      ],
    );
  }

  Widget _buildNotificationsList() {
    return ListView.builder(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      itemCount: _notifications.length,
      itemBuilder: (context, index) {
        final notification = _notifications[index];
        final isRead = notification['is_read'] == true;
        final title = notification['title'] ?? 'Notification';
        final message = notification['message'] ?? '';
        final createdAt = notification['created_at'];

        return Card(
          margin: const EdgeInsets.only(bottom: 12),
          elevation: isRead ? 0.3 : 1.5,
          color: isRead ? Colors.white : const Color(0xFFF3F7FC),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
            side: BorderSide(
              color: isRead
                  ? AppColors.border
                  : AppColors.primaryLight.withValues(alpha: 0.4),
              width: isRead ? 0.5 : 1.2,
            ),
          ),
          child: InkWell(
            onTap: () => _markSingleAsRead(notification, index),
            borderRadius: BorderRadius.circular(16),
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Icon
                  Container(
                    width: 44,
                    height: 44,
                    decoration: BoxDecoration(
                      color: isRead
                          ? Colors.grey.shade100
                          : AppColors.primary.withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Icon(
                      isRead
                          ? Icons.notifications_none_rounded
                          : Icons.notifications_active_rounded,
                      color: isRead ? AppColors.textSecondary : AppColors.primary,
                      size: 22,
                    ),
                  ),
                  const SizedBox(width: 14),
                  // Content
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Expanded(
                              child: Text(
                                title,
                                style: TextStyle(
                                  fontSize: 15,
                                  fontWeight: isRead ? FontWeight.w600 : FontWeight.bold,
                                  color: AppColors.textPrimary,
                                ),
                              ),
                            ),
                            if (!isRead)
                              Container(
                                width: 8,
                                height: 8,
                                margin: const EdgeInsets.only(left: 6),
                                decoration: const BoxDecoration(
                                  color: AppColors.primary,
                                  shape: BoxShape.circle,
                                ),
                              ),
                          ],
                        ),
                        const SizedBox(height: 6),
                        Text(
                          message,
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                          style: TextStyle(
                            fontSize: 13,
                            color: isRead ? AppColors.textSecondary : AppColors.textPrimary,
                            height: 1.3,
                          ),
                        ),
                        const SizedBox(height: 8),
                        Row(
                          children: [
                            const Icon(
                              Icons.access_time_rounded,
                              size: 13,
                              color: AppColors.textSecondary,
                            ),
                            const SizedBox(width: 4),
                            Text(
                              _timeAgo(createdAt),
                              style: const TextStyle(
                                fontSize: 11,
                                color: AppColors.textSecondary,
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
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
