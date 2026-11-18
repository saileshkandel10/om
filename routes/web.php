<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdvertisementController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\CampaignController;
use App\Http\Controllers\Admin\DistrictSettingsController;
use App\Http\Controllers\Admin\DoctorController;
use App\Http\Controllers\Admin\HealthAnalytics\AnalysisLogController;
use App\Http\Controllers\Admin\HealthAnalytics\ClinicalReviewController;
use App\Http\Controllers\Admin\HealthAnalytics\HealthAnalyticsDashboardController;
use App\Http\Controllers\Admin\HealthAnalytics\HealthAnalyticsSettingsController;
use App\Http\Controllers\Admin\HealthAnalytics\HealthCategoryTrendsController;
use App\Http\Controllers\Admin\HealthAnalytics\MedicationAnalysisController;
use App\Http\Controllers\Admin\HealthAnalytics\MedicineMappingController;
use App\Http\Controllers\Admin\HealthAnalytics\PatientAnalyticsController;
use App\Http\Controllers\Admin\IllnessCategoryController;
use App\Http\Controllers\Admin\NoticeController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OcrJobController;
use App\Http\Controllers\Admin\OcrReviewQueueController;
use App\Http\Controllers\Admin\OtherProductController;
use App\Http\Controllers\Admin\PatientIllnessReportController;
use App\Http\Controllers\Admin\PharmacistQuizController;
use App\Http\Controllers\Admin\PharmacyController;
use App\Http\Controllers\Admin\PharmacySourcingRecordController;
use App\Http\Controllers\Admin\PrescriptionController;
use App\Http\Controllers\Admin\PrescriptionItemController;
use App\Http\Controllers\Admin\ProductBatchController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductDemandReportController;
use App\Http\Controllers\Admin\PurchaseBillController;
use App\Http\Controllers\Admin\QuizCategoryController;
use App\Http\Controllers\Admin\QuizQuestionController;
use App\Http\Controllers\Admin\QuizSettingController;
use App\Http\Controllers\Admin\RewardRedemptionController;
use App\Http\Controllers\Admin\RewardRuleController;
use App\Http\Controllers\Admin\RewardTransactionController;
use App\Http\Controllers\Admin\RewardWalletController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\ShareholderController;
use App\Http\Controllers\Admin\ShareholderStatementController;
use App\Http\Controllers\Admin\SystemSettingsController;
use App\Http\Controllers\Admin\UploadedFileController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\DoubleTickController;
use App\Http\Controllers\Patient\PatientDashboardController;
use App\Http\Controllers\Pharmacy\PharmacyWebDashboardController;
use App\Http\Controllers\Shareholder\ShareholderDashboardController;
use App\Jobs\ProcessBillOcr;
use App\Models\Pharmacy;
use App\Models\PurchaseBill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/test-whatsapp', [DoubleTickController::class, 'sendSimpleTemplate']);

