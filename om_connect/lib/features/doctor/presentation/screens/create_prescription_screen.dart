import 'dart:io';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:image_picker/image_picker.dart';
import '../../../../core/api/api_client.dart';
import '../../../../core/api/api_endpoints.dart';
import '../../../../core/constants/app_theme.dart';

class CreatePrescriptionScreen extends StatefulWidget {
  const CreatePrescriptionScreen({super.key});

  @override
  State<CreatePrescriptionScreen> createState() => _CreatePrescriptionScreenState();
}

class _MedicineItemInput {
  final TextEditingController nameController = TextEditingController();
  final TextEditingController dosageController = TextEditingController();
  final TextEditingController frequencyController = TextEditingController();
  final TextEditingController durationController = TextEditingController();
  final TextEditingController quantityController = TextEditingController();

  void dispose() {
    nameController.dispose();
    dosageController.dispose();
    frequencyController.dispose();
    durationController.dispose();
    quantityController.dispose();
  }
}

class _CreatePrescriptionScreenState extends State<CreatePrescriptionScreen> {
  final _formKey = GlobalKey<FormState>();
  final _patientCodeController = TextEditingController();
  final _notesController = TextEditingController();
  final ImagePicker _picker = ImagePicker();

  DateTime _selectedDate = DateTime.now();
  int _tabIndex = 0; // 0 = Camera/Scan, 1 = Manual Entry
  bool _isSubmitting = false;
  double? _uploadProgress;

  XFile? _selectedImage;
  int? _imageSizeBytes;

  final List<_MedicineItemInput> _items = [];

  @override
  void initState() {
    super.initState();
    _addItem();
  }

  @override
  void dispose() {
    _patientCodeController.dispose();
    _notesController.dispose();
    for (final item in _items) {
      item.dispose();
    }
    super.dispose();
  }

  void _addItem() {
    setState(() {
      _items.add(_MedicineItemInput());
    });
  }

