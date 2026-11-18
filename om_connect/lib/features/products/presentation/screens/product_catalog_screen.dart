import 'dart:async';
import 'package:flutter/material.dart';
import '../../../../core/api/api_client.dart';
import '../../../../core/api/api_endpoints.dart';
import '../../../../core/config/app_config.dart';
import '../../../../core/constants/app_theme.dart';

class ProductCatalogScreen extends StatefulWidget {
  const ProductCatalogScreen({super.key});

  @override
  State<ProductCatalogScreen> createState() => _ProductCatalogScreenState();
}

class _ProductCatalogScreenState extends State<ProductCatalogScreen> {
  final ScrollController _scrollController = ScrollController();
  final TextEditingController _searchController = TextEditingController();
  Timer? _debounceTimer;

  bool _isSearchVisible = false;
  bool _isLoadingCategories = true;
  bool _isLoadingProducts = true;
  bool _isLoadingMore = false;
  bool _hasMore = true;
  int _currentPage = 1;
  String? _errorMessage;

  List<Map<String, dynamic>> _categories = [];
  int? _selectedCategoryId;
  String _searchQuery = '';
  List<Map<String, dynamic>> _products = [];

  String _resolveImageUrl(String? path) {
    if (path == null || path.isEmpty) return '';
    if (path.startsWith('http')) return path;
    final base = AppConfig.baseUrl.replaceAll('/api/v1', '');
    return '$base/storage/$path';
  }

  @override
  void initState() {
    super.initState();
    _scrollController.addListener(_onScroll);
    _loadCategories();
    _fetchProducts(reset: true);
  }

  @override
  void dispose() {
    _scrollController.dispose();
    _searchController.dispose();
    _debounceTimer?.cancel();
    super.dispose();
  }

  void _onScroll() {
    if (_scrollController.position.pixels >=
            _scrollController.position.maxScrollExtent - 200 &&
        !_isLoadingProducts &&
        !_isLoadingMore &&
        _hasMore) {
      _fetchProducts(reset: false);
    }
  }

