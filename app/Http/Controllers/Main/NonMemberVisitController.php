<?php

declare(strict_types=1);

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNonMemberVisitRequest;
use App\Services\NonMemberVisitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NonMemberVisitController extends Controller
{
    public function __construct(
        private readonly NonMemberVisitService $nonMemberVisitService
    ) {}

    public function index(): View
    {
        $visits = $this->nonMemberVisitService->getNonMemberVisits(perPage: 15);

        return view('pages.main.non-member-visit.index', compact('visits'));
    }

    public function create(): View
    {
        $defaultAmount = $this->nonMemberVisitService->getDefaultVisitAmount();

        return view('pages.main.non-member-visit.create', compact('defaultAmount'));
    }

    public function store(StoreNonMemberVisitRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $this->nonMemberVisitService->createNonMemberVisit($validated, Auth::id());

            return redirect()
                ->route('non-member-visit.index')
                ->with('success', 'Kunjungan non-member berhasil dicatat!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
