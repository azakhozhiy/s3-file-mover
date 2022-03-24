<?php

namespace Azk\S3FileMover\Components;

use Azk\S3FileMover\Contracts\StorageInterface;
use Azk\S3FileMover\Contracts\StorageManagerInterface;
use Azk\S3FileMover\Exceptions\UnknownStorageException;

class StorageManager implements StorageManagerInterface
{
    /** @var StorageInterface[]|callable[] */
    protected array $items = [];

    public function register(string $driverName, callable|StorageInterface $driver): self
    {
        $this->items[$driverName] = $driver;

        return $this;
    }

    public function driver(string $driverName): StorageInterface
    {
        if (!isset($this->items[$driverName])) {
            throw new UnknownStorageException();
        }

        $driver = $this->items[$driverName];
        $driver = is_callable($driver) ? $driver() : $this->items[$driverName];
        $this->items[$driverName] = $driver;

        return $driver;
    }

    public function getAll(): array
    {
        return array_map(static function (StorageInterface|callable $s) {
            return is_callable($s) ? $s() : $s;
        }, $this->items);
    }
}