<?php

namespace App\Http\Requests\Share;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CreateShareLinkRequest extends FormRequest
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
            'drive_item_id' => ['required', 'string', 'exists:drive_items,id'],
            'visibility' => ['required', 'string', 'in:public,private'],
            'permission' => ['required', 'string', 'in:view,download'],
            'recipient_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'recipient_email' => ['nullable', 'string', 'email', 'max:190'],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'password' => ['nullable', 'string', 'min:8', 'max:120'],
            'max_downloads' => ['nullable', 'integer', 'min:1', 'max:100000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->input('visibility') === 'private'
                && ! $this->filled('recipient_user_id')
                && ! $this->filled('recipient_email')) {
                $validator->errors()->add('recipient_email', __('share.private_recipient_required'));
            }
        });
    }
}
