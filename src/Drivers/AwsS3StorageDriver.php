<?php

namespace Azk\S3FileMover\Drivers;

use Azk\S3FileMover\Components\Abstract\AbstractStorage;
use Azk\S3FileMover\Components\Factories\StorageConfigItemFactory;

class AwsS3StorageDriver extends AbstractStorage
{
    public const CREDENTIALS_CONFIG_KEY = 'credentials';

    protected string $endpoint = '';
    protected string $region = 'us-east-2';

    public static function getName(): string
    {
        return 'aws-s3';
    }

    public function initConfigItems(): self
    {
        return $this->addConfigItem(StorageConfigItemFactory::create('version', 'latest'))
            ->addConfigItem(StorageConfigItemFactory::create('region', $this->region))
            ->addConfigItem(StorageConfigItemFactory::create('endpoint', $this->endpoint))
            ->addConfigItem(StorageConfigItemFactory::create(self::CREDENTIALS_CONFIG_KEY, [
                StorageConfigItemFactory::create('key', null),
                StorageConfigItemFactory::create('secret', null)
            ]));
    }
}