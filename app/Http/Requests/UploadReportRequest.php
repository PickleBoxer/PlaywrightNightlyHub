<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

final class UploadReportRequest extends FormRequest
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
            'token' => ['required', 'string'],
            'report' => [
                'required',
                'file',
                'mimes:json',
                'max:10240', // 10MB max size
            ],
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            // Validate token
            $nightlyToken = config('app.nightly_token');
            if ($this->input('token') !== $nightlyToken) {
                $validator->errors()->add('token', 'Invalid token.');
            }
        });
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     *
     * @throws HttpResponseException
     */
    protected function failedValidation($validator): void
    {
        $errors = $validator->errors();

        // Determine appropriate HTTP status code
        $statusCode = Response::HTTP_BAD_REQUEST;
        if ($errors->has('token')) {
            $statusCode = Response::HTTP_UNAUTHORIZED;
        }

        throw new HttpResponseException(
            response()->json([
                'message' => $errors->first(),
            ], $statusCode)
        );
    }
}
