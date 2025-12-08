<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStaffScheduleRequest extends FormRequest
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
            'user_id' => ['required', 'exists:users,id'],
            'schedule_date' => ['required', 'date'],
            'shift_type' => ['required', 'in:MORNING,EVENING'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
