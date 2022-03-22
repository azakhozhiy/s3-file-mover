<?php

namespace Azk\S3FileMover\Contracts;

interface StorageManagerInterface
{
    public function register(string $driverName, callable|StorageInterface $driver): self;

    public function driver(string $driverName): StorageInterface;
}