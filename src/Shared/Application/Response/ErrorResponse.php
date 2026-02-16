<?php

declare(strict_types=1);

namespace Shared\Application\Response;

/**
 * Generic Error Response
 *
 * Used for operations that fail
 */
final class ErrorResponse
{
    public function __construct(
        private readonly string $message,
        private readonly string $code = 'ERROR',
        private readonly array $errors = []
    ) {}

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function toArray(): array
    {
        return [
            'success' => false,
            'message' => $this->message,
            'code' => $this->code,
            'errors' => $this->errors,
        ];
    }
}
