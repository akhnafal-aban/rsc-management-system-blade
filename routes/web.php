<?php

use App\Http\Controllers\Admin\BusinessReportController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\StaffManagementController;
use App\Http\Controllers\Admin\StaffScheduleController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Main\AttendanceController;
use App\Http\Controllers\Main\DashboardController;
use App\Http\Controllers\Main\MemberController;
use App\Http\Controllers\Main\NonMemberVisitController;
use App\Http\Controllers\Main\ReportController;
use App\Http\Controllers\Main\StaffShiftController;
use App\Http\Controllers\NotificationController;
use App\Http\Middleware\RequireShiftConfirmation;
use Illuminate\Support\Facades\Route;

// Guest-only routes
Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect()->route('login');
    });
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Staff Shift Confirmation (must be first for staff)
    Route::get('/staff/shift/confirm', [StaffShiftController::class, 'showConfirmationPage'])->name('staff.shift.page');
    Route::post('/staff/shift/confirm', [StaffShiftController::class, 'confirm'])->name('staff.shift.store');
    Route::get('/staff/shift/schedule', [StaffShiftController::class, 'mySchedule'])->name('staff.shift.schedule');

    // Routes that require shift confirmation for staff
    Route::middleware(RequireShiftConfirmation::class)->group(function () {
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Members
        Route::get('/member/search', [MemberController::class, 'searchMembers'])->name('member.search');
        Route::get('/member/extend', [MemberController::class, 'extend'])->name('member.extend');
        Route::post('/member/extend', [MemberController::class, 'storeExtend'])->name('member.extend.store');
        Route::post('/member/bulk-update-status', [MemberController::class, 'bulkUpdateStatuses'])->name('member.bulk-update-status');
        Route::get('/member/registration-costs', [MemberController::class, 'getRegistrationCosts'])->name('member.registration-costs');

        Route::get('/member/export', [MemberController::class, 'export'])->name('member.export');

        Route::post('/member/{member}/suspend', [MemberController::class, 'suspend'])->name('member.suspend');
        Route::post('/member/{member}/activate', [MemberController::class, 'activate'])->name('member.activate');

        // TARUH PALING BAWAH
        Route::resource('member', MemberController::class);

        // Attendance
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('/attendance/check-in', [AttendanceController::class, 'checkInPage'])->name('attendance.check-in');
        Route::post('/attendance/checkin', [AttendanceController::class, 'checkIn'])->name('attendance.checkin');
        Route::post('/attendance/checkin/batch', [AttendanceController::class, 'batchCheckIn'])->name('attendance.checkin.batch');
        Route::post('/attendance/checkout', [AttendanceController::class, 'checkOut'])->name('attendance.checkout');
        Route::get('/attendance/export', [AttendanceController::class, 'exportTodayAttendances'])->name('attendance.export');

        // Non-Member Visits
        Route::resource('non-member-visit', NonMemberVisitController::class)->except(['show', 'edit', 'update', 'destroy']);

        // Notifications
        Route::get('/notifications/scheduled-commands', [NotificationController::class, 'getScheduledCommandNotifications'])->name('notifications.scheduled-commands');
        Route::post('/notifications/mark-read', [NotificationController::class, 'markNotificationsAsRead'])->name('notifications.mark-read');

        // Admin-only pages
        Route::middleware('role:admin')->group(function () {
            // Payment History
            Route::get('/payment', [PaymentController::class, 'index'])->name('admin.payment.index');

            // Staff Schedule Management
            Route::get('/staff-schedule', [StaffScheduleController::class, 'index'])->name('admin.staff-schedule.index');
            Route::post('/staff-schedule', [StaffScheduleController::class, 'store'])->name('admin.staff-schedule.store');
            Route::delete('/staff-schedule/{id}', [StaffScheduleController::class, 'destroy'])->name('admin.staff-schedule.destroy');

            // Business Report
            Route::get('/business-report', [BusinessReportController::class, 'index'])->name('admin.business-report.index');

            // Settings
            Route::get('/settings', [SettingsController::class, 'index'])->name('admin.settings.index');
            Route::post('/settings/membership-packages', [SettingsController::class, 'storePackage'])->name('admin.settings.packages.store');
            Route::put('/settings/membership-packages/{packageKey}', [SettingsController::class, 'updatePackage'])->name('admin.settings.packages.update');
            Route::put('/settings/fees', [SettingsController::class, 'updateFees'])->name('admin.settings.fees.update');
        });
    });
});

// if (app()->environment('local', 'testing')) {
//     Route::get('/test-error/{code}', function ($code) {
//         $validCodes = [400, 403, 404, 419, 429, 500, 503];

//         if (! in_array($code, $validCodes)) {
//             abort(404, 'Error code not found');
//         }

//         abort((int) $code, 'Test error page');
//     })->name('test.error');
// }
