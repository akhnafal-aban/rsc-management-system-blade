<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMembershipFeesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'new_member_fee' => 'required|integer|min:0',
            'non_member_visit_daily' => 'required|integer|min:0',
            'non_member_swim' => 'required|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'new_member_fee.required' => 'Biaya pendaftaran member baru harus diisi.',
            'non_member_visit_daily.required' => 'Biaya kunjungan harian non-member harus diisi.',
            'non_member_swim.required' => 'Biaya berenang non-member harus diisi.',
        ];
    }
}

