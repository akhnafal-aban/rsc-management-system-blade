<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNonMemberVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:CASH,TRANSFER,EWALLET'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
