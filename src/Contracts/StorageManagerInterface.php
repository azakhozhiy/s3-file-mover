<?php

declare(strict_types=1);

namespace Azk\S3FileMover\Contracts;

interface StorageManagerInterface
{
    public function register(string $driverName, callable|StorageInterface $driver): self;

    public function driver(string $driverName): StorageInterface;

    public function getAll(): array;
}
