<?php

declare(strict_types=1);

namespace Azk\S3FileMover\Components;

use Azk\S3FileMover\Contracts\StorageInterface;
use Azk\S3FileMover\Contracts\StorageManagerInterface;
use Azk\S3FileMover\Exceptions\UnknownStorageException;

class StorageManager implements StorageManagerInterface
{
    /** @var StorageInterface[]|callable[] */
    protected array $drivers = [];

    public function register(string $driverName, callable|StorageInterface $driver): self
    {
        $this->drivers[$driverName] = $driver;

        return $this;
    }

    public function driver(string $driverName): StorageInterface
    {
        if (!isset($this->drivers[$driverName])) {
            throw new UnknownStorageException();
        }

        $driver = $this->drivers[$driverName];
        $driver = is_callable($driver) ? $driver() : $this->drivers[$driverName];
        $this->drivers[$driverName] = $driver;

        return $driver;
    }

    public function getAll(): array
    {
        $data = array_map(static fn (StorageInterface|callable $s) => is_callable($s) ? $s() : $s, $this->drivers);

        sort($data);

        return $data;
    }
}
