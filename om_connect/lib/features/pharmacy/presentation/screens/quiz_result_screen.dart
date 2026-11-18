import 'package:flutter/material.dart';
import '../../../../core/constants/app_theme.dart';

class QuizResultScreen extends StatefulWidget {
  final Map<String, dynamic> resultData;
  final String quizTitle;

  const QuizResultScreen({
    super.key,
    required this.resultData,
    required this.quizTitle,
  });

  @override
  State<QuizResultScreen> createState() => _QuizResultScreenState();
}

class _QuizResultScreenState extends State<QuizResultScreen> {
  bool _showDetailedReview = false;

  @override
  Widget build(BuildContext context) {
    final isPassed = widget.resultData['is_passed'] as bool? ?? false;
    final rewardPoints = widget.resultData['reward_points_awarded']?.toString() ?? '0';

    final summary = widget.resultData['summary'] as Map<String, dynamic>? ?? {};
    final attempt = widget.resultData['attempt'] as Map<String, dynamic>? ?? {};

    final percentage = summary['percentage']?.toString() ?? (attempt['percentage']?.toString() ?? '0.00');
    final score = summary['score']?.toString() ?? (attempt['score']?.toString() ?? '0');
    final totalPoints = summary['total_points']?.toString() ?? (attempt['total_points']?.toString() ?? '0');
    final correctCount = summary['correct_answers']?.toString() ?? (attempt['correct_answers_count']?.toString() ?? '0');
    final incorrectCount = summary['incorrect_answers']?.toString() ?? (attempt['incorrect_answers_count']?.toString() ?? '0');
    final unansweredCount = summary['unanswered_questions']?.toString() ?? (attempt['unanswered_count']?.toString() ?? '0');
    final timeTaken = summary['time_taken_formatted']?.toString() ?? (attempt['time_taken_formatted']?.toString() ?? '');
    final passMark = summary['passing_percentage']?.toString() ?? '70';

    final answers = attempt['answers'] as List<dynamic>? ?? [];

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Quiz Results & Evaluation', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
        backgroundColor: isPassed ? const Color(0xFF047857) : const Color(0xFFB91C1C),
        foregroundColor: Colors.white,
        elevation: 0,
        automaticallyImplyLeading: false,
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(16),
          child: Column(
            children: [
              // Result Banner Card
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(24),
                decoration: BoxDecoration(
                  gradient: isPassed
                      ? const LinearGradient(
                          colors: [Color(0xFF047857), Color(0xFF065F46)],
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                        )
                      : const LinearGradient(
                          colors: [Color(0xFFB91C1C), Color(0xFF991B1B)],
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                        ),
                  borderRadius: BorderRadius.circular(20),
                  boxShadow: [
                    BoxShadow(
                      color: (isPassed ? Colors.green : Colors.red).withValues(alpha: 0.25),
                      blurRadius: 15,
                      offset: const Offset(0, 6),
                    ),
                  ],
                ),
                child: Column(
                  children: [
                    Container(
                      width: 72,
                      height: 72,
                      decoration: BoxDecoration(
                        color: Colors.white.withValues(alpha: 0.2),
                        shape: BoxShape.circle,
                      ),
                      child: Icon(
                        isPassed ? Icons.emoji_events_rounded : Icons.cancel_rounded,
                        color: Colors.white,
                        size: 44,
                      ),
                    ),
                    const SizedBox(height: 16),
                    Text(
                      isPassed ? 'Congratulations! Quiz Passed' : 'Assessment Completed',
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      widget.quizTitle,
                      style: const TextStyle(color: Colors.white70, fontSize: 13),
                      textAlign: TextAlign.center,
                    ),
                    const SizedBox(height: 16),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
                      decoration: BoxDecoration(
                        color: Colors.white.withValues(alpha: 0.15),
                        borderRadius: BorderRadius.circular(30),
                      ),
                      child: Text(
                        'Score: $score / $totalPoints ($percentage%)',
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 16,
                          fontWeight: FontWeight.w900,
                          letterSpacing: 0.5,
                        ),
                      ),
                    ),
                    if (isPassed && double.tryParse(rewardPoints) != null && double.parse(rewardPoints) > 0) ...[
                      const SizedBox(height: 14),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                        decoration: BoxDecoration(
                          color: Colors.amber.shade400,
                          borderRadius: BorderRadius.circular(20),
                          boxShadow: [
                            BoxShadow(color: Colors.black.withValues(alpha: 0.1), blurRadius: 6, offset: const Offset(0, 2)),
                          ],
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Icon(Icons.stars_rounded, color: Color(0xFF78350F), size: 18),
                            const SizedBox(width: 6),
                            Text(
                              '+$rewardPoints Loyalty Points Credited!',
                              style: const TextStyle(
                                fontWeight: FontWeight.bold,
                                color: Color(0xFF78350F),
                                fontSize: 12,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ],
                ),
              ),

              const SizedBox(height: 16),

              // Breakdown Metrics Grid
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: Colors.grey.shade200),
                  boxShadow: [
                    BoxShadow(color: Colors.black.withValues(alpha: 0.03), blurRadius: 6, offset: const Offset(0, 2)),
                  ],
                ),
                child: Column(
                  children: [
                    Row(
                      children: [
                        _buildStatBox('Correct', correctCount, Colors.green.shade50, Colors.green.shade700, Icons.check_circle),
                        const SizedBox(width: 8),
                        _buildStatBox('Incorrect', incorrectCount, Colors.red.shade50, Colors.red.shade700, Icons.cancel),
                        const SizedBox(width: 8),
                        _buildStatBox('Unanswered', unansweredCount, Colors.grey.shade100, Colors.grey.shade700, Icons.remove_circle),
                      ],
                    ),
                    if (timeTaken.isNotEmpty || passMark.isNotEmpty) ...[
                      const SizedBox(height: 12),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          if (timeTaken.isNotEmpty)
                            Row(
                              children: [
                                const Icon(Icons.timer_outlined, size: 14, color: AppColors.textSecondary),
                                const SizedBox(width: 4),
                                Text('Time Taken: $timeTaken', style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
                              ],
                            ),
                          Row(
                            children: [
                              const Icon(Icons.flag_outlined, size: 14, color: AppColors.textSecondary),
                              const SizedBox(width: 4),
                              Text('Pass Requirement: $passMark%', style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
                            ],
                          ),
                        ],
                      ),
                    ],
                  ],
                ),
              ),

              const SizedBox(height: 16),

              // Action Buttons Row
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton.icon(
                      style: OutlinedButton.styleFrom(
                        padding: const EdgeInsets.symmetric(vertical: 12),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                      onPressed: () {
                        setState(() {
                          _showDetailedReview = !_showDetailedReview;
                        });
                      },
                      icon: Icon(_showDetailedReview ? Icons.visibility_off_outlined : Icons.visibility_outlined, size: 16),
                      label: Text(_showDetailedReview ? 'Hide Review' : 'Review Answers', style: const TextStyle(fontWeight: FontWeight.bold)),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: ElevatedButton.icon(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.primary,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 12),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                      onPressed: () => Navigator.pop(context),
                      icon: const Icon(Icons.check_rounded, size: 18),
                      label: const Text('Done', style: TextStyle(fontWeight: FontWeight.bold)),
                    ),
                  ),
                ],
              ),

              // Question-by-Question Detailed Review
              if (_showDetailedReview && answers.isNotEmpty) ...[
                const SizedBox(height: 20),
                Row(
                  children: [
                    const Icon(Icons.assignment_turned_in_rounded, color: AppColors.primary, size: 20),
                    const SizedBox(width: 6),
                    Text(
                      'Question Review (${answers.length} Items)',
                      style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                ...answers.asMap().entries.map((entry) {
                  final idx = entry.key;
                  final ans = entry.value as Map<String, dynamic>;

                  final qText = ans['question_text']?.toString() ?? 'Question';
                  final selectedOptText = ans['selected_option_text']?.toString() ?? 'No Answer Selected';
                  final correctOptText = ans['correct_option_text']?.toString();
                  final explanation = ans['explanation']?.toString();
                  final isCorrect = ans['is_correct'] as bool? ?? false;
                  final pointsEarned = ans['points_earned']?.toString() ?? '0';
                  final possiblePoints = ans['possible_points']?.toString() ?? '1';

                  return Card(
                    margin: const EdgeInsets.only(bottom: 12),
                    elevation: 1,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(16),
                      side: BorderSide(
                        color: isCorrect ? Colors.green.shade200 : Colors.red.shade200,
                        width: 1.2,
                      ),
                    ),
                    color: Colors.white,
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                decoration: BoxDecoration(
                                  color: isCorrect ? Colors.green.shade50 : Colors.red.shade50,
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: Text(
                                  'Q${idx + 1}',
                                  style: TextStyle(
                                    fontWeight: FontWeight.bold,
                                    fontSize: 12,
                                    color: isCorrect ? Colors.green.shade800 : Colors.red.shade800,
                                  ),
                                ),
                              ),
                              const SizedBox(width: 8),
                              Expanded(
                                child: Text(
                                  qText,
                                  style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                                ),
                              ),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                decoration: BoxDecoration(
                                  color: isCorrect ? Colors.green.shade100 : Colors.red.shade100,
                                  borderRadius: BorderRadius.circular(10),
                                ),
                                child: Text(
                                  '$pointsEarned/$possiblePoints pt',
                                  style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: isCorrect ? Colors.green.shade900 : Colors.red.shade900),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 12),

                          // Selected Answer
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                            decoration: BoxDecoration(
                              color: isCorrect ? Colors.green.shade50 : Colors.red.shade50,
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: Row(
                              children: [
                                Icon(
                                  isCorrect ? Icons.check_circle_rounded : Icons.cancel_rounded,
                                  size: 16,
                                  color: isCorrect ? Colors.green.shade700 : Colors.red.shade700,
                                ),
                                const SizedBox(width: 8),
                                Expanded(
                                  child: Text(
                                    'Your Answer: $selectedOptText',
                                    style: TextStyle(
                                      fontSize: 12,
                                      fontWeight: FontWeight.w600,
                                      color: isCorrect ? Colors.green.shade900 : Colors.red.shade900,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),

                          // Correct Answer (if not correct and revealed)
                          if (!isCorrect && correctOptText != null) ...[
                            const SizedBox(height: 6),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                              decoration: BoxDecoration(
                                color: Colors.green.shade50,
                                borderRadius: BorderRadius.circular(10),
                              ),
                              child: Row(
                                children: [
                                  Icon(Icons.check_circle_outline, size: 16, color: Colors.green.shade700),
                                  const SizedBox(width: 8),
                                  Expanded(
                                    child: Text(
                                      'Correct Answer: $correctOptText',
                                      style: TextStyle(
                                        fontSize: 12,
                                        fontWeight: FontWeight.w600,
                                        color: Colors.green.shade900,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],

                          // Clinical Explanation & Literature Rationale
                          if (explanation != null && explanation.isNotEmpty) ...[
                            const SizedBox(height: 10),
                            Container(
                              padding: const EdgeInsets.all(12),
                              decoration: BoxDecoration(
                                color: AppColors.primary.withValues(alpha: 0.05),
                                borderRadius: BorderRadius.circular(10),
                                border: Border.all(color: AppColors.primary.withValues(alpha: 0.15)),
                              ),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const Row(
                                    children: [
                                      Icon(Icons.lightbulb_outline, size: 14, color: AppColors.primary),
                                      SizedBox(width: 4),
                                      Text(
                                        'Clinical Rationale:',
                                        style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: AppColors.primary),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 4),
                                  Text(
                                    explanation,
                                    style: const TextStyle(fontSize: 12, color: AppColors.textPrimary, height: 1.35),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ],
                      ),
                    ),
                  );
                }),
              ],
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildStatBox(String label, String value, Color bgColor, Color textColor, IconData icon) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 8),
        decoration: BoxDecoration(
          color: bgColor,
          borderRadius: BorderRadius.circular(12),
        ),
        child: Column(
          children: [
            Icon(icon, size: 18, color: textColor),
            const SizedBox(height: 4),
            Text(
              value,
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.w900, color: textColor),
            ),
            Text(
              label,
              style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: textColor),
            ),
          ],
        ),
      ),
    );
  }
}
