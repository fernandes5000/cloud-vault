<?php

namespace App\Http\Requests\Drive;

use Illuminate\Foundation\Http\FormRequest;

class UploadChunkRequest extends FormRequest
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
            'chunk_index' => ['required', 'integer', 'min:0', 'max:9999'],
            'chunk' => ['required', 'file', 'max:25600'],
        ];
    }
}
