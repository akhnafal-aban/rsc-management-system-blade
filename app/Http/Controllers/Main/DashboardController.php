<?php

declare(strict_types=1);

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;
use App\Services\CacheService;
use App\Services\DashboardService;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboardService) {}

    public function index()
    {
        $cacheKey = 'dashboard_data_'.now()->format('Y-m-d-H');

        $dashboardData = Cache::remember($cacheKey, CacheService::CACHE_TTL_MEDIUM, function () {
            return $this->dashboardService->getDashboardData();
        });

        return view('pages.main.dashboard', $dashboardData);
    }
}
