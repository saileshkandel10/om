import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/api/api_client.dart';
import '../../../../core/api/api_endpoints.dart';
import '../../../../core/constants/app_theme.dart';
import '../../../auth/domain/auth_provider.dart';

class BoidVerificationScreen extends ConsumerStatefulWidget {
  const BoidVerificationScreen({super.key});

  @override
  ConsumerState<BoidVerificationScreen> createState() => _BoidVerificationScreenState();
}

class _BoidVerificationScreenState extends ConsumerState<BoidVerificationScreen> {
  final TextEditingController _boidController = TextEditingController();
  final _formKey = GlobalKey<FormState>();

  bool _isSubmitting = false;
  bool _isVerified = false;
  String? _resultMessage;
  Map<String, dynamic>? _verifiedAccount;
  bool _hasAttempted = false;

  @override
  void initState() {
    super.initState();
    _boidController.addListener(_onBoidChanged);
  }

  @override
  void dispose() {
    _boidController.removeListener(_onBoidChanged);
    _boidController.dispose();
    super.dispose();
  }

  void _onBoidChanged() {
    setState(() {
      // Clear previous verification result if user modifies the input
      if (_hasAttempted) {
        _hasAttempted = false;
        _resultMessage = null;
      }
    });
  }

  bool get _isValidLength => _boidController.text.trim().length == 16;

