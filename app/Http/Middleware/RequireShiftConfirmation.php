<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Services\StaffShiftConfirmationService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RequireShiftConfirmation
{
    public function __construct(
        private readonly StaffShiftConfirmationService $shiftConfirmationService
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Only apply to staff users
        if (!$user || $user->role !== UserRole::STAFF) {
            return $next($request);
        }

        // Skip for shift confirmation routes and AJAX requests
        if ($request->routeIs('staff.shift.confirm') || $request->routeIs('staff.shift.store') || $request->ajax()) {
            return $next($request);
        }

        // Check if staff has confirmed shift today
        if (!$this->shiftConfirmationService->hasConfirmedShiftToday($user->id)) {
            // Store intended URL
            if (!$request->routeIs('staff.shift.page')) {
                session()->put('url.intended', $request->fullUrl());
            }

            // Redirect to shift confirmation page
            return redirect()->route('staff.shift.page');
        }

        return $next($request);
    }
}
