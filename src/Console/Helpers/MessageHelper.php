<?php

namespace Azk\S3FileMover\Console\Helpers;

class MessageHelper
{
    public static function fileSuccessfullyMoved(string $filePath, string $toStorage, string $bucket): string
    {
        return "File $filePath successfully uploaded to {$toStorage}, bucket: {$bucket}.";
    }
}