<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ReportsIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
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
        $validated['per_page'] ??= 20;
        $validated['page'] ??= 1;

        return $key ? ($validated[$key] ?? $default) : $validated;
    }
}
