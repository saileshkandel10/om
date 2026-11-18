import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/api/api_client.dart';
import '../../../../core/api/api_endpoints.dart';
import '../../../../core/constants/app_theme.dart';
import 'active_quiz_screen.dart';
import 'quiz_result_screen.dart';

class PharmacistQuizScreen extends ConsumerStatefulWidget {
  const PharmacistQuizScreen({super.key});

  @override
  ConsumerState<PharmacistQuizScreen> createState() => _PharmacistQuizScreenState();
}

class _PharmacistQuizScreenState extends ConsumerState<PharmacistQuizScreen> {
  bool _isLoading = true;
  String? _errorMessage;

  Map<String, dynamic>? _quizSettings;
  List<Map<String, dynamic>> _quizzes = [];
  List<Map<String, dynamic>> _categories = [];
  List<Map<String, dynamic>> _myAttempts = [];

  int _selectedTabIndex = 0; // 0: Available Quizzes, 1: My Attempts
  int? _selectedCategoryId;

  @override
  void initState() {
    super.initState();
    _fetchQuizData();
  }

  Future<void> _fetchQuizData() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final results = await Future.wait([
        ApiClient.instance.get(ApiEndpoints.pharmacyQuizzes, queryParameters: {
          if (_selectedCategoryId != null) 'category_id': _selectedCategoryId,
        }),
        ApiClient.instance.get(ApiEndpoints.pharmacyQuizCategories),
        ApiClient.instance.get(ApiEndpoints.pharmacyQuizMyAttempts),
        ApiClient.instance.get(ApiEndpoints.pharmacyQuizSettings),
      ]);

