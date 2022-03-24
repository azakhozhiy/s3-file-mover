<?php

declare(strict_types=1);

namespace Azk\S3FileMover\Storages;

use Azk\S3FileMover\Components\Factories\StorageConfigItemFactory;

class SelectelS3Storage extends AwsS3Storage
{
    protected string $region = 'ru-1';
    protected string $endpoint = 'https://s3.storage.selcloud.ru';

    public static function getName(): string
    {
        return 'selectel-s3';
    }

    public function initConfigItems(): AwsS3Storage
    {
        return parent::initConfigItems()
            ->addConfigItem(
                StorageConfigItemFactory::create('use_path_style_endpoint', true)
            );
    }
}
