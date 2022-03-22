<?php

namespace Azk\S3FileMover\Components;

use Aws\S3\S3Client;
use Aws\S3\S3ClientInterface;
use Azk\S3FileMover\Contracts\Entity\StorageConfigItemInterface;
use Azk\S3FileMover\Contracts\StorageInterface;

class S3ClientFactory
{
    public static function createByStorage(StorageInterface $storageDriver): S3ClientInterface
    {
        $options = self::fillOptions($storageDriver->getConfigItems());

        return new S3Client($options);
    }

    private static function fillOptions(array $configItems): array
    {
        $options = [];

        /** @var StorageConfigItemInterface $configItem */
        foreach ($configItems as $configItem) {
            $configItemValue = $configItem->getValue();

            if (is_array($configItemValue)) {
                $options[$configItem->getKey()] = self::fillOptions($configItemValue);
            } else {
                $options[$configItem->getKey()] = $configItem->getValue();
            }
        }

        return $options;
    }
}