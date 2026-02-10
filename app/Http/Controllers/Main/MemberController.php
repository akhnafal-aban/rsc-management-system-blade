<?php

declare(strict_types=1);

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExtendMembershipRequest;
use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Models\Member;
use App\Services\MemberService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MemberController extends Controller
{
    public function __construct(private MemberService $memberService) {}

    public function index()
    {
        $filters = request()->only(['search', 'status']);
        $members = $this->memberService->getAllMembers($filters);

        return view('pages.main.member.member', compact('members'));
    }

    public function create()
    {
        $packages = $this->memberService->getAvailableMembershipPackages();

        return view('pages.main.member.create', compact('packages'));
    }

    public function store(StoreMemberRequest $request)
    {
        $member = $this->memberService->createMember($request->validated());

        return redirect()->route('member.show', $member)
            ->with('success', 'Member berhasil ditambahkan.');
    }

    public function show(Member $member)
    {
        $member = $this->memberService->getMemberById($member->id);
        $stats = $this->memberService->getMemberStats($member);

        return view('pages.main.member.show', compact('member', 'stats'));
    }

    public function edit(Member $member)
    {
        return view('pages.main.member.edit', compact('member'));
    }

    public function update(UpdateMemberRequest $request, Member $member)
    {
        $validatedData = $request->validated();

        // Get original data for comparison
        $originalExpDate = $member->exp_date?->format('Y-m-d');
        $originalStatus = $member->status;
        $newExpDate = $validatedData['exp_date'] ?? null;

        // Update member
        $updatedMember = $this->memberService->updateMember($member->id, $validatedData);

        // Prepare success message based on what was changed
        $message = 'Data member berhasil diperbarui.';

        if ($originalExpDate !== $newExpDate) {
            $expirationStatus = $this->memberService->getMemberExpirationStatus($updatedMember);

            if ($expirationStatus['is_expired']) {
                $message .= ' Status member otomatis diubah menjadi expired karena telah expired.';
            } elseif ($expirationStatus['is_expiring_soon']) {
                $message .= ' Peringatan: Member akan expired dalam '.$expirationStatus['days_until_expiry'].' hari.';
            } else {
                $message .= ' Status member otomatis diubah menjadi aktif karena belum expired.';
            }
        }

        return redirect()->route('member.show', $updatedMember)
            ->with('success', $message);
    }

    public function destroy(Member $member)
    {
        $this->memberService->deleteMember($member->id);

        return redirect()->route('member.index')
            ->with('success', 'Member berhasil dihapus.');
    }

    public function suspend(Member $member)
    {
        $this->memberService->suspendMember($member->id);

        return redirect()->route('member.show', $member)
            ->with('success', 'Member berhasil disuspend.');
    }

    public function activate(Member $member)
    {
        $this->memberService->activateMember($member->id);

        return redirect()->route('member.show', $member)
            ->with('success', 'Member berhasil diaktifkan.');
    }

    public function extend()
    {
        $packages = $this->memberService->getAvailableMembershipPackages();

        return view('pages.main.member.extend', compact('packages'));
    }

    public function searchMembers()
    {
        $query = request('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $members = $this->memberService->searchMembers($query);

        return response()->json($members);
    }

    public function storeExtend(ExtendMembershipRequest $request)
    {
        $validated = $request->validated();

        // Get original status for comparison
        $member = Member::findOrFail($validated['member_id']);
        $originalStatus = $member->status;

        $updatedMember = $this->memberService->extendMembership(
            (int) $validated['member_id'],
            (string) $validated['package_key'],
            $validated['payment_method'],
            $validated['payment_notes'] ?? null
        );

        // Prepare success message
        $message = 'Membership berhasil diperpanjang.';

        if ($originalStatus === \App\Enums\MemberStatus::INACTIVE || $originalStatus === \App\Enums\MemberStatus::EXPIRED) {
            $message .= ' Status member otomatis diubah menjadi aktif karena membership telah diperpanjang.';
        }

        return redirect()->route('member.show', $updatedMember)
            ->with('success', $message);
    }

    public function getExpirationStatus(Member $member)
    {
        $expirationStatus = $this->memberService->getMemberExpirationStatus($member);

        return response()->json($expirationStatus);
    }

    public function bulkUpdateStatuses()
    {
        $result = $this->memberService->bulkUpdateMemberStatuses();

        return response()->json([
            'success' => true,
            'message' => "Berhasil mengupdate status {$result['total_updated']} member",
            'data' => $result,
        ]);
    }

    public function getRegistrationCosts()
    {
        $registrationFee = $this->memberService->getRegistrationFee();
        $packages = $this->memberService->getAvailableMembershipPackages();

        $membershipPrices = [];
        foreach ($packages as $key => $package) {
            $membershipPrices[$key] = $this->memberService->getTotalRegistrationCost($key);
        }

        return response()->json([
            'registration_fee' => $registrationFee,
            'membership_prices' => $membershipPrices,
            'packages' => $packages,
        ]);
    }
    
    public function export(Request $request): StreamedResponse
    {
        $filters = $request->only(['search', 'status']);
        $filename = 'members_' . now()->format('Ymd_His') . '.csv';

        $callback = $this->memberService->exportMembersCallback($filters);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->streamDownload($callback, $filename, $headers);
    }
}