      if (mounted) {
        setState(() {
          final qRes = results[0];
          if (qRes.statusCode == 200 && qRes.data != null) {
            final list = qRes.data['data'] is List ? qRes.data['data'] as List : [];
            _quizzes = list.whereType<Map<String, dynamic>>().toList();
          }

          final cRes = results[1];
          if (cRes.statusCode == 200 && cRes.data != null) {
            final list = cRes.data['data'] is List ? cRes.data['data'] as List : [];
            _categories = list.whereType<Map<String, dynamic>>().toList();
          }

          final aRes = results[2];
          if (aRes.statusCode == 200 && aRes.data != null) {
            final list = aRes.data['data'] is List ? aRes.data['data'] as List : [];
            _myAttempts = list.whereType<Map<String, dynamic>>().toList();
          }

          final sRes = results[3];
          if (sRes.statusCode == 200 && sRes.data != null) {
            _quizSettings = sRes.data['settings'] as Map<String, dynamic>?;
          }

          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _isLoading = false;
          _errorMessage = e is ApiException ? e.message : 'Unable to load quizzes. Please check connection.';
        });
      }
    }
  }

  void _showQuizInstructionsDialog(Map<String, dynamic> quiz) {
    final title = quiz['title']?.toString() ?? 'Pharmacy Quiz';
    final description = quiz['description']?.toString() ?? '';
    final categoryName = quiz['category']?['name']?.toString() ?? 'Clinical Pharmacology';
    final timeLimit = quiz['time_limit_minutes']?.toString() ?? (_quizSettings?['time_limit_minutes']?.toString() ?? '10');
    final passingPercentage = quiz['passing_percentage']?.toString() ?? (_quizSettings?['passing_percentage']?.toString() ?? '70');
    final rewardPoints = quiz['reward_points']?.toString() ?? (_quizSettings?['reward_points']?.toString() ?? '50');
    final questionsCount = quiz['questions_count']?.toString() ?? (_quizSettings?['questions_per_quiz']?.toString() ?? '10');
    final instructions = quiz['instructions']?.toString() ?? (_quizSettings?['instructions']?.toString() ?? 'Complete all questions before the countdown timer expires. Each correct answer earns points towards your pass score.');

    final userStats = quiz['user_stats'] as Map<String, dynamic>? ?? {};
    final attemptsCount = userStats['attempts_count']?.toString() ?? '0';
    final maxAttempts = userStats['max_attempts']?.toString() ?? (_quizSettings?['max_attempts']?.toString() ?? '3');

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => Container(
        padding: const EdgeInsets.all(24),
        decoration: const BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Center(
              child: Container(
                width: 40,
                height: 4,
                decoration: BoxDecoration(color: Colors.grey.shade300, borderRadius: BorderRadius.circular(2)),
              ),
            ),
            const SizedBox(height: 16),
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: AppColors.primary.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    categoryName.toUpperCase(),
                    style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: AppColors.primary),
                  ),
                ),
                const Spacer(),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: Colors.amber.shade50,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: Colors.amber.shade200),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.stars_rounded, color: Colors.amber, size: 16),
                      const SizedBox(width: 4),
                      Text('+$rewardPoints pts', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.amber.shade900)),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            Text(
              title,
              style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
            ),
            if (description.isNotEmpty) ...[
              const SizedBox(height: 6),
              Text(
                description,
                style: const TextStyle(fontSize: 13, color: AppColors.textSecondary, height: 1.3),
              ),
            ],
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: Colors.grey.shade50,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: Colors.grey.shade200),
              ),
              child: Column(
                children: [
                  _buildRuleRow(Icons.timer_outlined, 'Time Allotted:', '$timeLimit Minutes countdown'),
                  const SizedBox(height: 8),
                  _buildRuleRow(Icons.help_outline_rounded, 'Total Questions:', '$questionsCount Multiple Choice Questions'),
                  const SizedBox(height: 8),
                  _buildRuleRow(Icons.check_circle_outline, 'Passing Requirement:', '$passingPercentage% Score or higher'),
                  const SizedBox(height: 8),
                  _buildRuleRow(Icons.repeat_rounded, 'Attempt Quota:', '$attemptsCount / $maxAttempts attempts taken'),
                ],
              ),
            ),
            const SizedBox(height: 14),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: AppColors.primary.withValues(alpha: 0.05),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: AppColors.primary.withValues(alpha: 0.15)),
              ),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Icon(Icons.info_outline, color: AppColors.primary, size: 18),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      instructions,
                      style: const TextStyle(fontSize: 12, color: AppColors.textPrimary, height: 1.3),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.primary,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  elevation: 2,
                ),
                onPressed: () {
                  Navigator.pop(ctx);
                  _startQuiz(quiz);
                },
                icon: const Icon(Icons.play_arrow_rounded, size: 20),
                label: const Text('Start Quiz Assessment', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildRuleRow(IconData icon, String label, String value) {
    return Row(
      children: [
        Icon(icon, size: 16, color: AppColors.primary),
        const SizedBox(width: 8),
        Text(label, style: const TextStyle(fontSize: 12, color: AppColors.textSecondary, fontWeight: FontWeight.w500)),
        const Spacer(),
        Text(value, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
      ],
    );
  }

  void _startQuiz(Map<String, dynamic> quiz) async {
    final quizId = quiz['id'];
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => const Center(child: CircularProgressIndicator()),
    );

    try {
      final response = await ApiClient.instance.post(ApiEndpoints.pharmacyQuizStart(quizId));
      if (mounted) {
        Navigator.pop(context); // close loading
      }

      if (response.statusCode == 200 || response.statusCode == 201) {
        final attemptData = response.data['attempt'] as Map<String, dynamic>;
        if (mounted) {
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (_) => ActiveQuizScreen(
                attemptData: attemptData,
                quizSettings: _quizSettings,
                onQuizFinished: _fetchQuizData,
              ),
            ),
          );
        }
      } else {
        final msg = response.data?['message'] ?? 'Could not start quiz.';
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text(msg), backgroundColor: AppColors.danger),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        Navigator.pop(context); // close loading
        final msg = e is ApiException ? e.message : 'Failed to start quiz attempt.';
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(msg), backgroundColor: AppColors.danger),
        );
      }
    }
  }

  void _viewPastAttemptDetails(Map<String, dynamic> attempt) async {
    final attemptId = attempt['id'];
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => const Center(child: CircularProgressIndicator()),
    );

    try {
      final response = await ApiClient.instance.get(ApiEndpoints.pharmacyQuizAttemptDetail(attemptId));
      if (mounted) {
        Navigator.pop(context);
      }

      if (response.statusCode == 200 && response.data != null) {
        final attemptDetails = response.data['attempt'] as Map<String, dynamic>;
        final quizTitle = attemptDetails['quiz_title']?.toString() ?? 'Pharmacy Quiz';

        if (mounted) {
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (_) => QuizResultScreen(
                resultData: {
                  'is_passed': attemptDetails['is_passed'],
                  'reward_points_awarded': attemptDetails['reward_points_awarded'],
                  'attempt': attemptDetails,
                },
                quizTitle: quizTitle,
              ),
            ),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        Navigator.pop(context);
        final msg = e is ApiException ? e.message : 'Could not load attempt details.';
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(msg), backgroundColor: AppColors.danger),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final isModuleActive = _quizSettings?['is_currently_active'] as bool? ?? true;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Pharmacist Quiz & Education', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 17)),
        backgroundColor: AppColors.primary,
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _fetchQuizData,
              child: CustomScrollView(
                slivers: [
                  // Hero Header Banner
                  SliverToBoxAdapter(
                    child: Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(20),
                      decoration: const BoxDecoration(
                        gradient: AppColors.brandGradient,
                        borderRadius: BorderRadius.only(
                          bottomLeft: Radius.circular(24),
                          bottomRight: Radius.circular(24),
                        ),
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
                                    Icon(Icons.school, color: AppColors.accent, size: 16),
                                    SizedBox(width: 4),
                                    Text(
                                      'Continuous Pharmacy Education',
                                      style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                                    ),
                                  ],
                                ),
                              ),
                              const Icon(Icons.psychology_rounded, color: Colors.white70, size: 28),
                            ],
                          ),
                          const SizedBox(height: 12),
                          const Text(
                            'Pharmacist Knowledge Hub',
                            style: TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.bold),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            _quizSettings?['instructions']?.toString() ??
                                'Sharpen your pharmacology expertise, test clinical standards, and earn bonus loyalty reward points.',
                            style: const TextStyle(color: Colors.white70, fontSize: 12, height: 1.3),
                          ),
                        ],
                      ),
                    ),
                  ),

                  // Inactive Module Notice
                  if (!isModuleActive)
                    SliverToBoxAdapter(
                      child: Padding(
                        padding: const EdgeInsets.all(24),
                        child: Card(
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                          color: Colors.white,
                          elevation: 1,
                          child: Padding(
                            padding: const EdgeInsets.all(32),
                            child: Column(
                              children: [
                                Container(
                                  padding: const EdgeInsets.all(16),
                                  decoration: BoxDecoration(
                                    color: Colors.amber.shade50,
                                    shape: BoxShape.circle,
                                  ),
                                  child: Icon(Icons.hourglass_empty_rounded, size: 48, color: Colors.amber.shade700),
                                ),
                                const SizedBox(height: 16),
                                const Text(
                                  'Quiz Module Off-Schedule',
                                  style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                                ),
                                const SizedBox(height: 8),
                                Text(
                                  _quizSettings?['announcement']?.toString() ??
                                      'The pharmacist quiz module is currently inactive or off schedule. Please check back later when new assessments are released.',
                                  textAlign: TextAlign.center,
                                  style: const TextStyle(fontSize: 13, color: AppColors.textSecondary, height: 1.4),
                                ),
                                if (_quizSettings?['start_date'] != null) ...[
                                  const SizedBox(height: 12),
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                                    decoration: BoxDecoration(
                                      color: Colors.grey.shade100,
                                      borderRadius: BorderRadius.circular(8),
                                    ),
                                    child: Text(
                                      'Scheduled: ${_quizSettings!['start_date']} to ${_quizSettings?['end_date'] ?? 'TBD'}',
                                      style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600),
                                    ),
                                  ),
                                ],
                              ],
                            ),
                          ),
                        ),
                      ),
                    ),

                  // Error Notice
                  if (_errorMessage != null)
                    SliverToBoxAdapter(
                      child: Container(
                        margin: const EdgeInsets.all(16),
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: AppColors.danger.withValues(alpha: 0.1),
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: AppColors.danger.withValues(alpha: 0.3)),
                        ),
                        child: Row(
                          children: [
                            const Icon(Icons.error_outline, color: AppColors.danger, size: 20),
                            const SizedBox(width: 8),
                            Expanded(child: Text(_errorMessage!, style: const TextStyle(fontSize: 12, color: AppColors.danger))),
                            TextButton(onPressed: _fetchQuizData, child: const Text('Retry', style: TextStyle(fontSize: 12))),
                          ],
                        ),
                      ),
                    ),

                  if (isModuleActive) ...[
                    // Segmented Tabs: Available Quizzes vs My Attempts
                    SliverToBoxAdapter(
                      child: Padding(
                        padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
                        child: Container(
                          decoration: BoxDecoration(
                            color: Colors.grey.shade100,
                            borderRadius: BorderRadius.circular(12),
                          ),
                          padding: const EdgeInsets.all(4),
                          child: Row(
                            children: [
                              Expanded(
                                child: GestureDetector(
                                  onTap: () => setState(() => _selectedTabIndex = 0),
                                  child: Container(
                                    padding: const EdgeInsets.symmetric(vertical: 10),
                                    decoration: BoxDecoration(
                                      color: _selectedTabIndex == 0 ? Colors.white : Colors.transparent,
                                      borderRadius: BorderRadius.circular(10),
                                      boxShadow: _selectedTabIndex == 0
                                          ? [BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 4, offset: const Offset(0, 2))]
                                          : null,
                                    ),
                                    child: Row(
                                      mainAxisAlignment: MainAxisAlignment.center,
                                      children: [
                                        Icon(Icons.quiz_rounded, size: 16, color: _selectedTabIndex == 0 ? AppColors.primary : AppColors.textSecondary),
                                        const SizedBox(width: 6),
                                        Text(
                                          'Available Quizzes (${_quizzes.length})',
                                          style: TextStyle(
                                            fontSize: 12,
                                            fontWeight: _selectedTabIndex == 0 ? FontWeight.bold : FontWeight.w500,
                                            color: _selectedTabIndex == 0 ? AppColors.primary : AppColors.textSecondary,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                ),
                              ),
                              Expanded(
                                child: GestureDetector(
                                  onTap: () => setState(() => _selectedTabIndex = 1),
                                  child: Container(
                                    padding: const EdgeInsets.symmetric(vertical: 10),
                                    decoration: BoxDecoration(
                                      color: _selectedTabIndex == 1 ? Colors.white : Colors.transparent,
                                      borderRadius: BorderRadius.circular(10),
                                      boxShadow: _selectedTabIndex == 1
                                          ? [BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 4, offset: const Offset(0, 2))]
                                          : null,
                                    ),
                                    child: Row(
                                      mainAxisAlignment: MainAxisAlignment.center,
                                      children: [
                                        Icon(Icons.history_edu_rounded, size: 16, color: _selectedTabIndex == 1 ? AppColors.primary : AppColors.textSecondary),
                                        const SizedBox(width: 6),
                                        Text(
                                          'My History (${_myAttempts.length})',
                                          style: TextStyle(
                                            fontSize: 12,
                                            fontWeight: _selectedTabIndex == 1 ? FontWeight.bold : FontWeight.w500,
                                            color: _selectedTabIndex == 1 ? AppColors.primary : AppColors.textSecondary,
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

                    // Categories Filter Chips
                    if (_selectedTabIndex == 0 && _categories.isNotEmpty)
                      SliverToBoxAdapter(
                        child: SizedBox(
                          height: 48,
                          child: ListView(
                            scrollDirection: Axis.horizontal,
                            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
                            children: [
                              ChoiceChip(
                                label: const Text('All Topics', style: TextStyle(fontSize: 11)),
                                selected: _selectedCategoryId == null,
                                selectedColor: AppColors.primary.withValues(alpha: 0.15),
                                labelStyle: TextStyle(
                                  color: _selectedCategoryId == null ? AppColors.primary : AppColors.textSecondary,
                                  fontWeight: _selectedCategoryId == null ? FontWeight.bold : FontWeight.normal,
                                ),
                                onSelected: (_) {
                                  setState(() => _selectedCategoryId = null);
                                  _fetchQuizData();
                                },
                              ),
                              const SizedBox(width: 8),
                              ..._categories.map((cat) {
                                final isSel = _selectedCategoryId == cat['id'];
                                return Padding(
                                  padding: const EdgeInsets.only(right: 8),
                                  child: ChoiceChip(
                                    label: Text(cat['name']?.toString() ?? '', style: const TextStyle(fontSize: 11)),
                                    selected: isSel,
                                    selectedColor: AppColors.primary.withValues(alpha: 0.15),
                                    labelStyle: TextStyle(
                                      color: isSel ? AppColors.primary : AppColors.textSecondary,
                                      fontWeight: isSel ? FontWeight.bold : FontWeight.normal,
                                    ),
                                    onSelected: (_) {
                                      setState(() => _selectedCategoryId = isSel ? null : cat['id']);
                                      _fetchQuizData();
                                    },
                                  ),
                                );
                              }),
                            ],
                          ),
                        ),
                      ),

                    // Available Quizzes List
                    if (_selectedTabIndex == 0)
                      _quizzes.isEmpty
                          ? SliverToBoxAdapter(
                              child: Padding(
                                padding: const EdgeInsets.all(32),
                                child: Column(
                                  children: [
                                    Icon(Icons.assignment_turned_in_outlined, size: 64, color: Colors.grey.shade400),
                                    const SizedBox(height: 12),
                                    const Text(
                                      'No Active Quizzes Available',
                                      style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                                    ),
                                    const SizedBox(height: 6),
                                    const Text(
                                      'New clinical pharmacology and regulatory quizzes will be posted here soon. Check back later!',
                                      textAlign: TextAlign.center,
                                      style: TextStyle(fontSize: 13, color: AppColors.textSecondary),
                                    ),
                                  ],
                                ),
                              ),
                            )
                          : SliverPadding(
                              padding: const EdgeInsets.fromLTRB(16, 8, 16, 32),
                              sliver: SliverList(
                                delegate: SliverChildBuilderDelegate(
                                  (context, index) => _buildQuizCard(_quizzes[index]),
                                  childCount: _quizzes.length,
                                ),
                              ),
                            )
                    else
                      _myAttempts.isEmpty
                          ? SliverToBoxAdapter(
                              child: Padding(
                                padding: const EdgeInsets.all(32),
                                child: Column(
                                  children: [
                                    Icon(Icons.history_edu_outlined, size: 64, color: Colors.grey.shade400),
                                    const SizedBox(height: 12),
                                    const Text(
                                      'No Quiz Attempts Yet',
                                      style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                                    ),
                                    const SizedBox(height: 6),
                                    const Text(
                                      'Take an active quiz from the Available Quizzes tab to test your knowledge and record your score history.',
                                      textAlign: TextAlign.center,
                                      style: TextStyle(fontSize: 13, color: AppColors.textSecondary),
                                    ),
                                  ],
                                ),
                              ),
                            )
                          : SliverPadding(
                              padding: const EdgeInsets.fromLTRB(16, 8, 16, 32),
                              sliver: SliverList(
                                delegate: SliverChildBuilderDelegate(
                                  (context, index) => _buildAttemptCard(_myAttempts[index]),
                                  childCount: _myAttempts.length,
                                ),
                              ),
                            ),
                  ],
                ],
              ),
            ),
    );
  }

  Widget _buildQuizCard(Map<String, dynamic> quiz) {
    final title = quiz['title']?.toString() ?? 'Pharmacy Quiz';
    final description = quiz['description']?.toString() ?? '';
    final categoryName = quiz['category']?['name']?.toString() ?? 'Clinical';
    final questionsCount = quiz['questions_count']?.toString() ?? (_quizSettings?['questions_per_quiz']?.toString() ?? '10');
    final timeLimit = quiz['time_limit_minutes']?.toString() ?? (_quizSettings?['time_limit_minutes']?.toString() ?? '10');
    final rewardPoints = quiz['reward_points']?.toString() ?? (_quizSettings?['reward_points']?.toString() ?? '50');
    final passingPercentage = quiz['passing_percentage']?.toString() ?? (_quizSettings?['passing_percentage']?.toString() ?? '70');

    final userStats = quiz['user_stats'] as Map<String, dynamic>? ?? {};
    final canAttempt = userStats['can_attempt'] as bool? ?? true;
    final isPassed = userStats['is_passed'] as bool? ?? false;

    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      elevation: 2,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
        side: BorderSide(color: isPassed ? Colors.green.shade200 : AppColors.border),
      ),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: AppColors.primary.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    categoryName.toUpperCase(),
                    style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: AppColors.primary),
                  ),
                ),
                const Spacer(),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: Colors.amber.shade50,
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: Colors.amber.shade200),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Icon(Icons.stars_rounded, size: 14, color: Colors.amber),
                      const SizedBox(width: 4),
                      Text(
                        '+$rewardPoints pts',
                        style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.amber.shade900),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 10),
            Text(
              title,
              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
            ),
            if (description.isNotEmpty) ...[
              const SizedBox(height: 4),
              Text(
                description,
                style: const TextStyle(fontSize: 12, color: AppColors.textSecondary, height: 1.3),
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
              ),
            ],
            const SizedBox(height: 12),
            Row(
              children: [
                _buildQuizMetaTag(Icons.help_outline_rounded, '$questionsCount Questions'),
                const SizedBox(width: 12),
                _buildQuizMetaTag(Icons.timer_outlined, '$timeLimit Mins'),
                const SizedBox(width: 12),
                _buildQuizMetaTag(Icons.check_circle_outline, 'Pass: $passingPercentage%'),
              ],
            ),
            const SizedBox(height: 14),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                if (isPassed)
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                    decoration: BoxDecoration(
                      color: Colors.green.shade50,
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Row(
                      children: [
                        Icon(Icons.verified, size: 16, color: Colors.green.shade700),
                        const SizedBox(width: 4),
                        Text('Passed', style: TextStyle(color: Colors.green.shade800, fontSize: 12, fontWeight: FontWeight.bold)),
                      ],
                    ),
                  )
                else
                  const SizedBox.shrink(),
                ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: canAttempt ? AppColors.primary : Colors.grey.shade400,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  onPressed: canAttempt ? () => _showQuizInstructionsDialog(quiz) : null,
                  child: Text(
                    canAttempt ? (isPassed ? 'Retake Quiz' : 'Start Quiz') : 'Max Attempts Reached',
                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildAttemptCard(Map<String, dynamic> attempt) {
    final quizTitle = attempt['quiz_title'] ?? attempt['quiz']?['title'] ?? 'Pharmacy Quiz';
    final categoryName = attempt['category_name'] ?? attempt['quiz']?['category']?['name'] ?? 'General';
    final attemptNumber = attempt['attempt_number']?.toString() ?? '1';
    final percentage = attempt['percentage']?.toString() ?? '0.00';
    final isPassed = attempt['is_passed'] as bool? ?? false;
    final rewardPoints = attempt['reward_points_awarded']?.toString() ?? '0';
    final score = attempt['score']?.toString() ?? '0';
    final totalPoints = attempt['total_points']?.toString() ?? '0';
    final timeTaken = attempt['time_taken_formatted']?.toString();
    final dateStr = attempt['completed_at'] ?? attempt['started_at'];

    return Card(
      margin: const EdgeInsets.only(bottom: 10),
      elevation: 1,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
        side: BorderSide(color: isPassed ? Colors.green.shade200 : Colors.red.shade100),
      ),
      child: InkWell(
        onTap: () => _viewPastAttemptDetails(attempt),
        borderRadius: BorderRadius.circular(16),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                    decoration: BoxDecoration(
                      color: AppColors.primary.withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Text(
                      categoryName.toUpperCase(),
                      style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: AppColors.primary),
                    ),
                  ),
                  const SizedBox(width: 6),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 3),
                    decoration: BoxDecoration(
                      color: Colors.grey.shade100,
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Text(
                      'Attempt #$attemptNumber',
                      style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: AppColors.textSecondary),
                    ),
                  ),
                  const Spacer(),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: isPassed ? Colors.green.shade50 : Colors.red.shade50,
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Text(
                      isPassed ? 'PASSED' : 'FAILED',
                      style: TextStyle(
                        fontSize: 10,
                        fontWeight: FontWeight.bold,
                        color: isPassed ? Colors.green.shade800 : Colors.red.shade800,
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 10),
              Text(
                quizTitle,
                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: AppColors.textPrimary),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
              const SizedBox(height: 8),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text('Score: $score / $totalPoints ($percentage%)', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
                  if (double.tryParse(rewardPoints) != null && double.parse(rewardPoints) > 0)
                    Text('+$rewardPoints pts', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.green)),
                ],
              ),
              const SizedBox(height: 6),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Row(
                    children: [
                      if (timeTaken != null && timeTaken.isNotEmpty) ...[
                        Icon(Icons.timer_outlined, size: 12, color: Colors.grey.shade600),
                        const SizedBox(width: 3),
                        Text(timeTaken, style: TextStyle(fontSize: 11, color: Colors.grey.shade600)),
                        const SizedBox(width: 8),
                      ],
                      if (dateStr != null) ...[
                        Icon(Icons.calendar_today_outlined, size: 12, color: Colors.grey.shade600),
                        const SizedBox(width: 3),
                        Text(
                          dateStr.toString().split('T').first,
                          style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
                        ),
                      ],
                    ],
                  ),
                  const Text('Review answers >', style: TextStyle(fontSize: 11, color: AppColors.primary, fontWeight: FontWeight.bold)),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildQuizMetaTag(IconData icon, String text) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(icon, size: 14, color: AppColors.textSecondary),
        const SizedBox(width: 4),
        Text(text, style: const TextStyle(fontSize: 11, color: AppColors.textSecondary)),
      ],
    );
  }
}
