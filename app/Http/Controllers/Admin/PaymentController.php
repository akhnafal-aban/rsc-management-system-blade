<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PaymentHistoryService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentHistoryService $paymentHistoryService
    ) {}

    public function index(Request $request): View
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $payments = $this->paymentHistoryService->getAllPayments($startDate, $endDate, 15);
        $dailyRevenue = $this->paymentHistoryService->getDailyRevenue($request->get('date'));
        $monthlyRevenue = $this->paymentHistoryService->getMonthlyRevenue($request->get('month'));

        return view('pages.admin.payment.index', compact('payments', 'dailyRevenue', 'monthlyRevenue', 'startDate', 'endDate'));
    }
}
