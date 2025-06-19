<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class WebUploadReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed[]>
     */
    public function rules(): array
    {
        return [
            'report' => [
                'required',
                'file',
                'mimes:json',
                'max:10240', // 10MB max size
            ],
            'platform' => ['required', 'string', 'max:50'],
            'database' => ['required', 'string', 'max:50'],
            'campaign' => ['required', 'string', 'max:100'],
            'version' => ['nullable', 'string', 'max:100'],
            'force' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'report.required' => 'Please select a report file to upload.',
            'report.mimes' => 'The report must be a JSON file.',
            'report.max' => 'The report file size must not exceed 10MB.',
            'platform.required' => 'Please select a platform.',
            'database.required' => 'Please select a database.',
            'campaign.required' => 'Please enter a campaign name.',
        ];
    }
}
