<?php

declare(strict_types=1);

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExtendMembershipRequest;
use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Models\Member;
use App\Services\MemberService;

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
        return view('pages.main.member.create');
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

        // Get original exp_date for comparison
        $originalExpDate = $member->exp_date?->format('Y-m-d');
        $newExpDate = $validatedData['exp_date'] ?? null;

        // Update member
        $updatedMember = $this->memberService->updateMember($member->id, $validatedData);

        // Prepare success message based on what was changed
        $message = 'Data member berhasil diperbarui.';

        if ($originalExpDate !== $newExpDate) {
            $expirationStatus = $this->memberService->getMemberExpirationStatus($updatedMember);

            if ($expirationStatus['is_expired']) {
                $message .= ' Perhatian: Member telah expired berdasarkan tanggal baru.';
            } elseif ($expirationStatus['is_expiring_soon']) {
                $message .= ' Peringatan: Member akan expired dalam '.$expirationStatus['days_until_expiry'].' hari.';
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
        return view('pages.main.member.extend');
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

        $member = $this->memberService->extendMembership(
            (int) $validated['member_id'],
            (int) $validated['membership_duration'],
            $validated['payment_method'],
            $validated['payment_notes'] ?? null
        );

        return redirect()->route('member.show', $member)
            ->with('success', 'Membership berhasil diperpanjang.');
    }

    public function getExpirationStatus(Member $member)
    {
        $expirationStatus = $this->memberService->getMemberExpirationStatus($member);

        return response()->json($expirationStatus);
    }
}
