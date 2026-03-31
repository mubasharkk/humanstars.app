<?php

namespace App\Http\Requests\CalendarEvent;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'            => ['sometimes', 'required', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'type'             => ['sometimes', 'required', Rule::in(['virtual', 'on-site'])],
            'meeting_url'      => ['nullable', 'url'],
            'address'          => ['nullable', 'string', 'max:500'],
            'starts_at'        => ['sometimes', 'required', 'date'],
            'ends_at'          => ['sometimes', 'required', 'date', 'after:starts_at'],
            'timezone'         => ['nullable', 'timezone:all'],
            'rrule'            => ['nullable', 'string'],
            'reminder_minutes' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
