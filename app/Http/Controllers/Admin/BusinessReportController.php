<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BusinessReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BusinessReportController extends Controller
{
    public function __construct(
        private readonly BusinessReportService $businessReportService
    ) {}

    public function index(Request $request): View
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));

        $report = $this->businessReportService->getBusinessReport($startDate, $endDate);

        return view('pages.admin.business-report.index', compact('report', 'startDate', 'endDate'));
    }
}
