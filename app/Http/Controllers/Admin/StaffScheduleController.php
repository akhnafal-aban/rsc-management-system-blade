<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\ShiftType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStaffScheduleRequest;
use App\Services\StaffScheduleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StaffScheduleController extends Controller
{
    public function __construct(
        private readonly StaffScheduleService $staffScheduleService
    ) {}

    public function index(Request $request): View
    {
        $month = $request->get('month', now()->format('Y-m'));
        $userId = $request->filled('user_id')
        ? (int) $request->get('user_id')
        : null;
        $schedules = $this->staffScheduleService->getScheduleForMonth($month, $userId);
        $staffList = $this->staffScheduleService->getAllStaff();
        $shiftTypes = ShiftType::cases();

        return view('pages.admin.staff-schedule.index', compact('schedules', 'staffList', 'shiftTypes', 'month'));
    }

    public function store(StoreStaffScheduleRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $shiftType = ShiftType::from($validated['shift_type']);
            $this->staffScheduleService->createOrUpdateSchedule(
                (int) $validated['user_id'],
                $validated['schedule_date'],
                $shiftType,
                $validated['notes'] ?? null,
                (int) Auth::id()
            );

            return redirect()
                ->back()
                ->with('success', 'Jadwal staf berhasil disimpan!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy(int $id): RedirectResponse
    {
        try {
            $this->staffScheduleService->deleteSchedule($id);

            return redirect()
                ->back()
                ->with('success', 'Jadwal staf berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
