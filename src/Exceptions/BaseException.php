<?php

declare(strict_types=1);

namespace Azk\S3FileMover\Exceptions;

use RuntimeException;
use Throwable;

abstract class BaseException extends RuntimeException
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        $message = $this->message() ?: $message;
        $code = $this->code() ?: $code;
        parent::__construct($message, $code, $previous);
    }

    public function message(): ?string
    {
        return '';
    }

    public function code(): ?int
    {
        return 0;
    }
}
