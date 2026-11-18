import 'dart:async';
import 'package:flutter/material.dart';
import '../../../../core/api/api_client.dart';
import '../../../../core/api/api_endpoints.dart';
import '../../../../core/constants/app_theme.dart';
import 'quiz_result_screen.dart';

class ActiveQuizScreen extends StatefulWidget {
  final Map<String, dynamic> attemptData;
  final Map<String, dynamic>? quizSettings;
  final VoidCallback onQuizFinished;

  const ActiveQuizScreen({
    super.key,
    required this.attemptData,
    this.quizSettings,
    required this.onQuizFinished,
  });

  @override
  State<ActiveQuizScreen> createState() => _ActiveQuizScreenState();
}

class _ActiveQuizScreenState extends State<ActiveQuizScreen> {
  int _currentQuestionIndex = 0;
  final Map<int, int> _selectedAnswers = {}; // question_id -> selected_option_id
  bool _isSubmitting = false;

  late final List<dynamic> _questions;
  late final int _attemptId;
  late final String _quizTitle;

  Timer? _countdownTimer;
  int _remainingSeconds = 0;

  @override
  void initState() {
    super.initState();
    _attemptId = widget.attemptData['id'] as int;
    _quizTitle = widget.attemptData['quiz_title']?.toString() ?? 'Pharmacist Quiz';
    _questions = widget.attemptData['questions'] as List<dynamic>? ?? [];

    _initializeTimer();
  }

  void _initializeTimer() {
    final timeLimitMinutes = widget.attemptData['time_limit_minutes'] as int? ??
        (widget.quizSettings?['time_limit_minutes'] as int? ?? 15);

    if (timeLimitMinutes > 0) {
      final startedAtStr = widget.attemptData['started_at']?.toString();
      if (startedAtStr != null) {
        final startedAt = DateTime.tryParse(startedAtStr);
        if (startedAt != null) {
          final elapsedSeconds = DateTime.now().difference(startedAt.toLocal()).inSeconds;
          final totalLimitSeconds = timeLimitMinutes * 60;
          _remainingSeconds = (totalLimitSeconds - elapsedSeconds).clamp(0, totalLimitSeconds);
        } else {
          _remainingSeconds = timeLimitMinutes * 60;
        }
      } else {
        _remainingSeconds = timeLimitMinutes * 60;
      }

      if (_remainingSeconds > 0) {
        _startTimer();
      } else {
        // Expired on open
        WidgetsBinding.instance.addPostFrameCallback((_) {
          _submitQuiz(autoSubmit: true);
        });
      }
    }
  }

