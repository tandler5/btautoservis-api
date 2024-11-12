<?php

namespace App\Exceptions;

use Exception;
use GraphQL\Error\ClientAware;
use GraphQL\Error\ProvidesExtensions;

final class CustomException extends Exception implements ClientAware, ProvidesExtensions
{
    protected string $reason;
    protected array $additionalData;

    public function __construct(string $message, array $additionalData = [])
    {
        parent::__construct($message);

        $this->additionalData = $additionalData;
    }

    /**
     * Returns true when exception message is safe to be displayed to a client.
     */
    public function isClientSafe(): bool
    {
        return true;
    }

    /**
     * Data to include within the "extensions" key of the formatted error.
     *
     * @return array<string, mixed>
     */
    public function getExtensions(): array
    {
        return $this->additionalData;
    }
}
