<?php

namespace App\Http\Requests\EventInvitee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInviteeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'inviteable_type' => ['required', Rule::in(['user', 'group'])],
            'inviteable_id'   => ['required', 'integer'],
        ];
    }
}
