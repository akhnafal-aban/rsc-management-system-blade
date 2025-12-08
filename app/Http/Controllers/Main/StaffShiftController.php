<?php

declare(strict_types=1);

namespace App\Http\Controllers\Main;

use App\Enums\ShiftType;
use App\Http\Controllers\Controller;
use App\Http\Requests\ConfirmShiftRequest;
use App\Services\StaffScheduleService;
use App\Services\StaffShiftConfirmationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StaffShiftController extends Controller
{
    public function __construct(
        private readonly StaffShiftConfirmationService $shiftConfirmationService,
        private readonly StaffScheduleService $staffScheduleService
    ) {}

    public function showConfirmationPage(): View
    {
        $user = Auth::user();
        $todaySchedule = $this->shiftConfirmationService->getStaffScheduleForToday($user->id);
        $shiftTypes = ShiftType::cases();

        return view('pages.main.staff-shift.confirm', compact('todaySchedule', 'shiftTypes'));
    }

    public function confirm(ConfirmShiftRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = Auth::user();

        try {
            $shiftType = ShiftType::from($validated['shift_type']);
            $todaySchedule = $this->shiftConfirmationService->getStaffScheduleForToday($user->id);

            $this->shiftConfirmationService->confirmShift(
                $user->id,
                $shiftType,
                $todaySchedule?->id,
                $validated['notes'] ?? null
            );

            $intendedUrl = session()->pull('url.intended', route('dashboard'));

            return redirect($intendedUrl)
                ->with('success', 'Shift berhasil dikonfirmasi!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function mySchedule(): View
    {
        $user = Auth::user();
        $month = request()->get('month', now()->format('Y-m'));
        $schedules = $this->staffScheduleService->getScheduleForMonth($month, $user->id);

        return view('pages.main.staff-shift.schedule', compact('schedules', 'month'));
    }
}
