<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Main\DashboardController;
use App\Http\Controllers\Main\MemberController;
use App\Http\Controllers\Main\AttendanceController;
use App\Http\Controllers\Main\ReportController;
use App\Http\Controllers\Admin\StaffManagementController;

// Guest-only routes
Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return view('welcome');
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
    Route::get('/members', [MemberController::class, 'index'])->name('members.index');
    
    // Attendance
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/check-in', [AttendanceController::class, 'checkInPage'])->name('attendance.check-in');
    Route::post('/attendance/search-member', [AttendanceController::class, 'searchMember'])->name('attendance.search-member');
    Route::post('/attendance/checkin', [AttendanceController::class, 'checkIn'])->name('attendance.checkin');
    Route::post('/attendance/checkout', [AttendanceController::class, 'checkOut'])->name('attendance.checkout');
    Route::get('/attendance/export', [AttendanceController::class, 'exportTodayAttendances'])->name('attendance.export');
    
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // Admin-only pages
    Route::middleware('role:admin')->group(function () {
        Route::get('/staff-management', [StaffManagementController::class, 'index'])->name('admin.staff.index');
    });
});