  void _removeItem(int index) {
    if (_items.length > 1) {
      setState(() {
        final removed = _items.removeAt(index);
        removed.dispose();
      });
    }
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
          _imageSizeBytes = length;
        });
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Failed to capture image. Please check camera permissions.'),
            backgroundColor: AppColors.danger,
          ),
        );
      }
    }
  }

  Future<void> _submitPrescription() async {
    if (!_formKey.currentState!.validate()) return;

    if (_tabIndex == 0 && _selectedImage == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please capture or select a prescription image.'),
          backgroundColor: AppColors.danger,
        ),
      );
      return;
    }

    setState(() {
      _isSubmitting = true;
      _uploadProgress = 0.0;
    });

    final formattedDate = '${_selectedDate.year}-${_selectedDate.month.toString().padLeft(2, '0')}-${_selectedDate.day.toString().padLeft(2, '0')}';

    try {
      Response response;

      if (_tabIndex == 0) {
        // Multipart Upload for Camera Scan
        final formData = FormData.fromMap({
          'patient_ref_code': _patientCodeController.text.trim().isEmpty ? null : _patientCodeController.text.trim(),
          'prescription_date': formattedDate,
          'source_type': 'upload',
          'notes': _notesController.text.trim().isEmpty ? null : _notesController.text.trim(),
          'prescription_image': await MultipartFile.fromFile(
            _selectedImage!.path,
            filename: _selectedImage!.name,
          ),
        });

        response = await ApiClient.instance.post(
          ApiEndpoints.doctorPrescriptions,
          data: formData,
          onSendProgress: (sent, total) {
            if (total > 0 && mounted) {
              setState(() => _uploadProgress = sent / total);
            }
          },
        );
      } else {
        // JSON Payload for Manual Entry
        final payload = {
          'patient_ref_code': _patientCodeController.text.trim().isEmpty ? null : _patientCodeController.text.trim(),
          'prescription_date': formattedDate,
          'source_type': 'manual',
          'notes': _notesController.text.trim().isEmpty ? null : _notesController.text.trim(),
          'items': _items.map((item) {
            return {
              'medicine_name_raw': item.nameController.text.trim(),
              'dosage': item.dosageController.text.trim().isEmpty ? null : item.dosageController.text.trim(),
              'frequency': item.frequencyController.text.trim().isEmpty ? null : item.frequencyController.text.trim(),
              'duration_days': int.tryParse(item.durationController.text.trim()),
              'quantity': double.tryParse(item.quantityController.text.trim()) ?? 1,
            };
          }).toList(),
        };

        response = await ApiClient.instance.post(
          ApiEndpoints.doctorPrescriptions,
          data: payload,
        );
      }

      if (mounted) {
        setState(() => _isSubmitting = false);
        if (response.statusCode == 201 || response.statusCode == 200) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Prescription submitted successfully! OCR processing started.'),
              backgroundColor: AppColors.secondary,
            ),
          );
          context.pop(true);
        }
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isSubmitting = false);
        final msg = e is ApiException ? e.message : 'Failed to save prescription. Please try again.';
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(msg), backgroundColor: AppColors.danger),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('New Prescription'),
      ),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            // Mode Selector
            Container(
              decoration: BoxDecoration(
                color: AppColors.surface,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: AppColors.border),
              ),
              child: Row(
                children: [
                  Expanded(
                    child: GestureDetector(
                      onTap: () => setState(() => _tabIndex = 0),
                      child: Container(
                        padding: const EdgeInsets.symmetric(vertical: 12),
                        decoration: BoxDecoration(
                          color: _tabIndex == 0 ? AppColors.primary : Colors.transparent,
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.camera_alt_rounded, size: 18, color: _tabIndex == 0 ? Colors.white : AppColors.textSecondary),
                            const SizedBox(width: 8),
                            Text(
                              'Camera / Scan OCR',
                              style: TextStyle(
                                fontWeight: FontWeight.bold,
                                fontSize: 13,
                                color: _tabIndex == 0 ? Colors.white : AppColors.textSecondary,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                  Expanded(
                    child: GestureDetector(
                      onTap: () => setState(() => _tabIndex = 1),
                      child: Container(
                        padding: const EdgeInsets.symmetric(vertical: 12),
                        decoration: BoxDecoration(
                          color: _tabIndex == 1 ? AppColors.primary : Colors.transparent,
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.edit_note_rounded, size: 18, color: _tabIndex == 1 ? Colors.white : AppColors.textSecondary),
                            const SizedBox(width: 8),
                            Text(
                              'Manual Entry',
                              style: TextStyle(
                                fontWeight: FontWeight.bold,
                                fontSize: 13,
                                color: _tabIndex == 1 ? Colors.white : AppColors.textSecondary,
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
            const SizedBox(height: 16),

            // Patient details card
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: AppColors.surface,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: AppColors.border),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Row(
                    children: [
                      Icon(Icons.person_outline, color: AppColors.primary, size: 20),
                      SizedBox(width: 8),
                      Text('Patient & Date', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                    ],
                  ),
                  const SizedBox(height: 14),
                  TextFormField(
                    controller: _patientCodeController,
                    decoration: const InputDecoration(
                      labelText: 'Patient Ref / ID (e.g. PAT-1001)',
                      prefixIcon: Icon(Icons.badge_outlined),
                      isDense: true,
                    ),
                  ),
                  const SizedBox(height: 12),
                  ListTile(
                    contentPadding: EdgeInsets.zero,
                    dense: true,
                    title: const Text('Prescription Date', style: TextStyle(fontWeight: FontWeight.w600)),
                    subtitle: Text('${_selectedDate.year}-${_selectedDate.month.toString().padLeft(2, '0')}-${_selectedDate.day.toString().padLeft(2, '0')}'),
                    trailing: const Icon(Icons.calendar_month, color: AppColors.primary),
                    onTap: () async {
                      final picked = await showDatePicker(
                        context: context,
                        initialDate: _selectedDate,
                        firstDate: DateTime(2020),
                        lastDate: DateTime.now(),
                      );
                      if (picked != null) setState(() => _selectedDate = picked);
                    },
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),

            // Tab 0: Camera / Scan OCR
            if (_tabIndex == 0) ...[
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: AppColors.surface,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: AppColors.border),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Row(
                      children: [
                        Icon(Icons.document_scanner_outlined, color: AppColors.primary, size: 20),
                        SizedBox(width: 8),
                        Text('Prescription Photo', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                      ],
                    ),
                    const SizedBox(height: 14),
                    if (_selectedImage == null) ...[
                      Row(
                        children: [
                          Expanded(
                            child: OutlinedButton.icon(
                              onPressed: () => _pickImage(ImageSource.camera),
                              icon: const Icon(Icons.camera_alt),
                              label: const Text('Take Photo'),
                              style: OutlinedButton.styleFrom(
                                padding: const EdgeInsets.symmetric(vertical: 14),
                              ),
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: OutlinedButton.icon(
                              onPressed: () => _pickImage(ImageSource.gallery),
                              icon: const Icon(Icons.photo_library),
                              label: const Text('Gallery'),
                              style: OutlinedButton.styleFrom(
                                padding: const EdgeInsets.symmetric(vertical: 14),
                              ),
                            ),
                          ),
                        ],
                      ),
                    ] else ...[
                      Stack(
                        children: [
                          ClipRRect(
                            borderRadius: BorderRadius.circular(12),
                            child: Image.file(
                              File(_selectedImage!.path),
                              height: 220,
                              width: double.infinity,
                              fit: BoxFit.cover,
                            ),
                          ),
                          Positioned(
                            top: 8,
                            right: 8,
                            child: CircleAvatar(
                              backgroundColor: Colors.black54,
                              child: IconButton(
                                icon: const Icon(Icons.close, color: Colors.white),
                                onPressed: () => setState(() {
                                  _selectedImage = null;
                                  _imageSizeBytes = null;
                                }),
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      Text(
                        'Image Size: ${(_imageSizeBytes! / (1024 * 1024)).toStringAsFixed(2)} MB',
                        style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
                      ),
                    ],
                  ],
                ),
              ),
            ] else ...[
              // Tab 1: Manual Medicines Entry
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text('Prescribed Medicines', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                  TextButton.icon(
                    onPressed: _addItem,
                    icon: const Icon(Icons.add, size: 18),
                    label: const Text('Add Medicine'),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              ...List.generate(_items.length, (index) {
                final item = _items[index];
                return Container(
                  margin: const EdgeInsets.only(bottom: 12),
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: AppColors.surface,
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(color: AppColors.border),
                  ),
                  child: Column(
                    children: [
                      Row(
                        children: [
                          CircleAvatar(
                            radius: 12,
                            backgroundColor: AppColors.primary.withValues(alpha: 0.15),
                            child: Text('${index + 1}', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.primary)),
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: TextFormField(
                              controller: item.nameController,
                              decoration: const InputDecoration(
                                labelText: 'Medicine Name (e.g. OmParacetamol 500mg)',
                                isDense: true,
                              ),
                              validator: (v) => v == null || v.trim().isEmpty ? 'Medicine name is required' : null,
                            ),
                          ),
                          if (_items.length > 1)
                            IconButton(
                              icon: const Icon(Icons.delete_outline, color: AppColors.danger),
                              onPressed: () => _removeItem(index),
                            ),
                        ],
                      ),
                      const SizedBox(height: 10),
                      Row(
                        children: [
                          Expanded(
                            child: TextFormField(
                              controller: item.dosageController,
                              decoration: const InputDecoration(labelText: 'Dosage (e.g. 500mg)', isDense: true),
                            ),
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: TextFormField(
                              controller: item.frequencyController,
                              decoration: const InputDecoration(labelText: 'Freq (e.g. 1-0-1)', isDense: true),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 10),
                      Row(
                        children: [
                          Expanded(
                            child: TextFormField(
                              controller: item.durationController,
                              keyboardType: TextInputType.number,
                              decoration: const InputDecoration(labelText: 'Duration (Days)', isDense: true),
                            ),
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: TextFormField(
                              controller: item.quantityController,
                              keyboardType: TextInputType.number,
                              decoration: const InputDecoration(labelText: 'Qty', isDense: true),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                );
              }),
            ],

            const SizedBox(height: 16),
            TextFormField(
              controller: _notesController,
              maxLines: 3,
              decoration: const InputDecoration(
                labelText: 'Clinical Notes / Instructions (Optional)',
                alignLabelWithHint: true,
              ),
            ),
            const SizedBox(height: 20),

            if (_isSubmitting && _uploadProgress != null && _uploadProgress! > 0) ...[
              LinearProgressIndicator(value: _uploadProgress, color: AppColors.primary),
              const SizedBox(height: 8),
              Text(
                'Uploading & scanning... ${(_uploadProgress! * 100).toInt()}%',
                textAlign: TextAlign.center,
                style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
              ),
              const SizedBox(height: 12),
            ],

            ElevatedButton(
              onPressed: _isSubmitting ? null : _submitPrescription,
              child: _isSubmitting
                  ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                  : Text(_tabIndex == 0 ? 'Upload & Scan Prescription' : 'Save Prescription'),
            ),
            const SizedBox(height: 32),
          ],
        ),
      ),
    );
  }
}
