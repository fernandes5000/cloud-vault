<?php

namespace App\Http\Requests\Drive;

use Illuminate\Foundation\Http\FormRequest;

class InitiateUploadRequest extends FormRequest
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
            'folder_id' => ['nullable', 'string', 'exists:drive_items,id'],
            'total_chunks' => ['required', 'integer', 'min:1', 'max:10000'],
            'total_size_bytes' => ['nullable', 'integer', 'min:1', 'max:5368709120'],
            'mime_type' => ['nullable', 'string', 'max:190'],
            'checksum_sha256' => ['nullable', 'string', 'size:64'],
            'metadata' => ['nullable', 'array'],
            'metadata.client_path' => ['nullable', 'string', 'max:500'],
        ];
    }
}
