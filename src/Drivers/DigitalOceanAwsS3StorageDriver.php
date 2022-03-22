<?php

namespace Azk\S3FileMover\Drivers;

class DigitalOceanAwsS3StorageDriver extends AwsS3StorageDriver
{
    protected string $endpoint = 'https://ams3.digitaloceanspaces.com';
    protected string $region = 'us-east-1';

    public static function getName(): string
    {
        return 'do-s3';
    }
}