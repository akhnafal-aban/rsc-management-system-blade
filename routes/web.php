<?php

use App\Http\Controllers\Admin\StaffManagementController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Main\AttendanceController;
use App\Http\Controllers\Main\DashboardController;
use App\Http\Controllers\Main\MemberController;
use App\Http\Controllers\Main\ReportController;
use App\Http\Controllers\NotificationController;
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

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Members
    Route::get('/member/search', [MemberController::class, 'searchMembers'])->name('member.search');
    Route::get('/member/extend', [MemberController::class, 'extend'])->name('member.extend');
    Route::post('/member/extend', [MemberController::class, 'storeExtend'])->name('member.extend.store');
    Route::post('/member/bulk-update-status', [MemberController::class, 'bulkUpdateStatuses'])->name('member.bulk-update-status');
    Route::get('/member/registration-costs', [MemberController::class, 'getRegistrationCosts'])->name('member.registration-costs');
    Route::resource('member', MemberController::class);
    Route::post('/member/{member}/suspend', [MemberController::class, 'suspend'])->name('member.suspend');
    Route::post('/member/{member}/activate', [MemberController::class, 'activate'])->name('member.activate');

    // Attendance
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/check-in', [AttendanceController::class, 'checkInPage'])->name('attendance.check-in');
    Route::post('/attendance/checkin', [AttendanceController::class, 'checkIn'])->name('attendance.checkin');
    Route::post('/attendance/checkin/batch', [AttendanceController::class, 'batchCheckIn'])->name('attendance.checkin.batch');
    Route::post('/attendance/checkout', [AttendanceController::class, 'checkOut'])->name('attendance.checkout');
    Route::get('/attendance/export', [AttendanceController::class, 'exportTodayAttendances'])->name('attendance.export');

    // Notifications
    Route::get('/notifications/scheduled-commands', [NotificationController::class, 'getScheduledCommandNotifications'])->name('notifications.scheduled-commands');
    Route::post('/notifications/mark-read', [NotificationController::class, 'markNotificationsAsRead'])->name('notifications.mark-read');

    // Admin-only pages
    Route::middleware('role:admin')->group(function () {
        Route::get('/staff-management', [StaffManagementController::class, 'index'])->name('admin.staff.index');
    });
});

if (app()->environment('local', 'testing')) {
    Route::get('/test-error/{code}', function ($code) {
        $validCodes = [400, 403, 404, 419, 429, 500, 503];

        if (! in_array($code, $validCodes)) {
            abort(404, 'Error code not found');
        }

        abort((int) $code, 'Test error page');
    })->name('test.error');
}
