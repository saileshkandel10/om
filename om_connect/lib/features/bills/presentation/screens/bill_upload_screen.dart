import 'dart:io';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:image_picker/image_picker.dart';
import '../../../../core/api/api_client.dart';
import '../../../../core/api/api_endpoints.dart';
import '../../../../core/constants/app_theme.dart';

class BillUploadScreen extends StatefulWidget {
  const BillUploadScreen({super.key});

  @override
  State<BillUploadScreen> createState() => _BillUploadScreenState();
}

class _BillUploadScreenState extends State<BillUploadScreen> {
  final _formKey = GlobalKey<FormState>();
  final _invoiceNumberController = TextEditingController();
  final _totalAmountController = TextEditingController();
  final _doctorNameController = TextEditingController();
  final _doctorNmcController = TextEditingController();
  final ImagePicker _picker = ImagePicker();

  XFile? _selectedImage;
  int? _fileSizeBytes;
  bool _isUploading = false;
  double? _uploadProgress;

  @override
  void dispose() {
    _invoiceNumberController.dispose();
    _totalAmountController.dispose();
    _doctorNameController.dispose();
    _doctorNmcController.dispose();
    super.dispose();
  }

  Future<void> _pickImage(ImageSource source) async {
    try {
      final XFile? image = await _picker.pickImage(
        source: source,
        imageQuality: 85,
        maxWidth: 2000,
        maxHeight: 2000,
      );

      if (image != null) {
        final file = File(image.path);
        final length = await file.length();

        setState(() {
          _selectedImage = image;
          _fileSizeBytes = length;
        });
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Failed to pick image: $e'),
            backgroundColor: AppColors.danger,
          ),
        );
      }
    }
  }

  void _showImageSourceModal() {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) {
        return SafeArea(
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 20),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Text(
                  'Select Receipt Image',
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                    color: AppColors.textPrimary,
                  ),
                ),
                const SizedBox(height: 16),
                ListTile(
                  leading: Container(
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: AppColors.primary.withValues(alpha: 0.1),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(Icons.camera_alt, color: AppColors.primary),
                  ),
                  title: const Text('Take a Photo'),
                  subtitle: const Text('Capture using device camera'),
                  onTap: () {
                    Navigator.pop(ctx);
                    _pickImage(ImageSource.camera);
                  },
                ),
                const Divider(),
                ListTile(
                  leading: Container(
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: AppColors.secondary.withValues(alpha: 0.1),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(Icons.photo_library, color: AppColors.secondary),
                  ),
                  title: const Text('Choose from Gallery'),
                  subtitle: const Text('Pick existing receipt photo'),
                  onTap: () {
                    Navigator.pop(ctx);
                    _pickImage(ImageSource.gallery);
                  },
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  Future<void> _uploadBill() async {
    if (_selectedImage == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please select or capture a receipt image first.'),
          backgroundColor: AppColors.warning,
        ),
      );
      return;
    }

    if (_fileSizeBytes != null && _fileSizeBytes! > 5 * 1024 * 1024) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('The receipt file must not exceed 5MB.'),
          backgroundColor: AppColors.danger,
        ),
      );
      return;
    }

    setState(() {
      _isUploading = true;
      _uploadProgress = 0.0;
    });

    try {
      final fileName = _selectedImage!.path.split('/').last.split('\\').last;

      final formMap = <String, dynamic>{
        'receipt': await MultipartFile.fromFile(
          _selectedImage!.path,
          filename: fileName,
        ),
      };

      final invoiceNum = _invoiceNumberController.text.trim();
      if (invoiceNum.isNotEmpty) {
        formMap['invoice_number'] = invoiceNum;
      }

      final amountText = _totalAmountController.text.trim();
      if (amountText.isNotEmpty) {
        final parsedAmount = double.tryParse(amountText);
        if (parsedAmount != null) {
          formMap['total_amount'] = parsedAmount;
        }
      }

      final doctorName = _doctorNameController.text.trim();
      if (doctorName.isNotEmpty) {
        formMap['doctor_name'] = doctorName;
      }

      final doctorNmc = _doctorNmcController.text.trim();
      if (doctorNmc.isNotEmpty) {
        formMap['doctor_nmc'] = doctorNmc;
      }

      final formData = FormData.fromMap(formMap);

      final response = await ApiClient.instance.post(
        ApiEndpoints.bills,
        data: formData,
        onSendProgress: (sent, total) {
          if (total > 0 && mounted) {
            setState(() {
              _uploadProgress = sent / total;
            });
          }
        },
      );

      if (mounted) {
        setState(() {
          _isUploading = false;
          _uploadProgress = null;
        });

        if (response.statusCode == 201 || response.statusCode == 200) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Receipt uploaded successfully! OCR processing started.'),
              backgroundColor: AppColors.secondary,
            ),
          );

          if (context.canPop()) {
            context.pop(true);
          } else {
            context.go('/bills');
          }
        }
      }
    } catch (e) {
      final message = e is ApiException
          ? e.message
          : e is DioException
              ? ApiException.fromDioError(e).message
              : 'Failed to upload receipt. Please try again.';

      if (mounted) {
        setState(() {
          _isUploading = false;
          _uploadProgress = null;
        });

        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(message),
            backgroundColor: AppColors.danger,
            duration: const Duration(seconds: 4),
          ),
        );
      }
    }
  }

  String _formatFileSize(int bytes) {
    if (bytes < 1024) return '$bytes B';
    if (bytes < 1024 * 1024) return '${(bytes / 1024).toStringAsFixed(1)} KB';
    return '${(bytes / (1024 * 1024)).toStringAsFixed(2)} MB';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.surface,
      appBar: AppBar(
        title: const Text(
          'Upload Receipt',
          style: TextStyle(fontWeight: FontWeight.w700),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 16.0),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              _buildInstructionsCard(),
              const SizedBox(height: 16),
              _buildImagePickerArea(),
              const SizedBox(height: 16),
              _buildOptionalFieldsCard(),
              const SizedBox(height: 20),
              if (_isUploading) _buildProgressIndicator(),
              const SizedBox(height: 16),
              _buildSubmitButton(),
              const SizedBox(height: 32),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildInstructionsCard() {
    return Card(
      elevation: 0,
      margin: EdgeInsets.zero,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
        side: BorderSide(color: AppColors.info.withValues(alpha: 0.3)),
      ),
      color: AppColors.info.withValues(alpha: 0.06),
      child: const Padding(
        padding: EdgeInsets.all(14),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Icon(Icons.info_outline, color: AppColors.info, size: 22),
            SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'OCR Automatic Extraction',
                    style: TextStyle(
                      fontWeight: FontWeight.bold,
                      fontSize: 13,
                      color: AppColors.textPrimary,
                    ),
                  ),
                  SizedBox(height: 2),
                  Text(
                    'Our system will automatically scan invoice number, date, amount, and doctor details from the receipt image.',
                    style: TextStyle(
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
    );
  }

  Widget _buildImagePickerArea() {
    if (_selectedImage != null) {
      return Card(
        elevation: 1,
        margin: EdgeInsets.zero,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(16),
          side: const BorderSide(color: AppColors.secondary, width: 1.5),
        ),
        clipBehavior: Clip.antiAlias,
        child: Column(
          children: [
            Stack(
              alignment: Alignment.topRight,
              children: [
                Container(
                  width: double.infinity,
                  height: 230,
                  color: Colors.black12,
                  child: Image.file(
                    File(_selectedImage!.path),
                    fit: BoxFit.cover,
                  ),
                ),
                Positioned(
                  top: 10,
                  right: 10,
                  child: CircleAvatar(
                    backgroundColor: Colors.black54,
                    child: IconButton(
                      icon: const Icon(Icons.close, color: Colors.white, size: 20),
                      onPressed: () {
                        setState(() {
                          _selectedImage = null;
                          _fileSizeBytes = null;
                        });
                      },
                    ),
                  ),
                ),
              ],
            ),
            Padding(
              padding: const EdgeInsets.all(14),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Row(
                    children: [
                      const Icon(Icons.check_circle, color: AppColors.secondary, size: 20),
                      const SizedBox(width: 8),
                      Text(
                        _selectedImage!.name,
                        style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
                      ),
                    ],
                  ),
                  if (_fileSizeBytes != null)
                    Text(
                      _formatFileSize(_fileSizeBytes!),
                      style: const TextStyle(color: AppColors.textSecondary, fontSize: 12),
                    ),
                ],
              ),
            ),
          ],
        ),
      );
    }

    return Card(
      elevation: 1,
      margin: EdgeInsets.zero,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
        side: const BorderSide(color: AppColors.border),
      ),
      child: InkWell(
        onTap: _showImageSourceModal,
        borderRadius: BorderRadius.circular(16),
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 36, horizontal: 20),
          child: Column(
            children: [
              Container(
                padding: const EdgeInsets.all(18),
                decoration: BoxDecoration(
                  color: AppColors.primary.withValues(alpha: 0.1),
                  shape: BoxShape.circle,
                ),
                child: const Icon(
                  Icons.cloud_upload_rounded,
                  color: AppColors.primary,
                  size: 40,
                ),
              ),
              const SizedBox(height: 14),
              const Text(
                'Tap to Capture or Select Receipt',
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                  color: AppColors.textPrimary,
                ),
              ),
              const SizedBox(height: 6),
              const Text(
                'Camera or Gallery • JPG, PNG (Max 5MB)',
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 12,
                  color: AppColors.textSecondary,
                ),
              ),
              const SizedBox(height: 16),
              Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  ElevatedButton.icon(
                    onPressed: () => _pickImage(ImageSource.camera),
                    icon: const Icon(Icons.camera_alt_outlined, size: 18),
                    label: const Text('Camera'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.primary,
                      foregroundColor: Colors.white,
                      minimumSize: const Size(110, 40),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10),
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  OutlinedButton.icon(
                    onPressed: () => _pickImage(ImageSource.gallery),
                    icon: const Icon(Icons.photo_library_outlined, size: 18),
                    label: const Text('Gallery'),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: AppColors.primary,
                      minimumSize: const Size(110, 40),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10),
                      ),
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

  Widget _buildOptionalFieldsCard() {
    return Card(
      elevation: 1,
      margin: EdgeInsets.zero,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
        side: BorderSide(color: AppColors.border.withValues(alpha: 0.6)),
      ),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Row(
              children: [
                Icon(Icons.tune_rounded, color: AppColors.primary, size: 20),
                SizedBox(width: 8),
                Text(
                  'Optional Information',
                  style: TextStyle(
                    fontSize: 15,
                    fontWeight: FontWeight.bold,
                    color: AppColors.textPrimary,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 4),
            const Text(
              'Fill in manually or let OCR auto-fill during processing.',
              style: TextStyle(
                fontSize: 12,
                color: AppColors.textSecondary,
              ),
            ),
            const SizedBox(height: 16),
            TextFormField(
              controller: _invoiceNumberController,
              enabled: !_isUploading,
              decoration: const InputDecoration(
                labelText: 'Invoice Number (Optional)',
                hintText: 'e.g. INV-100234',
                prefixIcon: Icon(Icons.tag_rounded),
              ),
            ),
            const SizedBox(height: 14),
            TextFormField(
              controller: _totalAmountController,
              enabled: !_isUploading,
              keyboardType: const TextInputType.numberWithOptions(decimal: true),
              decoration: const InputDecoration(
                labelText: 'Total Amount (Optional)',
                hintText: 'e.g. 1500.00',
                prefixIcon: Icon(Icons.receipt_rounded),
                prefixText: 'Rs. ',
              ),
            ),
            const SizedBox(height: 14),
            TextFormField(
              controller: _doctorNameController,
              enabled: !_isUploading,
              decoration: const InputDecoration(
                labelText: 'Doctor Name (Optional)',
                hintText: 'e.g. Dr. Ramesh Sharma',
                prefixIcon: Icon(Icons.person_outline_rounded),
              ),
            ),
            const SizedBox(height: 14),
            TextFormField(
              controller: _doctorNmcController,
              enabled: !_isUploading,
              decoration: const InputDecoration(
                labelText: 'Doctor NMC Number (Optional)',
                hintText: 'e.g. NMC-12345',
                prefixIcon: Icon(Icons.badge_outlined),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildProgressIndicator() {
    final progress = _uploadProgress ?? 0.0;
    final percentage = (progress * 100).toInt();

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                'Uploading & Processing OCR...',
                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
              ),
              Text(
                '$percentage%',
                style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.primary),
              ),
            ],
          ),
          const SizedBox(height: 10),
          LinearProgressIndicator(
            value: progress > 0 ? progress : null,
            backgroundColor: Colors.grey.shade200,
            valueColor: const AlwaysStoppedAnimation<Color>(AppColors.primary),
          ),
        ],
      ),
    );
  }

  Widget _buildSubmitButton() {
    return ElevatedButton(
      onPressed: _isUploading ? null : _uploadBill,
      style: ElevatedButton.styleFrom(
        backgroundColor: AppColors.primary,
        foregroundColor: Colors.white,
        minimumSize: const Size(double.infinity, 52),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(14),
        ),
      ),
      child: _isUploading
          ? const SizedBox(
              height: 22,
              width: 22,
              child: CircularProgressIndicator(
                strokeWidth: 2.5,
                color: Colors.white,
              ),
            )
          : const Text(
              'Submit Bill & Extract OCR',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
            ),
    );
  }
}
