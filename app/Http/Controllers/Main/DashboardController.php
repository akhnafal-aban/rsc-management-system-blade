<?php

declare(strict_types=1);

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboardService) {}

    public function index()
    {
        $dashboardData = $this->dashboardService->getDashboardData();

        return view('pages.main.dashboard', $dashboardData);
    }
}
