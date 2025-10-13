<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Member;
use App\Models\Payment;

class PaymentService
{
    public function createPayment(Member $member, int $amount, string $method, ?string $notes = null): Payment
    {
        return Payment::create([
            'member_id' => $member->id,
            'amount' => $amount,
            'method' => $method,
            'notes' => $notes,
        ]);
    }

    public function getMemberPayments(Member $member): \Illuminate\Database\Eloquent\Collection
    {
        return $member->payments()->latest()->get();
    }

    public function getTotalPaid(Member $member): float
    {
        return (float) $member->payments()->sum('amount');
    }

    public function getPaymentById(int $id): Payment
    {
        return Payment::with('member')->findOrFail($id);
    }
}
