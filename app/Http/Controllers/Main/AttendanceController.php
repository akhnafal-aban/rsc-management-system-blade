<?php

declare(strict_types=1);

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;
use App\Services\AttendanceService;
use App\Services\MemberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    public function __construct(
        private readonly AttendanceService $attendanceService,
        private readonly MemberService $memberService
    ) {}

    public function index(Request $request): View
    {
        $search = $request->get('search');
        $statusFilter = $request->get('status');
        $dateFilter = $request->get('date', now()->format('Y-m-d'));

        $attendances = $this->attendanceService->getAttendancesByDate($dateFilter, 10, $search, $statusFilter);
        $searchResults = collect();

        return view('pages.main.attendance.attendance', compact('attendances', 'searchResults', 'search', 'statusFilter', 'dateFilter'));
    }

    public function checkInPage(): View
    {
        return view('pages.main.attendance.check-in');
    }

    public function checkIn(Request $request): RedirectResponse
    {
        $request->validate([
            'member_id' => ['required', 'string'],
        ]);

        $member = $this->attendanceService->getMemberById($request->member_id);

        if (! $member) {
            return redirect()->back()->with('error', 'Member tidak ditemukan');
        }

        // Simple validation - just check for duplicate check-in today
        $duplicateCheck = $this->attendanceService->canCheckIn($member);

        if (! $duplicateCheck['can_checkin']) {
            return redirect()->back()->with('error', $duplicateCheck['message']);
        }

        try {
            $this->attendanceService->checkInMember($member, Auth::id());

            return redirect()
                ->route('attendance.check-in')
                ->with('success', 'Check-in berhasil!');
        } catch (\Exception $e) {
            return redirect()
                ->route('attendance.check-in')
                ->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    public function checkOut(Request $request): RedirectResponse
    {
        $request->validate([
            'attendance_id' => ['required', 'exists:attendances,id'],
        ]);

        $attendance = $this->attendanceService->getAttendanceById($request->attendance_id);

        if (! $attendance) {
            return redirect()->back()->with('error', 'Data absensi tidak ditemukan');
        }

        if ($attendance->check_out_time) {
            return redirect()->back()->with('error', 'Member sudah check-out');
        }

        try {
            $this->attendanceService->checkOutMember($attendance, Auth::id());

            return redirect()->back()->with('success', 'Check-out berhasil!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    public function exportTodayAttendances(Request $request): StreamedResponse
    {
        $dateFilter = $request->get('date', now()->format('Y-m-d'));
        $data = $this->attendanceService->exportAttendancesByDate($dateFilter);
        $filename = 'absensi_'.$dateFilter.'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
