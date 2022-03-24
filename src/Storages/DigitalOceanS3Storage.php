<?php

declare(strict_types=1);

namespace Azk\S3FileMover\Storages;

class DigitalOceanS3Storage extends AwsS3Storage
{
    protected string $endpoint = 'https://ams3.digitaloceanspaces.com';
    protected string $region = 'us-east-1';

    public static function getName(): string
    {
        return 'do-s3';
    }
}
