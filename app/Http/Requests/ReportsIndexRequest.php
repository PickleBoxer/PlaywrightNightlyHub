<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReportsIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'platform' => ['sometimes', 'string', 'max:50'],
            'campaign' => ['sometimes', 'string', 'max:100'],
            'version' => ['sometimes', 'string', 'max:100'],
            'search' => ['sometimes', 'string', 'max:255'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        // Set defaults
        $validated['per_page'] = $validated['per_page'] ?? 20;
        $validated['page'] = $validated['page'] ?? 1;

        return $key ? ($validated[$key] ?? $default) : $validated;
    }
}
