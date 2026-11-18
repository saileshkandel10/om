<?php

use App\Http\Controllers\Api\V1\AdvertisementController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CampaignController;
use App\Http\Controllers\Api\V1\DoctorController;
use App\Http\Controllers\Api\V1\NoticeController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\PatientIllnessReportApiController;
use App\Http\Controllers\Api\V1\PharmacyController;
use App\Http\Controllers\Api\V1\PharmacyQuizController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ProductDemandApiController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\PurchaseBillController;
use App\Http\Controllers\Api\V1\RewardController;
use App\Http\Controllers\Api\V1\ShareholderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Version 1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // ---------------------------------------------------------------
    // Public (unauthenticated) routes
    // ---------------------------------------------------------------
    Route::post('auth/register', [AuthController::class, 'register'])->name('api.v1.auth.register');
    Route::post('auth/login', [AuthController::class, 'login'])->name('api.v1.auth.login');

    // Public reports endpoint access
    Route::prefix('reports')->group(function () {
        Route::get('illness-summary', [PatientIllnessReportApiController::class, 'summary']);
        Route::get('illness-patients', [PatientIllnessReportApiController::class, 'patients']);
        Route::get('multi-illness', [PatientIllnessReportApiController::class, 'multiIllness']);
        Route::get('illness-trends', [PatientIllnessReportApiController::class, 'trends']);
        Route::get('illness-combinations', [PatientIllnessReportApiController::class, 'combinations']);
        Route::get('export/excel', [PatientIllnessReportApiController::class, 'exportExcel']);
        Route::get('export/csv', [PatientIllnessReportApiController::class, 'exportCsv']);
        Route::get('export/pdf', [PatientIllnessReportApiController::class, 'exportPdf']);
    });

    // ---------------------------------------------------------------
    // Authenticated routes
    // ---------------------------------------------------------------
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::post('auth/logout', [AuthController::class, 'logout'])->name('api.v1.auth.logout');
        Route::get('auth/user', [AuthController::class, 'user'])->name('api.v1.auth.user');

        // Profile
        Route::get('profile', [ProfileController::class, 'show'])->name('api.v1.profile.show');
        Route::put('profile', [ProfileController::class, 'update'])->name('api.v1.profile.update');
        Route::post('profile/avatar', [ProfileController::class, 'updateAvatar'])->name('api.v1.profile.avatar');

        // Products
        Route::get('products/categories', [ProductController::class, 'categories'])->name('api.v1.products.categories');
        Route::get('products/illness-categories', [ProductController::class, 'illnessCategories'])->name('api.v1.products.illness-categories');
        Route::get('products', [ProductController::class, 'index'])->name('api.v1.products.index');
        Route::get('products/{product}', [ProductController::class, 'show'])->name('api.v1.products.show');

        // Purchase Bills
        Route::get('bills', [PurchaseBillController::class, 'index'])->name('api.v1.bills.index');
        Route::post('bills', [PurchaseBillController::class, 'store'])->name('api.v1.bills.store');
        Route::get('bills/{bill}', [PurchaseBillController::class, 'show'])->name('api.v1.bills.show');

        // Advertisements & Role-based Banners
        Route::get('advertisements', [AdvertisementController::class, 'index'])->name('api.v1.advertisements.index');
        Route::post('advertisements/{advertisement}/impression', [AdvertisementController::class, 'recordImpression'])->name('api.v1.advertisements.impression');
        Route::post('advertisements/{advertisement}/click', [AdvertisementController::class, 'recordClick'])->name('api.v1.advertisements.click');

        // Rewards
        Route::get('rewards/wallet', [RewardController::class, 'wallet'])->name('api.v1.rewards.wallet');
        Route::get('rewards/transactions', [RewardController::class, 'transactions'])->name('api.v1.rewards.transactions');
        Route::post('rewards/redeem', [RewardController::class, 'redeem'])->name('api.v1.rewards.redeem');
        Route::get('rewards/redemptions', [RewardController::class, 'redemptions'])->name('api.v1.rewards.redemptions');

        // Notifications
        Route::get('notifications', [NotificationController::class, 'index'])->name('api.v1.notifications.index');
        Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('api.v1.notifications.unread-count');
        Route::put('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('api.v1.notifications.read-all');
        Route::put('notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('api.v1.notifications.read');

        // Notices
        Route::get('notices', [NoticeController::class, 'index'])->name('api.v1.notices.index');
        Route::get('notices/{notice}', [NoticeController::class, 'show'])->name('api.v1.notices.show');

        // Campaigns
        Route::get('campaigns', [CampaignController::class, 'index'])->name('api.v1.campaigns.index');
        Route::get('campaigns/{campaign}', [CampaignController::class, 'show'])->name('api.v1.campaigns.show');

        // Illness Category & Patient Reports API
        Route::prefix('reports')->name('api.v1.reports.')->group(function () {
            Route::get('illness-summary', [PatientIllnessReportApiController::class, 'summary'])->name('illness-summary');
            Route::get('illness-patients', [PatientIllnessReportApiController::class, 'patients'])->name('illness-patients');
            Route::get('multi-illness', [PatientIllnessReportApiController::class, 'multiIllness'])->name('multi-illness');
            Route::get('illness-trends', [PatientIllnessReportApiController::class, 'trends'])->name('illness-trends');
            Route::get('illness-combinations', [PatientIllnessReportApiController::class, 'combinations'])->name('illness-combinations');
            Route::get('export/excel', [PatientIllnessReportApiController::class, 'exportExcel'])->name('export.excel');
            Route::get('export/csv', [PatientIllnessReportApiController::class, 'exportCsv'])->name('export.csv');
            Route::get('export/pdf', [PatientIllnessReportApiController::class, 'exportPdf'])->name('export.pdf');

            // Product Demand & Geographic Purchasing Behaviour API
            Route::get('product-demand', [ProductDemandApiController::class, 'index'])->name('product-demand');
            Route::get('trending-products', [ProductDemandApiController::class, 'trending'])->name('trending-products');
            Route::get('top-districts', [ProductDemandApiController::class, 'topDistricts'])->name('top-districts');
            Route::get('province-demand', [ProductDemandApiController::class, 'provinceDemand'])->name('province-demand');
            Route::get('product-district-demand', [ProductDemandApiController::class, 'productDistrictDemand'])->name('product-district-demand');
            Route::get('customer-behaviour', [ProductDemandApiController::class, 'customerBehaviour'])->name('customer-behaviour');
            Route::get('purchase-frequency', [ProductDemandApiController::class, 'purchaseFrequency'])->name('purchase-frequency');
            Route::get('product-growth', [ProductDemandApiController::class, 'productGrowth'])->name('product-growth');
            Route::get('district-coverage', [ProductDemandApiController::class, 'districtCoverage'])->name('district-coverage');
            Route::get('purchase-time-analysis', [ProductDemandApiController::class, 'purchaseTimeAnalysis'])->name('purchase-time-analysis');
            Route::get('export/product-demand', [ProductDemandApiController::class, 'export'])->name('export.product-demand');
        });

        // ---------------------------------------------------------------
        // Role-specific routes
        // ---------------------------------------------------------------

        // Shareholder routes
        Route::middleware('role:shareholder')->prefix('shareholder')->name('api.v1.shareholder.')->group(function () {
            Route::get('dashboard', [ShareholderController::class, 'dashboard'])->name('dashboard');
            Route::post('verify-boid', [ShareholderController::class, 'verifyBoid'])->name('verify-boid');
            Route::get('account', [ShareholderController::class, 'account'])->name('account');
        });

        // Pharmacy routes
        Route::middleware('role:pharmacy')->prefix('pharmacy')->name('api.v1.pharmacy.')->group(function () {
            Route::get('dashboard', [PharmacyController::class, 'dashboard'])->name('dashboard');
            Route::get('profile', [PharmacyController::class, 'profile'])->name('profile');
            Route::put('profile', [PharmacyController::class, 'updateProfile'])->name('profile.update');
            Route::get('sourcing-records', [PharmacyController::class, 'sourcingRecords'])->name('sourcing-records');

            // Pharmacist Quiz Module
            Route::prefix('quizzes')->name('quizzes.')->group(function () {
                Route::get('/', [PharmacyQuizController::class, 'index'])->name('index');
                Route::get('settings', [PharmacyQuizController::class, 'settings'])->name('settings');
                Route::get('categories', [PharmacyQuizController::class, 'categories'])->name('categories');
                Route::get('my-attempts', [PharmacyQuizController::class, 'myAttempts'])->name('my-attempts');
                Route::get('{quiz}', [PharmacyQuizController::class, 'show'])->name('show');
                Route::post('{quiz}/start', [PharmacyQuizController::class, 'start'])->name('start');
                Route::post('attempts/{attempt}/submit', [PharmacyQuizController::class, 'submit'])->name('attempts.submit');
                Route::get('attempts/{attempt}', [PharmacyQuizController::class, 'attemptDetail'])->name('attempts.detail');
            });
        });

        // Doctor routes
        Route::middleware('role:doctor')->prefix('doctor')->name('api.v1.doctor.')->group(function () {
            Route::get('dashboard', [DoctorController::class, 'dashboard'])->name('dashboard');
            Route::get('prescriptions', [DoctorController::class, 'prescriptions'])->name('prescriptions.index');
            Route::post('prescriptions', [DoctorController::class, 'storePrescription'])->name('prescriptions.store');
            Route::get('prescriptions/{prescription}', [DoctorController::class, 'showPrescription'])->name('prescriptions.show');
        });
    });
});
