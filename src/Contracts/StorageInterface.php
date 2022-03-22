<?php

declare(strict_types=1);

namespace Azk\S3FileMover\Contracts;

use Azk\S3FileMover\Contracts\Entity\StorageConfigItemInterface;

interface StorageInterface
{
    public static function getName(): string;

    public function initConfigItems(): self;

    public function getConfigItems(): array;

    public function resetConfigItem(int $key, StorageConfigItemInterface $configItem): self;

    public function addConfigItem(StorageConfigItemInterface $configItem): self;
}