<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $memberId = $this->route('member')->id;

        return [
            'name' => 'required|string|max:100',
            'email' => 'nullable|email|unique:members,email,'.$memberId,
            'phone' => 'nullable|string|max:20',
            'status' => 'sometimes|in:ACTIVE,INACTIVE',
            'membership_duration' => 'required|integer|in:1,3,6,12',
            'payment_method' => 'required|in:CASH,TRANSFER,EWALLET',
            'payment_notes' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama harus diisi.',
            'name.max' => 'Nama maksimal 100 karakter.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'phone.max' => 'Nomor telepon maksimal 20 karakter.',
            'status.in' => 'Status harus ACTIVE atau INACTIVE.',
            'membership_duration.required' => 'Durasi membership harus diisi.',
            'membership_duration.integer' => 'Durasi membership harus berupa angka.',
            'membership_duration.in' => 'Durasi membership harus 1, 3, 6, atau 12 bulan.',
            'payment_method.required' => 'Metode pembayaran harus diisi.',
            'payment_method.in' => 'Metode pembayaran harus CASH, TRANSFER, atau EWALLET.',
            'payment_notes.max' => 'Catatan pembayaran maksimal 500 karakter.',
        ];
    }
}
