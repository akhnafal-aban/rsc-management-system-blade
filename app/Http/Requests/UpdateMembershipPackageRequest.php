<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMembershipPackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'label' => 'required|string|max:150',
            'price' => 'required|integer|min:0',
            'discount_percent' => 'nullable|integer|min:0|max:100',
            'duration_days' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'label.required' => 'Label paket harus diisi.',
            'price.required' => 'Harga paket harus diisi.',
            'price.integer' => 'Harga paket harus berupa angka.',
            'duration_days.required' => 'Durasi paket (hari) harus diisi.',
            'duration_days.integer' => 'Durasi paket harus berupa angka.',
            'duration_days.min' => 'Durasi paket minimal 1 hari.',
            'discount_percent.min' => 'Diskon minimal 0%.',
            'discount_percent.max' => 'Diskon maksimal 100%.',
        ];
    }
}

