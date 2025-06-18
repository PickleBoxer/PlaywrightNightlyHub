<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class ImportPlaywrightRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'filename' => ['required', 'string'],
            'platform' => ['nullable', 'string'],
            'database' => ['nullable', 'string'],
            'campaign' => ['nullable', 'string'],
            'force' => ['nullable'],
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Check if request has body content
            if ($this->getContent()) {
                $validator->errors()->add('body', 'This endpoint does not accept body content.');
            }

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
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
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

    /**
     * Get validated data with proper types.
     *
     * @return array<string, mixed>
     */
    public function validatedData(): array
    {
        $validated = $this->validated();

        return [
            'platform' => $validated['platform'] ?? null,
            'database' => $validated['database'] ?? null,
            'campaign' => $validated['campaign'] ?? null,
            'force' => $this->has('force'),
        ];
    }
}
