<?php

declare(strict_types=1);

namespace Shared\Application\Response;

/**
 * Generic Success Response
 *
 * Used for operations that complete successfully
 */
final class SuccessResponse
{
    public function __construct(
        private readonly mixed $data = null,
        private readonly string $message = 'Operation completed successfully'
    ) {
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function toArray(): array
    {
        return [
            'success' => true,
            'message' => $this->message,
            'data' => $this->data,
        ];
    }
}
