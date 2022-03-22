<?php

use Azk\S3FileMover\Components\StorageManager;
use Azk\S3FileMover\Contracts\StorageInterface;
use Azk\S3FileMover\Drivers\AwsS3StorageDriver;
use Azk\S3FileMover\Drivers\DigitalOceanAwsS3StorageDriver;
use Azk\S3FileMover\Drivers\SelectelAwsS3StorageDriver;

require_once __DIR__.'/vendor/autoload.php';

$app = new Symfony\Component\Console\Application('S3 File Mover', '1.0.0');

$storages = [
    SelectelAwsS3StorageDriver::class,
    DigitalOceanAwsS3StorageDriver::class,
    AwsS3StorageDriver::class
];

$storageManager = (new StorageManager());

/** @var string|StorageInterface $storage */
foreach ($storages as $storage) {
    $storageManager->register($storage::getName(), new $storage());
}

$app->add(new Azk\S3FileMover\Console\FileMoverCommand($storageManager));

$app->run();
