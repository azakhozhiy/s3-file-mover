<?php

declare(strict_types=1);

namespace Azk\S3FileMover\Console\Services;

use Azk\S3FileMover\Console\Helpers\QuestionHelper;
use Azk\S3FileMover\Contracts\Entity\StorageConfigItemInterface;
use Azk\S3FileMover\Contracts\StorageInterface;

class StorageConfigFiller
{
    private QuestionHelper $questionHelper;

    public function __construct(QuestionHelper $questionHelper)
    {
        $this->questionHelper = $questionHelper;
    }

    public function fill(StorageInterface $storage): StorageInterface
    {
        /**
         * @var int $key
         * @var StorageConfigItemInterface $configItem
         */
        foreach ($storage->getConfigItems() as $key => $configItem) {
            $configItem = $this->fillOne($configItem);
            $storage->resetConfigItem($key, $configItem);
        }

        return $storage;
    }

    private function fillOne(
        StorageConfigItemInterface $configItem,
        ?string $prefixForKeyInQuestion = null
    ): StorageConfigItemInterface {
        $configItemValue = $configItem->getValue();

        if (is_array($configItemValue)) {
            $resetItems = [];

            foreach ($configItemValue as $childItemKey => $childItemValue) {
                $childItem = $this->fillOne($childItemValue, $configItem->getKey());
                $resetItems[$childItemKey] = $childItem;
            }

            $configItem->setValue($resetItems);
        } else {
            $key = $prefixForKeyInQuestion
                ? "$prefixForKeyInQuestion.{$configItem->getKey()}"
                : $configItem->getKey();

            $defaultValue = $configItem->getValue();
            $question = $defaultValue
                ? "Entry value for {$key} (default value: $defaultValue) config key: "
                : "Entry value for {$key} config key: ";

            $questionResult = $this->questionHelper->stringQuestion($question);

            if ($questionResult) {
                $configItem->setValue($questionResult);
            }
        }

        return $configItem;
    }
}
