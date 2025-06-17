<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Services\ReportSuiteBuilder;
use Illuminate\Foundation\Http\FormRequest;

final class ReportShowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['sometimes', 'string', 'max:255'],
            'filter_state' => ['sometimes', 'array'],
            'filter_state.*' => ['in:failed,passed,skipped,pending'],
        ];
    }

    /**
     * Get validated data with defaults
     *
     * @param  string|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        // Set defaults
        if (is_array($validated)) {
            $validated['filter_state'] ??= ReportSuiteBuilder::FILTER_STATES;
        }

        return $key ? ($validated[$key] ?? $default) : $validated;
    }
}
