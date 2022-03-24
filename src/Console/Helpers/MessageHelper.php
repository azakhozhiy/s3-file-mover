<?php

declare(strict_types=1);

namespace Azk\S3FileMover\Console\Helpers;

class MessageHelper
{
    public static function fileSuccessfullyMoved(string $filePath, string $toStorage, string $bucket): string
    {
        return "File $filePath successfully uploaded to {$toStorage}, bucket: {$bucket}. \n";
    }
}
