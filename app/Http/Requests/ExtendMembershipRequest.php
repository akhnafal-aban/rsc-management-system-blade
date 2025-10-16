<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExtendMembershipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'member_id' => 'required|integer|exists:members,id',
            'membership_duration' => 'required|integer|in:1,3,6,12',
            'payment_method' => 'required|string|in:CASH,TRANSFER,EWALLET',
            'payment_notes' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'member_id.required' => 'Member harus dipilih.',
            'member_id.exists' => 'Member yang dipilih tidak ditemukan.',
            'membership_duration.required' => 'Durasi membership harus dipilih.',
            'membership_duration.in' => 'Durasi membership tidak valid.',
            'payment_method.required' => 'Metode pembayaran harus dipilih.',
            'payment_method.in' => 'Metode pembayaran tidak valid.',
            'payment_notes.max' => 'Catatan pembayaran maksimal 500 karakter.',
        ];
    }
}
