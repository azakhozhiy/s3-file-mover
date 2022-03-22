<?php

namespace Azk\S3FileMover\Components\Abstract;

use Azk\S3FileMover\Contracts\Entity\StorageConfigItemInterface;

abstract class AbstractStorageConfigItem implements StorageConfigItemInterface
{
    private mixed $value;
    private string $key;

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): self
    {
        $this->key = $key;

        return $this;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function setValue(mixed $value): self
    {
        $this->value = $value;

        return $this;
    }
}