Route::get('/', fn () => auth()->check() ? redirect()->route('dashboard') : redirect()->route('login'))->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', function () {
        $user = auth()->user();

        if ($user->hasRole('admin') || $user->hasRole('staff')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasRole('doctor')) {
            return redirect()->route('doctor.dashboard');
        }

        if ($user->hasRole('shareholder')) {
            return redirect()->route('shareholder.dashboard');
        }

        if ($user->hasRole('pharmacy')) {
            return redirect()->route('pharmacy.dashboard');
        }

        return redirect()->route('patient.dashboard');
    })->name('dashboard');

    // ------------------------------------------------------------------------
    // Patient / Customer Routes
    // ------------------------------------------------------------------------
    Route::prefix('patient')->name('patient.')->group(function () {
        Route::get('dashboard', [PatientDashboardController::class, 'index'])->name('dashboard');

        // Patient purchase bills
        Route::prefix('bills')->name('bills.')->group(function () {
            Route::get('/', function (Request $request) {
                $user = auth()->user();
                $bills = PurchaseBill::where('user_id', $user->id)->with('pharmacy')->latest()->paginate(15);

                return view('shareholder.bills.index', compact('bills'));
            })->name('index');

            Route::get('create', function () {
                $pharmacies = Pharmacy::where('status', 'active')->orWhere('status', 'approved')->orderBy('store_name')->get();

                return view('shareholder.bills.create', compact('pharmacies'));
            })->name('create');

            Route::post('/', function (Request $request) {
                $validated = $request->validate([
                    'pharmacy_id' => ['nullable', 'exists:pharmacies,id'],
                    'invoice_number' => ['nullable', 'string', 'max:255'],
                    'invoice_date' => ['nullable', 'date'],
                    'total_amount' => ['nullable', 'numeric', 'min:0'],
                    'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
                ]);

                $billData = [
                    'pharmacy_id' => $validated['pharmacy_id'] ?? null,
                    'invoice_number' => $validated['invoice_number'] ?? null,
                    'invoice_date' => $validated['invoice_date'] ?? now(),
                    'total_amount' => $validated['total_amount'] ?? 0,
                    'subtotal_amount' => $validated['total_amount'] ?? 0,
                    'ocr_status' => 'pending',
                    'validation_status' => 'pending',
                ];

                if ($request->hasFile('receipt')) {
                    $file = $request->file('receipt');
                    $path = $file->store('receipts/'.auth()->id(), 'public');

                    $uploadedFile = auth()->user()->uploadedFiles()->create([
                        'module_name' => 'purchase_bill',
                        'file_type' => 'receipt',
                        'file_path' => $path,
                        'mime_type' => $file->getClientMimeType(),
                        'file_size' => $file->getSize(),
                        'uploaded_at' => now(),
                    ]);

                    $billData['uploaded_file_id'] = $uploadedFile->id;
                }

                $bill = auth()->user()->purchaseBills()->create($billData);

                if ($bill->uploaded_file_id) {
                    ProcessBillOcr::dispatch($bill);
                }

                return redirect()->route('patient.bills.show', $bill)
                    ->with('success', __('Bill submitted successfully. OCR processing started.'));
            })->name('store');

            Route::get('{purchaseBill}', function (PurchaseBill $purchaseBill) {
                if ($purchaseBill->user_id !== auth()->id()) {
                    abort(403);
                }

                $purchaseBill->load(['pharmacy', 'items', 'rewardTransactions']);

                return view('shareholder.bills.show', compact('purchaseBill'));
            })->name('show');
        });
    });

    // ------------------------------------------------------------------------
    // Pharmacy Routes
    // ------------------------------------------------------------------------
    Route::prefix('pharmacy')->name('pharmacy.')->middleware('role:pharmacy,admin')->group(function () {
        Route::get('dashboard', [PharmacyWebDashboardController::class, 'index'])->name('dashboard');
    });

    // ------------------------------------------------------------------------
    // Admin Routes
    // ------------------------------------------------------------------------
    Route::prefix('admin')->name('admin.')->middleware('role:admin,staff')->group(function () {
        // Dashboard
        Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('profile', [AdminDashboardController::class, 'profile'])->name('profile');
        Route::post('profile', [AdminDashboardController::class, 'updateProfile'])->name('profile.update');

        // User Management
        Route::get('users/{user}/statement', [ShareholderStatementController::class, 'generate'])->name('users.statement');
        Route::resource('users', UserController::class);
        Route::post('shareholders/import', [ShareholderController::class, 'import'])->name('shareholders.import');
        Route::get('shareholders/sample', [ShareholderController::class, 'sample'])->name('shareholders.sample');
        Route::get('shareholders/export', [ShareholderController::class, 'export'])->name('shareholders.export');
        Route::resource('shareholders', ShareholderController::class)->except(['create']);
        Route::resource('pharmacies', PharmacyController::class);
        Route::post('doctors/import', [DoctorController::class, 'import'])->name('doctors.import');
        Route::get('doctors/sample', [DoctorController::class, 'sample'])->name('doctors.sample');
        Route::get('doctors/export', [DoctorController::class, 'export'])->name('doctors.export');
        Route::resource('doctors', DoctorController::class);

        // Roles & Permissions Management
        Route::get('roles/export', [RoleController::class, 'export'])->name('roles.export');
        Route::put('roles/{role}/permissions', [RoleController::class, 'updatePermissions'])->name('roles.permissions');
        Route::resource('roles', RoleController::class);

        // Product Management
        Route::get('other-products/sample-template', [OtherProductController::class, 'sampleTemplate'])->name('other-products.sample-template');
        Route::get('other-products/export', [OtherProductController::class, 'export'])->name('other-products.export');
        Route::post('other-products/import', [OtherProductController::class, 'import'])->name('other-products.import');
        Route::post('other-products/preview-import', [OtherProductController::class, 'previewImport'])->name('other-products.preview-import');
        Route::post('other-products/auto-suggest', [OtherProductController::class, 'autoSuggestAll'])->name('other-products.auto-suggest');
        Route::get('other-products/suggestions', [OtherProductController::class, 'suggestions'])->name('other-products.suggestions');
        Route::post('other-products/batch-map', [OtherProductController::class, 'batchMap'])->name('other-products.batch-map');
        Route::post('other-products/{otherProduct}/map', [OtherProductController::class, 'map'])->name('other-products.map');
        Route::resource('other-products', OtherProductController::class)->except(['create', 'show', 'edit']);
        Route::get('products/sample-template', [ProductController::class, 'sampleTemplate'])->name('products.sample-template');
        Route::get('products/export', [ProductController::class, 'export'])->name('products.export');
        Route::post('products/preview-import', [ProductController::class, 'previewImport'])->name('products.preview-import');
        Route::post('products/import', [ProductController::class, 'import'])->name('products.import');
        Route::resource('products', ProductController::class);
        Route::resource('product-categories', ProductCategoryController::class)->except(['show']);
        Route::post('illness-categories/{illnessCategory}/products/link', [IllnessCategoryController::class, 'linkProducts'])->name('illness-categories.products.link');
        Route::post('illness-categories/{illnessCategory}/products/{product}/unlink', [IllnessCategoryController::class, 'unlinkProduct'])->name('illness-categories.products.unlink');
        Route::resource('illness-categories', IllnessCategoryController::class)->except(['show']);
        Route::resource('product-batches', ProductBatchController::class);

        // OCR & Bills
        Route::resource('purchase-bills', PurchaseBillController::class)->only(['index', 'show', 'destroy']);
        Route::post('purchase-bills/{purchaseBill}/rescan-ocr', [PurchaseBillController::class, 'rescanOcr'])->name('purchase-bills.rescan-ocr');
        Route::post('purchase-bills/{purchaseBill}/verify', [PurchaseBillController::class, 'verify'])->name('purchase-bills.verify');
        Route::get('fraud-detection', [PurchaseBillController::class, 'fraudDetection'])->name('fraud-detection');
        Route::resource('ocr-jobs', OcrJobController::class)->only(['index', 'show']);
        Route::resource('ocr-review-queue', OcrReviewQueueController::class)->only(['index', 'show', 'update']);

        // Rewards & Loyalty
        Route::resource('reward-rules', RewardRuleController::class);
        Route::resource('reward-transactions', RewardTransactionController::class)->only(['index', 'show']);
        Route::resource('reward-wallets', RewardWalletController::class)->only(['index', 'show']);
        Route::resource('redemptions', RewardRedemptionController::class)->only(['index', 'show', 'update']);

        // Campaigns, Advertisements & Notices
        Route::get('campaigns/export', [CampaignController::class, 'export'])->name('campaigns.export');
        Route::resource('campaigns', CampaignController::class);
        Route::post('advertisements/{advertisement}/toggle-active', [AdvertisementController::class, 'toggleActive'])->name('advertisements.toggle-active');
        Route::resource('advertisements', AdvertisementController::class);
        Route::get('notices/export', [NoticeController::class, 'export'])->name('notices.export');
        Route::resource('notices', NoticeController::class);
        Route::resource('notifications', NotificationController::class)->only(['index', 'show', 'destroy']);

        // Shareholder Zone
        Route::get('shareholder-zone/agm-notices', [NoticeController::class, 'agmNotices'])->name('agm-notices');
        Route::get('shareholder-zone/financial-reports', [NoticeController::class, 'financialReports'])->name('financial-reports');
        Route::get('shareholder-zone/exclusive-rewards', [ShareholderController::class, 'exclusiveRewards'])->name('exclusive-rewards');

        // Pharmacy Operations
        Route::prefix('pharmacist-quiz')->name('pharmacist-quiz.')->group(function () {
            Route::get('/', [PharmacistQuizController::class, 'index'])->name('index');
            Route::get('results', [PharmacistQuizController::class, 'results'])->name('results.index');
            Route::get('results/{attempt}', [PharmacistQuizController::class, 'showAttempt'])->name('results.show');
            Route::get('settings', [QuizSettingController::class, 'index'])->name('settings.index');
            Route::put('settings', [QuizSettingController::class, 'update'])->name('settings.update');
            Route::post('categories/{category}/toggle-status', [QuizCategoryController::class, 'toggleStatus'])->name('categories.toggle-status');
            Route::resource('categories', QuizCategoryController::class)->except(['create', 'show', 'edit']);
            Route::post('questions/{question}/duplicate', [QuizQuestionController::class, 'duplicate'])->name('questions.duplicate');
            Route::post('questions/{question}/toggle-status', [QuizQuestionController::class, 'toggleStatus'])->name('questions.toggle-status');
            Route::resource('questions', QuizQuestionController::class);
        });
        Route::resource('sourcing-records', PharmacySourcingRecordController::class);

        // Doctors & Prescriptions
        Route::resource('prescriptions', PrescriptionController::class)->only(['index', 'show', 'destroy']);
        Route::resource('prescription-items', PrescriptionItemController::class)->only(['index', 'show']);

        // Analytics & Reports
        Route::get('reports/product-demand', [ProductDemandReportController::class, 'index'])->name('reports.product-demand');
        Route::get('reports/product-demand/export', [ProductDemandReportController::class, 'export'])->name('reports.product-demand.export');
        Route::get('reports/product-demand/print', [ProductDemandReportController::class, 'print'])->name('reports.product-demand.print');
        Route::get('reports/product-demand/product/{product}', [ProductDemandReportController::class, 'productDrilldown'])->name('reports.product-demand.product-modal');
        Route::get('reports/product-demand/district/{district}', [ProductDemandReportController::class, 'districtDrilldown'])->name('reports.product-demand.district-modal');

        Route::get('reports/illness-categories', [PatientIllnessReportController::class, 'index'])->name('reports.illness-categories');
        Route::get('reports/illness-categories/export', [PatientIllnessReportController::class, 'export'])->name('reports.illness-categories.export');
        Route::get('reports/illness-categories/print', [PatientIllnessReportController::class, 'print'])->name('reports.illness-categories.print');
        Route::post('reports/illness-categories/sync', [PatientIllnessReportController::class, 'sync'])->name('reports.illness-categories.sync');
        Route::get('reports/illness-categories/patient/{user}', [PatientIllnessReportController::class, 'patientModal'])->name('reports.illness-categories.patient-modal');
        Route::get('analytics/sales', [AnalyticsController::class, 'sales'])->name('analytics.sales');
        Route::get('analytics/product-demand', [ProductDemandReportController::class, 'index'])->name('analytics.product-demand');
        Route::get('analytics/user-behaviour', [AnalyticsController::class, 'userBehaviour'])->name('analytics.user-behaviour');
        Route::get('analytics/reports', [AnalyticsController::class, 'reports'])->name('analytics.reports');
        Route::get('analytics/export', [AnalyticsController::class, 'export'])->name('analytics.export');

        // Location Master Data Settings (Settings -> Locations -> Nepal Districts)
        Route::get('settings/locations/districts/export', [DistrictSettingsController::class, 'export'])->name('settings.locations.districts.export');
        Route::get('settings/locations/districts', [DistrictSettingsController::class, 'index'])->name('settings.locations.districts');
        Route::put('settings/locations/districts/{district}', [DistrictSettingsController::class, 'update'])->name('settings.locations.districts.update');
        Route::post('settings/locations/districts/normalize', [DistrictSettingsController::class, 'normalize'])->name('settings.locations.districts.normalize');
        Route::post('settings/locations/districts/map-location', [DistrictSettingsController::class, 'mapLocation'])->name('settings.locations.districts.map-location');
        Route::post('settings/locations/bills/{bill}/map', [DistrictSettingsController::class, 'mapBill'])->name('settings.locations.bills.map');
        Route::post('settings/locations/bills/{bill}/skip', [DistrictSettingsController::class, 'skipBill'])->name('settings.locations.bills.skip');
        Route::post('settings/locations/bills/bulk', [DistrictSettingsController::class, 'bulkAction'])->name('settings.locations.bills.bulk');
        Route::get('settings/locations/bills/{bill}/modal', [DistrictSettingsController::class, 'billModal'])->name('settings.locations.bills.modal');

        // ------------------------------------------------------------------------
        // Health Analytics Module (Admin Panel Only)
        // ------------------------------------------------------------------------
        Route::prefix('health-analytics')->name('health-analytics.')->group(function () {
            // 1. Dashboard
            Route::get('dashboard', [HealthAnalyticsDashboardController::class, 'index'])->name('dashboard');

            // 2. Patient Health Analytics
            Route::get('patients', [PatientAnalyticsController::class, 'index'])->name('patients.index');
            Route::get('patients/{patient}', [PatientAnalyticsController::class, 'show'])->name('patients.show');
            Route::post('patients/{patient}/reanalyze', [PatientAnalyticsController::class, 'reanalyze'])->name('patients.reanalyze');

            // 3. Medication Analysis
            Route::get('medications', [MedicationAnalysisController::class, 'index'])->name('medications.index');

            // 4. Health Category Trends
            Route::get('trends', [HealthCategoryTrendsController::class, 'index'])->name('trends.index');

            // 5. Medicine–Condition Mapping
            Route::get('mappings', [MedicineMappingController::class, 'index'])->name('mappings.index');
            Route::post('mappings', [MedicineMappingController::class, 'store'])->name('mappings.store');
            Route::delete('mappings/{mapping}', [MedicineMappingController::class, 'destroy'])->name('mappings.destroy');

            // 6. Clinical Review
            Route::get('reviews', [ClinicalReviewController::class, 'index'])->name('reviews.index');
            Route::post('reviews/{analytic}/submit', [ClinicalReviewController::class, 'submit'])->name('reviews.submit');

            // 7. Analysis Logs
            Route::get('logs', [AnalysisLogController::class, 'index'])->name('logs.index');

            // 8. Settings
            Route::get('settings', [HealthAnalyticsSettingsController::class, 'index'])->name('settings.index');
            Route::post('settings', [HealthAnalyticsSettingsController::class, 'update'])->name('settings.update');
        });

        // System Settings & Rules
        Route::get('settings/system', [SystemSettingsController::class, 'index'])->name('settings.system');
        Route::post('settings/system', [SystemSettingsController::class, 'update'])->name('settings.system.update');

        // System
        Route::resource('uploaded-files', UploadedFileController::class)->only(['index', 'show', 'destroy']);
        Route::resource('audit-logs', AuditLogController::class)->only(['index', 'show']);
    });

    // ------------------------------------------------------------------------
    // Doctor Routes
    // ------------------------------------------------------------------------
    Route::prefix('doctor')->name('doctor.')->middleware('role:doctor,admin')->group(function () {
        Route::view('dashboard', 'dashboards.doctor')->name('dashboard');
        Route::get('profile', function () {
            $user = auth()->user()->load(['doctor.prescriptions', 'roles']);
            $doctor = $user->doctor;
            $totalPrescriptions = $doctor?->prescriptions()->count() ?? 0;
            $recentPrescriptions = $doctor?->prescriptions()->latest()->take(10)->get() ?? collect();

            return view('doctor.profile', compact('user', 'doctor', 'totalPrescriptions', 'recentPrescriptions'));
        })->name('profile');
    });

    // ------------------------------------------------------------------------
    // Shareholder Routes
    // ------------------------------------------------------------------------
    Route::prefix('shareholder')->name('shareholder.')->middleware('role:shareholder,admin')->group(function () {
        // Dashboard
        Route::get('dashboard', [ShareholderDashboardController::class, 'index'])->name('dashboard');
        Route::get('profile', function () {
            $user = auth()->user()->load(['shareholderAccount.master', 'roles', 'rewardWallet']);
            $shareholder = $user->shareholderAccount?->master;
            $account = $user->shareholderAccount;
            $wallet = $user->rewardWallet;
            $totalBills = PurchaseBill::where('user_id', $user->id)->count();

            return view('shareholder.profile', compact('user', 'shareholder', 'account', 'wallet', 'totalBills'));
        })->name('profile');

        // Purchase Bills
        Route::prefix('bills')->name('bills.')->group(function () {
            Route::get('/', function (Request $request) {
                $user = auth()->user();
                $query = PurchaseBill::where('user_id', $user->id)->with('pharmacy');

                if ($request->filled('search')) {
                    $query->where('invoice_number', 'like', "%{$request->search}%");
                }

                if ($request->filled('ocr_status')) {
                    $query->where('ocr_status', $request->ocr_status);
                }

                if ($request->filled('validation_status')) {
                    $query->where('validation_status', $request->validation_status);
                }

                $bills = $query->latest()->paginate(15);

                return view('shareholder.bills.index', compact('bills'));
            })->name('index');

            Route::get('create', function () {
                $pharmacies = Pharmacy::where('status', 'active')->orWhere('status', 'approved')->orderBy('store_name')->get();

                return view('shareholder.bills.create', compact('pharmacies'));
            })->name('create');

            Route::post('/', function (Request $request) {
                $validated = $request->validate([
                    'pharmacy_id' => ['nullable', 'exists:pharmacies,id'],
                    'invoice_number' => ['nullable', 'string', 'max:255'],
                    'invoice_date' => ['nullable', 'date'],
                    'total_amount' => ['nullable', 'numeric', 'min:0'],
                    'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
                ]);

                $billData = [
                    'pharmacy_id' => $validated['pharmacy_id'] ?? null,
                    'invoice_number' => $validated['invoice_number'] ?? null,
                    'invoice_date' => $validated['invoice_date'] ?? now(),
                    'total_amount' => $validated['total_amount'] ?? 0,
                    'subtotal_amount' => $validated['total_amount'] ?? 0,
                    'ocr_status' => 'pending',
                    'validation_status' => 'pending',
                ];

                // Handle receipt upload
                if ($request->hasFile('receipt')) {
                    $file = $request->file('receipt');
                    $path = $file->store('receipts/'.auth()->id(), 'public');

                    $uploadedFile = auth()->user()->uploadedFiles()->create([
                        'module_name' => 'purchase_bill',
                        'file_type' => 'receipt',
                        'file_path' => $path,
                        'mime_type' => $file->getClientMimeType(),
                        'file_size' => $file->getSize(),
                        'uploaded_at' => now(),
                    ]);

                    $billData['uploaded_file_id'] = $uploadedFile->id;
                }

                $bill = auth()->user()->purchaseBills()->create($billData);

                // Dispatch OCR processing if a receipt was uploaded
                if ($bill->uploaded_file_id) {
                    ProcessBillOcr::dispatch($bill);
                }

                return redirect()->route('shareholder.bills.show', $bill)
                    ->with('success', __('Bill submitted successfully. It will be processed shortly.'));
            })->name('store');

            Route::get('{purchaseBill}', function (PurchaseBill $purchaseBill) {
                if ($purchaseBill->user_id !== auth()->id()) {
                    abort(403);
                }

                $purchaseBill->load(['pharmacy', 'items', 'rewardTransactions']);

                return view('shareholder.bills.show', compact('purchaseBill'));
            })->name('show');
        });
    });

});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
