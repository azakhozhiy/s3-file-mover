<?php

namespace Azk\S3FileMover\Drivers;

use Azk\S3FileMover\Components\Factories\StorageConfigItemFactory;

class SelectelAwsS3StorageDriver extends AwsS3StorageDriver
{
    protected string $region = 'ru-1';
    protected string $endpoint = 'https://s3.storage.selcloud.ru';

    public static function getName(): string
    {
        return 'selectel-s3';
    }

    public function initConfigItems(): AwsS3StorageDriver
    {
        return parent::initConfigItems()
            ->addConfigItem(
                StorageConfigItemFactory::create('use_path_style_endpoint', true)
            );
    }
}