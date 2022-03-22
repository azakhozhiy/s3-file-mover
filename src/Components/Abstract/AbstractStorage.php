<?php

namespace Azk\S3FileMover\Components\Abstract;

use Azk\S3FileMover\Contracts\Entity\StorageConfigItemInterface;
use Azk\S3FileMover\Contracts\StorageInterface;
use Azk\S3FileMover\Exceptions\S3\ConfigItemIsNotArrayException;
use Azk\S3FileMover\Exceptions\S3\ConfigItemIsUndefinedException;

abstract class AbstractStorage implements StorageInterface
{
    private array $configItems = [];

    public function __construct()
    {
        $this->initConfigItems();
    }

    abstract public static function getName(): string;

    public function addConfigItem(StorageConfigItemInterface $configItem): self
    {
        $this->configItems[] = $configItem;

        return $this;
    }

    public function resetConfigItem(int $key, StorageConfigItemInterface $configItem): self
    {
        $this->configItems[$key] = $configItem;

        return $this;
    }

    public function appendConfigItemToConfigItem(string $name, StorageConfigItemInterface $configItem): self
    {
        $configItems = $this->getConfigItems();

        $configItemValue = null;
        $configItemKey = null;

        /**
         * @var string $key
         * @var StorageConfigItemInterface $value
         */
        foreach ($configItems as $key => $value) {
            if ($value->getKey() === $name) {
                $configItemValue = $value;
                $configItemKey = $key;
                break;
            }
        }

        if (!$configItemValue) {
            throw new ConfigItemIsUndefinedException();
        }

        if (!is_array($configItemValue->getValue())) {
            throw new ConfigItemIsNotArrayException($configItemValue->getKey());
        }

        $configItemValue->setValue([...$configItemValue->getValue(), $configItem]);
        $this->configItems[$configItemKey] = $configItemValue;

        return $this;
    }

    public function getConfigItems(): array
    {
        return $this->configItems;
    }
}