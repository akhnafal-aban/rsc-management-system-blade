<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Services\MemberDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly MemberDashboardService $dashboardService
    ) {}

    public function index(): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return $this->error('Unauthenticated', 401);
        }

        $data = $this->dashboardService->getDashboardData($user);
        if ($data === null) {
            return $this->error('Data member tidak ditemukan.', 404);
        }

        return $this->success($data);
    }
}