  Future<void> _verifyBoid() async {
    if (!_formKey.currentState!.validate()) return;

    FocusScope.of(context).unfocus();

    setState(() {
      _isSubmitting = true;
      _hasAttempted = false;
      _resultMessage = null;
    });

    final boid = _boidController.text.trim();

    try {
      final response = await ApiClient.instance.post(
        ApiEndpoints.shareholderVerifyBoid,
        data: {'boid': boid},
      );

      if (response.statusCode == 200 && response.data != null) {
        final data = response.data as Map<String, dynamic>;
        final verified = data['verified'] == true;
        final message = data['message'] as String? ?? 'BOID verified successfully.';
        final account = data['account'] as Map<String, dynamic>?;

        if (mounted) {
          setState(() {
            _isSubmitting = false;
            _hasAttempted = true;
            _isVerified = verified;
            _resultMessage = message;
            _verifiedAccount = account;
          });
          if (verified) {
            ref.read(authProvider.notifier).refreshUser();
          }
        }
      } else {
        throw ApiException(message: 'Verification failed. Please try again.');
      }
    } catch (e) {
      if (mounted) {
        String errorMsg = 'BOID verification failed. Please try again.';
        if (e is ApiException) {
          if (e.errors != null && e.errors!.containsKey('boid')) {
            final boidErrors = e.errors!['boid'];
            errorMsg = boidErrors is List ? boidErrors.join(' ') : boidErrors.toString();
          } else {
            errorMsg = e.message;
          }
        }

        setState(() {
          _isSubmitting = false;
          _hasAttempted = true;
          _isVerified = false;
          _resultMessage = errorMsg;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.surface,
      appBar: AppBar(
        title: const Text('Verify BOID'),
        elevation: 0,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Informational Card
            _buildInfoCard(),
            const SizedBox(height: 16),

            // Form Card
            _buildFormCard(),
            const SizedBox(height: 16),

            // Result Display Card
            if (_hasAttempted && _resultMessage != null)
              _buildResultCard(),
          ],
        ),
      ),
    );
  }

  Widget _buildInfoCard() {
    return Card(
      elevation: 0.5,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
        side: const BorderSide(color: AppColors.border, width: 0.6),
      ),
      color: Colors.white,
      margin: EdgeInsets.zero,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color: AppColors.primary.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(12),
              ),
              child: const Icon(
                Icons.info_outline_rounded,
                color: AppColors.primary,
                size: 24,
              ),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'What is a BOID?',
                    style: TextStyle(
                      fontSize: 15,
                      fontWeight: FontWeight.bold,
                      color: AppColors.textPrimary,
                    ),
                  ),
                  const SizedBox(height: 4),
                  const Text(
                    'Your Beneficiary Owner Identification Number (BOID) is the unique 16-digit Demat account number provided by CDSC Nepal (8-digit DP ID + 8-digit Client ID).\n\nVerifying your BOID links your shareholder status to claim shareholder bonuses and royalties.',
                    style: TextStyle(
                      fontSize: 13,
                      height: 1.4,
                      color: AppColors.textSecondary,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildFormCard() {
    final currentLength = _boidController.text.trim().length;

    return Card(
      elevation: 1,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
      ),
      color: Colors.white,
      margin: EdgeInsets.zero,
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const Text(
                'Enter Shareholder BOID',
                style: TextStyle(
                  fontSize: 17,
                  fontWeight: FontWeight.bold,
                  color: AppColors.textPrimary,
                ),
              ),
              const SizedBox(height: 6),
              const Text(
                'Please enter your 16-digit Demat number as registered in shareholder records.',
                style: TextStyle(
                  fontSize: 13,
                  color: AppColors.textSecondary,
                ),
              ),
              const SizedBox(height: 20),

              // BOID Input Field
              TextFormField(
                controller: _boidController,
                keyboardType: TextInputType.number,
                maxLength: 16,
                inputFormatters: [
                  FilteringTextInputFormatter.digitsOnly,
                  LengthLimitingTextInputFormatter(16),
                ],
                style: const TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.w600,
                  letterSpacing: 2.0,
                ),
                decoration: InputDecoration(
                  labelText: '16-Digit BOID',
                  hintText: '1301000000000000',
                  hintStyle: TextStyle(
                    letterSpacing: 1.5,
                    color: Colors.grey.shade400,
                  ),
                  prefixIcon: const Icon(Icons.fingerprint_rounded),
                  suffixIcon: currentLength == 16
                      ? const Icon(
                          Icons.check_circle_rounded,
                          color: AppColors.secondary,
                        )
                      : null,
                  counterText: '$currentLength / 16 digits',
                  counterStyle: TextStyle(
                    color: currentLength == 16
                        ? AppColors.secondary
                        : AppColors.textSecondary,
                    fontWeight: currentLength == 16
                        ? FontWeight.bold
                        : FontWeight.normal,
                  ),
                ),
                validator: (value) {
                  if (value == null || value.trim().isEmpty) {
                    return 'BOID is required';
                  }
                  if (value.trim().length != 16) {
                    return 'BOID must be exactly 16 digits (${16 - value.trim().length} more needed)';
                  }
                  if (!RegExp(r'^\d{16}$').hasMatch(value.trim())) {
                    return 'BOID can only contain digits';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 8),

              // Real-time helper text
              Row(
                children: [
                  Icon(
                    currentLength == 16
                        ? Icons.check_circle_outline_rounded
                        : Icons.radio_button_unchecked_rounded,
                    size: 14,
                    color: currentLength == 16
                        ? AppColors.secondary
                        : AppColors.textSecondary,
                  ),
                  const SizedBox(width: 6),
                  Text(
                    currentLength == 16
                        ? '16 digits entered'
                        : currentLength == 0
                            ? 'Must be exactly 16 digits'
                            : '${16 - currentLength} digits remaining',
                    style: TextStyle(
                      fontSize: 12,
                      color: currentLength == 16
                          ? AppColors.secondary
                          : AppColors.textSecondary,
                      fontWeight: currentLength == 16
                          ? FontWeight.bold
                          : FontWeight.normal,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 24),

              // Verify Button
              ElevatedButton(
                onPressed: (_isSubmitting || !_isValidLength)
                    ? null
                    : _verifyBoid,
                child: _isSubmitting
                    ? const SizedBox(
                        height: 20,
                        width: 20,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          color: Colors.white,
                        ),
                      )
                    : const Text(
                        'Verify BOID',
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildResultCard() {
    final isSuccess = _isVerified;

    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
        side: BorderSide(
          color: isSuccess ? AppColors.secondary : AppColors.danger,
          width: 1.2,
        ),
      ),
      color: isSuccess ? const Color(0xFFF0FDF4) : const Color(0xFFFEF2F2),
      margin: EdgeInsets.zero,
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          children: [
            Container(
              width: 56,
              height: 56,
              decoration: BoxDecoration(
                color: (isSuccess ? AppColors.secondary : AppColors.danger)
                    .withValues(alpha: 0.15),
                shape: BoxShape.circle,
              ),
              child: Icon(
                isSuccess
                    ? Icons.verified_rounded
                    : Icons.error_outline_rounded,
                size: 32,
                color: isSuccess ? AppColors.secondary : AppColors.danger,
              ),
            ),
            const SizedBox(height: 14),
            Text(
              isSuccess ? 'Verification Successful' : 'Verification Failed',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
                color: isSuccess ? AppColors.secondary : AppColors.danger,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              _resultMessage ?? '',
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontSize: 14,
                color: AppColors.textPrimary,
                height: 1.4,
              ),
            ),

            if (isSuccess && _verifiedAccount != null) ...[
              const SizedBox(height: 16),
              const Divider(),
              const SizedBox(height: 8),
              if (_verifiedAccount!['boid'] != null)
                _buildResultDetailRow(
                  label: 'Verified BOID',
                  value: _verifiedAccount!['boid'].toString(),
                ),
              if (_verifiedAccount!['shares_owned'] != null)
                _buildResultDetailRow(
                  label: 'Shares Owned',
                  value: '${_verifiedAccount!['shares_owned']} Units',
                ),
              if (_verifiedAccount!['verification_status'] != null)
                _buildResultDetailRow(
                  label: 'Account Status',
                  value: _verifiedAccount!['verification_status']
                      .toString()
                      .toUpperCase(),
                ),
            ],

            const SizedBox(height: 20),
            if (isSuccess)
              SizedBox(
                width: double.infinity,
                child: ElevatedButton.icon(
                  onPressed: () => Navigator.of(context).pop(true),
                  icon: const Icon(Icons.check_rounded),
                  label: const Text('Continue to Dashboard'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.secondary,
                    foregroundColor: Colors.white,
                  ),
                ),
              )
            else
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton(
                      onPressed: () {
                        setState(() {
                          _hasAttempted = false;
                          _resultMessage = null;
                        });
                      },
                      child: const Text('Try Again'),
                    ),
                  ),
                ],
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildResultDetailRow({
    required String label,
    required String value,
  }) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
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
              fontWeight: FontWeight.bold,
              color: AppColors.textPrimary,
            ),
          ),
        ],
      ),
    );
  }
}
