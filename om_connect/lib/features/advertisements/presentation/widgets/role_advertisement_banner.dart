import 'dart:async';
import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../../../core/api/api_client.dart';
import '../../../../core/api/api_endpoints.dart';
import '../../../../core/constants/app_theme.dart';

class RoleAdvertisementBanner extends StatefulWidget {
  const RoleAdvertisementBanner({super.key});

  @override
  State<RoleAdvertisementBanner> createState() => _RoleAdvertisementBannerState();
}

class _RoleAdvertisementBannerState extends State<RoleAdvertisementBanner> {
  List<Map<String, dynamic>> _banners = [];
  bool _isLoading = true;
  int _currentPage = 0;
  late final PageController _pageController;
  Timer? _autoScrollTimer;
  final Set<int> _recordedImpressions = {};

  @override
  void initState() {
    super.initState();
    _pageController = PageController();
    _fetchBanners();
  }

  @override
  void dispose() {
    _autoScrollTimer?.cancel();
    _pageController.dispose();
    super.dispose();
  }

  Future<void> _fetchBanners() async {
    try {
      final response = await ApiClient.instance.get(ApiEndpoints.advertisements);
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
            _banners = list.whereType<Map<String, dynamic>>().toList();
            _isLoading = false;
          });

          if (_banners.isNotEmpty) {
            _recordImpression(_banners[0]['id']);
            if (_banners.length > 1) {
              _startAutoScroll();
            }
          }
        }
      } else {
        if (mounted) {
          setState(() => _isLoading = false);
        }
      }
    } catch (_) {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  void _startAutoScroll() {
    _autoScrollTimer?.cancel();
    _autoScrollTimer = Timer.periodic(const Duration(milliseconds: 4500), (timer) {
      if (!mounted || _banners.length <= 1 || !_pageController.hasClients) return;
      final nextPage = (_currentPage + 1) % _banners.length;
      _pageController.animateToPage(
        nextPage,
        duration: const Duration(milliseconds: 600),
        curve: Curves.easeInOutCubic,
      );
    });
  }

  void _recordImpression(dynamic adId) {
    if (adId == null) return;
    final id = int.tryParse(adId.toString());
    if (id == null || _recordedImpressions.contains(id)) return;
    _recordedImpressions.add(id);

    ApiClient.instance.post(ApiEndpoints.advertisementImpression(id)).catchError((_) => null as dynamic);
  }

  void _onAdClicked(Map<String, dynamic> banner) {
    final adId = banner['id'];
    if (adId != null) {
      final id = int.tryParse(adId.toString());
      if (id != null) {
        ApiClient.instance.post(ApiEndpoints.advertisementClick(id)).catchError((_) => null as dynamic);
      }
    }

    final actionUrl = banner['cta_action']?.toString();
    if (actionUrl != null && actionUrl.isNotEmpty) {
      final uri = Uri.tryParse(actionUrl);
      if (uri != null) {
        launchUrl(uri, mode: LaunchMode.externalApplication).catchError((_) => false);
      }
    }
  }

  Color _parseColor(String? colorStr, Color fallback) {
    if (colorStr == null || colorStr.isEmpty) return fallback;
    try {
      String hex = colorStr.replaceAll('#', '').trim();
      if (hex.length == 6) {
        hex = 'FF$hex';
      }
      if (hex.length == 8) {
        return Color(int.parse(hex, radix: 16));
      }
    } catch (_) {}
    return fallback;
  }

  Gradient _parseGradient(String? colorStr) {
    if (colorStr != null && colorStr.contains('linear-gradient')) {
      final hexMatches = RegExp(r'#([A-Fa-f0-9]{6})').allMatches(colorStr);
      if (hexMatches.length >= 2) {
        final colors = hexMatches.map((m) => _parseColor(m.group(0), AppColors.primary)).toList();
        return LinearGradient(
          colors: colors,
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        );
      }
    }

    final solidColor = _parseColor(colorStr, const Color(0xFF1E3A5F));
    return LinearGradient(
      colors: [solidColor, solidColor.withValues(alpha: 0.85)],
      begin: Alignment.topLeft,
      end: Alignment.bottomRight,
    );
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const SizedBox.shrink();
    }

    if (_banners.isEmpty) {
      return const SizedBox.shrink();
    }

    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        SizedBox(
          height: 145,
          child: PageView.builder(
            controller: _pageController,
            itemCount: _banners.length,
            onPageChanged: (index) {
              setState(() => _currentPage = index);
              _recordImpression(_banners[index]['id']);
            },
            itemBuilder: (context, index) {
              final banner = _banners[index];
              return _buildBannerCard(banner);
            },
          ),
        ),
        if (_banners.length > 1) ...[
          const SizedBox(height: 8),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: List.generate(_banners.length, (index) {
              final isActive = index == _currentPage;
              return AnimatedContainer(
                duration: const Duration(milliseconds: 300),
                margin: const EdgeInsets.symmetric(horizontal: 3),
                height: 6,
                width: isActive ? 18 : 6,
                decoration: BoxDecoration(
                  color: isActive ? AppColors.primary : Colors.grey.shade300,
                  borderRadius: BorderRadius.circular(3),
                ),
              );
            }),
          ),
        ],
      ],
    );
  }

  Widget _buildBannerCard(Map<String, dynamic> banner) {
    final type = banner['type']?.toString() ?? 'text';
    final imageUrl = banner['image_url']?.toString();
    final title = banner['title']?.toString() ?? '';
    final description = banner['description']?.toString() ?? '';
    final ctaText = banner['cta_text']?.toString();

    final bgGradient = _parseGradient(banner['background_color']?.toString());
    final textColor = _parseColor(banner['text_color']?.toString(), Colors.white);
    final btnBgColor = _parseColor(banner['button_color']?.toString(), Colors.white);
    final btnTextColor = _parseColor(banner['button_text_color']?.toString(), AppColors.primary);

    return GestureDetector(
      onTap: () => _onAdClicked(banner),
      child: Container(
        margin: const EdgeInsets.symmetric(horizontal: 2),
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.08),
              blurRadius: 10,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: ClipRRect(
          borderRadius: BorderRadius.circular(16),
          child: type == 'image' && imageUrl != null && imageUrl.isNotEmpty
              ? Stack(
                  fit: StackFit.expand,
                  children: [
                    CachedNetworkImage(
                      imageUrl: imageUrl,
                      fit: BoxFit.cover,
                      placeholder: (context, url) => Container(
                        color: Colors.grey.shade200,
                        child: const Center(child: CircularProgressIndicator(strokeWidth: 2)),
                      ),
                      errorWidget: (context, url, error) => Container(
                        decoration: BoxDecoration(gradient: bgGradient),
                        padding: const EdgeInsets.all(16),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Text(
                              title,
                              style: TextStyle(color: textColor, fontWeight: FontWeight.bold, fontSize: 15),
                              maxLines: 2,
                            ),
                          ],
                        ),
                      ),
                    ),
                    if (ctaText != null && ctaText.isNotEmpty)
                      Positioned(
                        bottom: 12,
                        right: 12,
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                          decoration: BoxDecoration(
                            color: btnBgColor,
                            borderRadius: BorderRadius.circular(20),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withValues(alpha: 0.2),
                                blurRadius: 6,
                                offset: const Offset(0, 2),
                              ),
                            ],
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Text(
                                ctaText,
                                style: TextStyle(
                                  color: btnTextColor,
                                  fontWeight: FontWeight.bold,
                                  fontSize: 11,
                                ),
                              ),
                              const SizedBox(width: 4),
                              Icon(Icons.arrow_forward_rounded, size: 12, color: btnTextColor),
                            ],
                          ),
                        ),
                      ),
                  ],
                )
              : Container(
                  decoration: BoxDecoration(gradient: bgGradient),
                  padding: const EdgeInsets.fromLTRB(16, 12, 16, 12),
                  child: Row(
                    crossAxisAlignment: CrossAxisAlignment.center,
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                              decoration: BoxDecoration(
                                color: Colors.white.withValues(alpha: 0.2),
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: const Text(
                                'SPECIAL NOTICE',
                                style: TextStyle(
                                  color: Colors.white,
                                  fontSize: 9,
                                  fontWeight: FontWeight.bold,
                                  letterSpacing: 0.5,
                                ),
                              ),
                            ),
                            const SizedBox(height: 6),
                            Text(
                              title,
                              style: TextStyle(
                                color: textColor,
                                fontWeight: FontWeight.bold,
                                fontSize: 14,
                                height: 1.2,
                              ),
                              maxLines: 2,
                              overflow: TextTheme.of(context).bodyMedium?.overflow ?? TextOverflow.ellipsis,
                            ),
                            if (description.isNotEmpty) ...[
                              const SizedBox(height: 4),
                              Text(
                                description,
                                style: TextStyle(
                                  color: textColor.withValues(alpha: 0.85),
                                  fontSize: 11,
                                  height: 1.2,
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ],
                          ],
                        ),
                      ),
                      if (ctaText != null && ctaText.isNotEmpty) ...[
                        const SizedBox(width: 10),
                        ElevatedButton(
                          style: ElevatedButton.styleFrom(
                            backgroundColor: btnBgColor,
                            foregroundColor: btnTextColor,
                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                            minimumSize: Size.zero,
                            tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(20),
                            ),
                            elevation: 2,
                          ),
                          onPressed: () => _onAdClicked(banner),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Text(
                                ctaText,
                                style: TextStyle(
                                  color: btnTextColor,
                                  fontWeight: FontWeight.bold,
                                  fontSize: 11,
                                ),
                              ),
                              const SizedBox(width: 4),
                              Icon(Icons.arrow_forward_rounded, size: 12, color: btnTextColor),
                            ],
                          ),
                        ),
                      ],
                    ],
                  ),
                ),
        ),
      ),
    );
  }
}