  Future<void> _loadCategories() async {
    try {
      final response = await ApiClient.instance.get(
        ApiEndpoints.productCategories,
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
            _categories = list.whereType<Map<String, dynamic>>().toList();
            _isLoadingCategories = false;
          });
        }
      }
    } catch (_) {
      if (mounted) {
        setState(() => _isLoadingCategories = false);
      }
    }
  }

  Future<void> _fetchProducts({bool reset = false}) async {
    if (reset) {
      setState(() {
        _currentPage = 1;
        _hasMore = true;
        _isLoadingProducts = true;
        _errorMessage = null;
      });
    } else {
      setState(() {
        _isLoadingMore = true;
      });
    }

    try {
      final queryParams = <String, dynamic>{
        'page': _currentPage,
        'per_page': 20,
      };

      if (_searchQuery.trim().isNotEmpty) {
        queryParams['search'] = _searchQuery.trim();
      }

      if (_selectedCategoryId != null) {
        queryParams['category_id'] = _selectedCategoryId;
      }

      final response = await ApiClient.instance.get(
        ApiEndpoints.products,
        queryParameters: queryParams,
      );

      if (response.statusCode == 200 && response.data != null) {
        final data = response.data;
        List<dynamic> list = [];
        Map<String, dynamic>? meta;

        if (data is Map<String, dynamic>) {
          if (data['data'] is List) {
            list = data['data'] as List<dynamic>;
          }
          if (data['meta'] is Map<String, dynamic>) {
            meta = data['meta'] as Map<String, dynamic>;
          }
        } else if (data is List) {
          list = data;
        }

        final newItems = list.whereType<Map<String, dynamic>>().toList();

        // Check pagination limit
        bool hasMoreData = newItems.length >= 20;
        if (meta != null && meta.containsKey('last_page')) {
          final lastPage = (meta['last_page'] as num).toInt();
          hasMoreData = _currentPage < lastPage;
        }

        if (mounted) {
          setState(() {
            if (reset) {
              _products = newItems;
              _isLoadingProducts = false;
            } else {
              _products.addAll(newItems);
              _isLoadingMore = false;
            }
            _hasMore = hasMoreData;
            _currentPage++;
          });
        }
      } else {
        throw ApiException(message: 'Failed to retrieve products list.');
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _isLoadingProducts = false;
          _isLoadingMore = false;
          if (reset) {
            _errorMessage = e is ApiException
                ? e.message
                : 'Failed to load products. Please check your connection.';
          }
        });
      }
    }
  }

  void _onSearchChanged(String val) {
    _debounceTimer?.cancel();
    _debounceTimer = Timer(const Duration(milliseconds: 400), () {
      if (_searchQuery != val) {
        setState(() => _searchQuery = val);
        _fetchProducts(reset: true);
      }
    });
  }

  void _onCategorySelected(int? categoryId) {
    if (_selectedCategoryId == categoryId) return;
    setState(() {
      _selectedCategoryId = categoryId;
    });
    _fetchProducts(reset: true);
  }

  void _showProductDetails(Map<String, dynamic> product) {
    final brandName = product['brand_name'] ?? 'Unnamed Product';
    final genericName = product['generic_name'] ?? '';
    final strength = product['strength']?.toString() ?? '';
    final dosage = product['dosage_form']?.toString() ?? '';
    final packSize = product['pack_size']?.toString() ?? '';
    final mrp = product['mrp']?.toString() ?? '0.00';
    final sku = product['sku']?.toString() ?? '';
    final description = product['description']?.toString() ?? 'No description provided.';
    final categoryName = product['category'] is Map<String, dynamic>
        ? product['category']['name']
        : null;
    final imageUrl = product['image']?.toString();

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) {
        return DraggableScrollableSheet(
          initialChildSize: 0.7,
          minChildSize: 0.45,
          maxChildSize: 0.9,
          builder: (_, sheetScrollController) {
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
                      controller: sheetScrollController,
                      padding: const EdgeInsets.fromLTRB(24, 8, 24, 24),
                      children: [
                        // Image or Placeholder
                        Center(
                          child: Container(
                            height: 140,
                            width: 140,
                            decoration: BoxDecoration(
                              color: AppColors.primary.withValues(alpha: 0.06),
                              borderRadius: BorderRadius.circular(20),
                              border: Border.all(color: AppColors.border),
                            ),
                            child: (imageUrl != null && imageUrl.isNotEmpty)
                                ? ClipRRect(
                                    borderRadius: BorderRadius.circular(20),
                                      child: Image.network(
                                        _resolveImageUrl(imageUrl),
                                        fit: BoxFit.cover,
                                      errorBuilder: (_, __, ___) => const Icon(
                                        Icons.medication_rounded,
                                        size: 64,
                                        color: AppColors.primary,
                                      ),
                                    ),
                                  )
                                : const Icon(
                                    Icons.medication_rounded,
                                    size: 64,
                                    color: AppColors.primary,
                                  ),
                          ),
                        ),
                        const SizedBox(height: 20),

                        // Category Badge
                        if (categoryName != null)
                          Align(
                            alignment: Alignment.centerLeft,
                            child: Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 10,
                                vertical: 4,
                              ),
                              decoration: BoxDecoration(
                                color: AppColors.primaryLight.withValues(alpha: 0.12),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Text(
                                categoryName,
                                style: const TextStyle(
                                  color: AppColors.primaryLight,
                                  fontSize: 12,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ),
                          ),
                        const SizedBox(height: 8),

                        // Brand Name
                        Text(
                          brandName,
                          style: const TextStyle(
                            fontSize: 22,
                            fontWeight: FontWeight.bold,
                            color: AppColors.textPrimary,
                          ),
                        ),
                        if (genericName.isNotEmpty) ...[
                          const SizedBox(height: 4),
                          Text(
                            genericName,
                            style: const TextStyle(
                              fontSize: 15,
                              fontStyle: FontStyle.italic,
                              color: AppColors.textSecondary,
                            ),
                          ),
                        ],
                        const SizedBox(height: 16),

                        // Key Specs Box
                        Container(
                          padding: const EdgeInsets.all(16),
                          decoration: BoxDecoration(
                            color: AppColors.surface,
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(color: AppColors.border),
                          ),
                          child: Column(
                            children: [
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  const Text('Maximum Retail Price (MRP):',
                                      style: TextStyle(fontSize: 14, color: AppColors.textSecondary)),
                                  Text(
                                    'NPR $mrp',
                                    style: const TextStyle(
                                      fontSize: 17,
                                      fontWeight: FontWeight.bold,
                                      color: AppColors.secondary,
                                    ),
                                  ),
                                ],
                              ),
                              const Divider(height: 20),
                              if (strength.isNotEmpty) ...[
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    const Text('Strength:',
                                        style: TextStyle(fontSize: 13, color: AppColors.textSecondary)),
                                    Text(strength,
                                        style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                                  ],
                                ),
                                const SizedBox(height: 8),
                              ],
                              if (dosage.isNotEmpty) ...[
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    const Text('Dosage Form:',
                                        style: TextStyle(fontSize: 13, color: AppColors.textSecondary)),
                                    Text(dosage,
                                        style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                                  ],
                                ),
                                const SizedBox(height: 8),
                              ],
                              if (packSize.isNotEmpty) ...[
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    const Text('Pack Size:',
                                        style: TextStyle(fontSize: 13, color: AppColors.textSecondary)),
                                    Text(packSize,
                                        style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                                  ],
                                ),
                                const SizedBox(height: 8),
                              ],
                              if (sku.isNotEmpty) ...[
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    const Text('SKU Code:',
                                        style: TextStyle(fontSize: 13, color: AppColors.textSecondary)),
                                    Text(sku,
                                        style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                                  ],
                                ),
                              ],
                            ],
                          ),
                        ),
                        const SizedBox(height: 20),

                        // Description
                        const Text(
                          'Product Description',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                            color: AppColors.textPrimary,
                          ),
                        ),
                        const SizedBox(height: 8),
                        Text(
                          description,
                          style: const TextStyle(
                            fontSize: 14,
                            height: 1.5,
                            color: AppColors.textPrimary,
                          ),
                        ),
                        const SizedBox(height: 28),

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
        title: _isSearchVisible
            ? TextField(
                controller: _searchController,
                autofocus: true,
                style: const TextStyle(color: Colors.white, fontSize: 16),
                cursorColor: Colors.white,
                decoration: const InputDecoration(
                  hintText: 'Search brand, generic, or SKU...',
                  hintStyle: TextStyle(color: Colors.white70),
                  border: InputBorder.none,
                  enabledBorder: InputBorder.none,
                  focusedBorder: InputBorder.none,
                  fillColor: Colors.transparent,
                  filled: false,
                  contentPadding: EdgeInsets.zero,
                ),
                onChanged: _onSearchChanged,
              )
            : const Text('Products'),
        actions: [
          IconButton(
            icon: Icon(_isSearchVisible ? Icons.close_rounded : Icons.search_rounded),
            onPressed: () {
              setState(() {
                if (_isSearchVisible) {
                  _isSearchVisible = false;
                  _searchController.clear();
                  _searchQuery = '';
                  _fetchProducts(reset: true);
                } else {
                  _isSearchVisible = true;
                }
              });
            },
          ),
        ],
      ),
      body: Column(
        children: [
          // Category filter chips
          Container(
            color: Colors.white,
            padding: const EdgeInsets.symmetric(vertical: 10),
            child: SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: Row(
                children: [
                  Padding(
                    padding: const EdgeInsets.only(right: 8),
                    child: FilterChip(
                      label: const Text('All Products'),
                      selected: _selectedCategoryId == null,
                      onSelected: (_) => _onCategorySelected(null),
                      selectedColor: AppColors.primary,
                      checkmarkColor: Colors.white,
                      backgroundColor: AppColors.surface,
                      labelStyle: TextStyle(
                        color: _selectedCategoryId == null
                            ? Colors.white
                            : AppColors.textPrimary,
                        fontWeight: _selectedCategoryId == null
                            ? FontWeight.bold
                            : FontWeight.w500,
                        fontSize: 13,
                      ),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(20),
                        side: BorderSide(
                          color: _selectedCategoryId == null
                              ? AppColors.primary
                              : AppColors.border,
                        ),
                      ),
                    ),
                  ),
                  if (_isLoadingCategories)
                    const Padding(
                      padding: EdgeInsets.symmetric(horizontal: 12),
                      child: SizedBox(
                        width: 16,
                        height: 16,
                        child: CircularProgressIndicator(strokeWidth: 2),
                      ),
                    )
                  else
                    ..._categories.map((category) {
                      final id = category['id'] as int;
                      final name = category['name']?.toString() ?? 'Category';
                      final isSelected = _selectedCategoryId == id;

                    return Padding(
                      padding: const EdgeInsets.only(right: 8),
                      child: FilterChip(
                        label: Text(name),
                        selected: isSelected,
                        onSelected: (_) => _onCategorySelected(id),
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
                      ),
                    );
                  }),
                ],
              ),
            ),
          ),
          const Divider(height: 1, thickness: 1, color: AppColors.border),

          // Product Grid
          Expanded(
            child: RefreshIndicator(
              onRefresh: () => _fetchProducts(reset: true),
              color: AppColors.primary,
              child: _isLoadingProducts
                  ? const Center(child: CircularProgressIndicator())
                  : _errorMessage != null && _products.isEmpty
                      ? _buildErrorView()
                      : _products.isEmpty
                          ? _buildEmptyState()
                          : _buildProductGrid(),
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
          _errorMessage ?? 'Failed to load products.',
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
            onPressed: () => _fetchProducts(reset: true),
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
              Icons.medication_liquid_outlined,
              size: 48,
              color: AppColors.primary,
            ),
          ),
        ),
        const SizedBox(height: 24),
        const Text(
          'No Products Found',
          textAlign: TextAlign.center,
          style: TextStyle(
            fontSize: 20,
            fontWeight: FontWeight.bold,
            color: AppColors.textPrimary,
          ),
        ),
        const SizedBox(height: 8),
        Text(
          _searchQuery.isNotEmpty
              ? 'No products matched "$_searchQuery". Try different keywords.'
              : 'There are no active products in this category.',
          textAlign: TextAlign.center,
          style: const TextStyle(
            fontSize: 14,
            color: AppColors.textSecondary,
          ),
        ),
      ],
    );
  }

  Widget _buildProductGrid() {
    return CustomScrollView(
      controller: _scrollController,
      physics: const AlwaysScrollableScrollPhysics(),
      slivers: [
        SliverPadding(
          padding: const EdgeInsets.all(16),
          sliver: SliverGrid(
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 2,
              childAspectRatio: 0.72,
              crossAxisSpacing: 14,
              mainAxisSpacing: 14,
            ),
            delegate: SliverChildBuilderDelegate(
              (context, index) {
                final product = _products[index];
                return _buildProductCard(product);
              },
              childCount: _products.length,
            ),
          ),
        ),
        if (_isLoadingMore)
          const SliverToBoxAdapter(
            child: Padding(
              padding: EdgeInsets.symmetric(vertical: 20),
              child: Center(
                child: SizedBox(
                  width: 24,
                  height: 24,
                  child: CircularProgressIndicator(strokeWidth: 2),
                ),
              ),
            ),
          ),
      ],
    );
  }

  Widget _buildProductCard(Map<String, dynamic> product) {
    final brandName = product['brand_name'] ?? 'Product';
    final genericName = product['generic_name'] ?? '';
    final mrp = product['mrp']?.toString() ?? '0.00';
    final imageUrl = product['image']?.toString();

    return Card(
      elevation: 0.8,
      margin: EdgeInsets.zero,
      color: Colors.white,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
        side: const BorderSide(color: AppColors.border, width: 0.6),
      ),
      child: InkWell(
        onTap: () => _showProductDetails(product),
        borderRadius: BorderRadius.circular(16),
        child: Padding(
          padding: const EdgeInsets.all(12),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Product Image Placeholder
              Expanded(
                child: Container(
                  width: double.infinity,
                  decoration: BoxDecoration(
                    color: AppColors.primary.withValues(alpha: 0.05),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: (imageUrl != null && imageUrl.isNotEmpty)
                      ? ClipRRect(
                          borderRadius: BorderRadius.circular(12),
                           child: Image.network(
                             _resolveImageUrl(imageUrl),
                             fit: BoxFit.cover,
                            errorBuilder: (_, __, ___) => const Center(
                              child: Icon(
                                Icons.medication_rounded,
                                size: 40,
                                color: AppColors.primary,
                              ),
                            ),
                          ),
                        )
                      : const Center(
                          child: Icon(
                            Icons.medication_rounded,
                            size: 40,
                            color: AppColors.primary,
                          ),
                        ),
                ),
              ),
              const SizedBox(height: 10),

              // Brand Name
              Text(
                brandName,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.bold,
                  color: AppColors.textPrimary,
                ),
              ),
              const SizedBox(height: 2),

              // Generic Name
              Text(
                genericName,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(
                  fontSize: 12,
                  fontStyle: FontStyle.italic,
                  color: AppColors.textSecondary,
                ),
              ),
              const SizedBox(height: 6),

              // Price Row
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    'MRP',
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w600,
                      color: AppColors.textSecondary,
                    ),
                  ),
                  Text(
                    'Rs. $mrp',
                    style: const TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.bold,
                      color: AppColors.secondary,
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}
