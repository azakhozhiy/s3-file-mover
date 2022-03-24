<?php

declare(strict_types=1);

namespace Azk\S3FileMover\Contracts\Entity;

interface StorageConfigItemInterface
{
    public function getKey(): string;

    public function setKey(string $key): self;

    public function setValue(mixed $value): self;

    public function getValue(): mixed;
}
