<?php

declare(strict_types=1);

namespace Azk\S3FileMover\Components\Factories;

use Azk\S3FileMover\Components\Entities\StorageConfigItem;
use Azk\S3FileMover\Contracts\Entity\StorageConfigItemInterface;

class StorageConfigItemFactory
{
    public static function create(string $key, mixed $value): StorageConfigItemInterface
    {
        return (new StorageConfigItem())
            ->setKey($key)
            ->setValue($value);
    }
}