  void _startTimer() {
    _countdownTimer?.cancel();
    _countdownTimer = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (!mounted) return;
      if (_remainingSeconds > 0) {
        setState(() => _remainingSeconds--);
      } else {
        timer.cancel();
        _submitQuiz(autoSubmit: true);
      }
    });
  }

  @override
  void dispose() {
    _countdownTimer?.cancel();
    super.dispose();
  }

  String _formatTimer(int totalSeconds) {
    final m = totalSeconds ~/ 60;
    final s = totalSeconds % 60;
    return '${m.toString().padLeft(2, '0')}:${s.toString().padLeft(2, '0')}';
  }

  Future<bool> _onWillPop() async {
    final shouldExit = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
        title: const Row(
          children: [
            Icon(Icons.warning_amber_rounded, color: Colors.orange, size: 24),
            SizedBox(width: 8),
            Text('Leave Quiz Session?', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
          ],
        ),
        content: const Text(
          'Your quiz attempt is active on the server. The countdown timer will continue running. You can resume before it expires.',
          style: TextStyle(fontSize: 13, height: 1.4),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Stay in Quiz'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.primary,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Leave for Now'),
          ),
        ],
      ),
    );

    return shouldExit ?? false;
  }

  Future<void> _submitQuiz({bool autoSubmit = false}) async {
    if (_isSubmitting) return;

    if (!autoSubmit && _selectedAnswers.length < _questions.length) {
      final confirm = await showDialog<bool>(
        context: context,
        builder: (ctx) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
          title: const Text('Incomplete Quiz Submission', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'You have answered ${_selectedAnswers.length} of ${_questions.length} questions.',
                style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600),
              ),
              const SizedBox(height: 8),
              const Text(
                'Unanswered questions will receive 0 points. Are you sure you want to finish and submit now?',
                style: TextStyle(fontSize: 12, color: AppColors.textSecondary),
              ),
            ],
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(ctx, false),
              child: const Text('Continue Answering'),
            ),
            ElevatedButton(
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.green.shade600,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
              ),
              onPressed: () => Navigator.pop(ctx, true),
              child: const Text('Submit Quiz'),
            ),
          ],
        ),
      );
      if (confirm != true) return;
    }

    setState(() => _isSubmitting = true);

    try {
      final payload = {
        'answers': _questions.map((q) {
          final qId = q['id'];
          return {
            'question_id': qId,
            'selected_option_id': _selectedAnswers[qId],
          };
        }).toList(),
      };

      final response = await ApiClient.instance.post(
        ApiEndpoints.pharmacyQuizSubmit(_attemptId),
        data: payload,
      );

      if (mounted) {
        setState(() => _isSubmitting = false);
      }

      if (response.statusCode == 200 || response.statusCode == 201) {
        final resData = response.data as Map<String, dynamic>;
        widget.onQuizFinished();

        if (mounted) {
          Navigator.pushReplacement(
            context,
            MaterialPageRoute(
              builder: (_) => QuizResultScreen(
                resultData: resData,
                quizTitle: _quizTitle,
              ),
            ),
          );
        }
      } else {
        final msg = response.data?['message'] ?? 'Quiz submission failed.';
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text(msg), backgroundColor: AppColors.danger),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isSubmitting = false);
        final msg = e is ApiException ? e.message : 'Network error during submission. Please try again.';
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(msg), backgroundColor: AppColors.danger),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_questions.isEmpty) {
      return Scaffold(
        appBar: AppBar(title: Text(_quizTitle)),
        body: const Center(child: Text('No questions loaded for this quiz.')),
      );
    }

    final currentQuestion = _questions[_currentQuestionIndex] as Map<String, dynamic>;
    final qId = currentQuestion['id'] as int;
    final qText = currentQuestion['question_text']?.toString() ?? '';
    final options = currentQuestion['options'] as List<dynamic>? ?? [];

    return PopScope(
      canPop: false,
      onPopInvokedWithResult: (didPop, result) async {
        if (didPop) return;
        final shouldPop = await _onWillPop();
        if (shouldPop && context.mounted) {
          Navigator.pop(context);
        }
      },
      child: Scaffold(
        backgroundColor: AppColors.background,
        appBar: AppBar(
          title: Text(
            _quizTitle,
            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
          backgroundColor: AppColors.primary,
          foregroundColor: Colors.white,
          elevation: 0,
          leading: IconButton(
            icon: const Icon(Icons.close_rounded),
            onPressed: () async {
              final shouldPop = await _onWillPop();
              if (shouldPop && context.mounted) {
                Navigator.pop(context);
              }
            },
          ),
          actions: [
            if (_remainingSeconds > 0)
              Padding(
                padding: const EdgeInsets.only(right: 16),
                child: Center(
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: _remainingSeconds < 60 ? Colors.red.shade700 : Colors.white.withValues(alpha: 0.25),
                      borderRadius: BorderRadius.circular(14),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Icon(Icons.timer, size: 14, color: Colors.white),
                        const SizedBox(width: 4),
                        Text(
                          _formatTimer(_remainingSeconds),
                          style: const TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.bold,
                            fontSize: 12,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
          ],
        ),
        body: SafeArea(
          child: Column(
            children: [
              // Top Progress Header & Question Navigator Strip
              Container(
                color: Colors.white,
                padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
                child: Column(
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          'Question ${_currentQuestionIndex + 1} of ${_questions.length}',
                          style: const TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 13,
                            color: AppColors.textPrimary,
                          ),
                        ),
                        Text(
                          '${_selectedAnswers.length}/${_questions.length} Answered',
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w600,
                            color: _selectedAnswers.length == _questions.length
                                ? Colors.green.shade700
                                : AppColors.textSecondary,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    LinearProgressIndicator(
                      value: (_currentQuestionIndex + 1) / _questions.length,
                      backgroundColor: Colors.grey.shade100,
                      color: AppColors.primary,
                      minHeight: 5,
                      borderRadius: BorderRadius.circular(4),
                    ),
                    const SizedBox(height: 10),
                    // Quick Question Jump Carousel
                    SizedBox(
                      height: 32,
                      child: ListView.builder(
                        scrollDirection: Axis.horizontal,
                        itemCount: _questions.length,
                        itemBuilder: (context, index) {
                          final itemQId = _questions[index]['id'] as int;
                          final isAnswered = _selectedAnswers.containsKey(itemQId);
                          final isCurrent = index == _currentQuestionIndex;

                          return GestureDetector(
                            onTap: () {
                              setState(() => _currentQuestionIndex = index);
                            },
                            child: AnimatedContainer(
                              duration: const Duration(milliseconds: 200),
                              width: 32,
                              height: 32,
                              margin: const EdgeInsets.only(right: 6),
                              decoration: BoxDecoration(
                                color: isCurrent
                                    ? AppColors.primary
                                    : (isAnswered ? Colors.green.shade500 : Colors.grey.shade100),
                                shape: BoxShape.circle,
                                border: Border.all(
                                  color: isCurrent ? AppColors.primary : (isAnswered ? Colors.green.shade600 : Colors.grey.shade300),
                                  width: isCurrent ? 2 : 1,
                                ),
                              ),
                              child: Center(
                                child: Text(
                                  '${index + 1}',
                                  style: TextStyle(
                                    fontSize: 11,
                                    fontWeight: FontWeight.bold,
                                    color: (isCurrent || isAnswered) ? Colors.white : AppColors.textSecondary,
                                  ),
                                ),
                              ),
                            ),
                          );
                        },
                      ),
                    ),
                  ],
                ),
              ),

              // Question Text & MCQ Options
              Expanded(
                child: SingleChildScrollView(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Question Prompt Card
                      Card(
                        elevation: 1,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                        color: Colors.white,
                        child: Padding(
                          padding: const EdgeInsets.all(18),
                          child: Text(
                            qText,
                            style: const TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                              color: AppColors.textPrimary,
                              height: 1.45,
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(height: 16),

                      const Padding(
                        padding: EdgeInsets.symmetric(horizontal: 4),
                        child: Text(
                          'Select your answer:',
                          style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.textSecondary),
                        ),
                      ),
                      const SizedBox(height: 8),

                      // 4 Options (A, B, C, D)
                      ...List.generate(options.length, (optIdx) {
                        final opt = options[optIdx];
                        final optId = opt['id'] as int;
                        final optText = opt['option_text']?.toString() ?? '';
                        final isSelected = _selectedAnswers[qId] == optId;
                        final letter = String.fromCharCode(65 + optIdx);

                        return GestureDetector(
                          onTap: () {
                            setState(() {
                              _selectedAnswers[qId] = optId;
                            });
                          },
                          child: AnimatedContainer(
                            duration: const Duration(milliseconds: 150),
                            margin: const EdgeInsets.only(bottom: 10),
                            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
                            decoration: BoxDecoration(
                              color: isSelected ? AppColors.primary.withValues(alpha: 0.08) : Colors.white,
                              borderRadius: BorderRadius.circular(14),
                              border: Border.all(
                                color: isSelected ? AppColors.primary : Colors.grey.shade300,
                                width: isSelected ? 2 : 1,
                              ),
                              boxShadow: [
                                BoxShadow(
                                  color: Colors.black.withValues(alpha: 0.03),
                                  blurRadius: 4,
                                  offset: const Offset(0, 2),
                                ),
                              ],
                            ),
                            child: Row(
                              children: [
                                Container(
                                  width: 28,
                                  height: 28,
                                  decoration: BoxDecoration(
                                    color: isSelected ? AppColors.primary : Colors.grey.shade100,
                                    shape: BoxShape.circle,
                                    border: Border.all(
                                      color: isSelected ? AppColors.primary : Colors.grey.shade300,
                                    ),
                                  ),
                                  child: Center(
                                    child: Text(
                                      letter,
                                      style: TextStyle(
                                        fontSize: 12,
                                        fontWeight: FontWeight.bold,
                                        color: isSelected ? Colors.white : AppColors.textPrimary,
                                      ),
                                    ),
                                  ),
                                ),
                                const SizedBox(width: 12),
                                Expanded(
                                  child: Text(
                                    optText,
                                    style: TextStyle(
                                      fontSize: 14,
                                      fontWeight: isSelected ? FontWeight.w600 : FontWeight.normal,
                                      color: isSelected ? AppColors.primary : AppColors.textPrimary,
                                      height: 1.3,
                                    ),
                                  ),
                                ),
                                if (isSelected)
                                  const Icon(Icons.check_circle_rounded, color: AppColors.primary, size: 20),
                              ],
                            ),
                          ),
                        );
                      }),
                    ],
                  ),
                ),
              ),

              // Bottom Navigation Action Bar
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                decoration: BoxDecoration(
                  color: Colors.white,
                  border: Border(top: BorderSide(color: Colors.grey.shade200)),
                  boxShadow: [
                    BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 6, offset: const Offset(0, -2)),
                  ],
                ),
                child: Row(
                  children: [
                    if (_currentQuestionIndex > 0)
                      OutlinedButton.icon(
                        style: OutlinedButton.styleFrom(
                          foregroundColor: AppColors.textPrimary,
                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                        onPressed: () {
                          setState(() => _currentQuestionIndex--);
                        },
                        icon: const Icon(Icons.arrow_back_rounded, size: 16),
                        label: const Text('Previous'),
                      )
                    else
                      const SizedBox.shrink(),
                    const Spacer(),
                    if (_currentQuestionIndex < _questions.length - 1)
                      ElevatedButton(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppColors.primary,
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                        onPressed: () {
                          setState(() => _currentQuestionIndex++);
                        },
                        child: const Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Text('Next Question', style: TextStyle(fontWeight: FontWeight.bold)),
                            SizedBox(width: 6),
                            Icon(Icons.arrow_forward_rounded, size: 16),
                          ],
                        ),
                      )
                    else
                      ElevatedButton.icon(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.green.shade600,
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                        onPressed: _isSubmitting ? null : () => _submitQuiz(),
                        icon: _isSubmitting
                            ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                            : const Icon(Icons.send_rounded, size: 16),
                        label: Text(
                          _isSubmitting ? 'Submitting...' : 'Submit Quiz',
                          style: const TextStyle(fontWeight: FontWeight.bold),
                        ),
                      ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
