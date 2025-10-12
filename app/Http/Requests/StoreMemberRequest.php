<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'email' => 'nullable|email|unique:members,email',
            'phone' => 'nullable|string|max:20',
            'exp_date' => 'required|date|after:today',
            'status' => 'sometimes|in:ACTIVE,INACTIVE',
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
            'exp_date.required' => 'Tanggal kedaluwarsa harus diisi.',
            'exp_date.date' => 'Format tanggal kedaluwarsa tidak valid.',
            'exp_date.after' => 'Tanggal kedaluwarsa harus setelah hari ini.',
            'status.in' => 'Status harus ACTIVE atau INACTIVE.',
        ];
    }
}
