<?php

namespace App\Http\Requests\CalendarEvent;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'            => ['required', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'type'             => ['required', Rule::in(['virtual', 'on-site'])],
            'meeting_url'      => ['nullable', 'url', 'required_if:type,virtual'],
            'address'          => ['nullable', 'string', 'max:500'],
            'starts_at'        => ['required', 'date'],
            'ends_at'          => ['required', 'date', 'after:starts_at'],
            'timezone'         => ['nullable', 'timezone:all'],
            'rrule'            => ['nullable', 'string'],
            'reminder_minutes' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
