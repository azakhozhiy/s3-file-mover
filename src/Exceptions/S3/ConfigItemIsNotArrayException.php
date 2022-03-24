<?php

declare(strict_types=1);

namespace Azk\S3FileMover\Exceptions\S3;

use Azk\S3FileMover\Exceptions\BaseException;
use Throwable;

class ConfigItemIsNotArrayException extends BaseException
{
    private string $configItemKey;

    public function __construct(string $configItemKey, string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        $this->configItemKey = $configItemKey;
        parent::__construct($message, $code, $previous);
    }

    public function message(): ?string
    {
        return "Config item with key $this->configItemKey is not array";
    }
}
