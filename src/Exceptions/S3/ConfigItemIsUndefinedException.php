<?php

declare(strict_types=1);

namespace Azk\S3FileMover\Exceptions\S3;

use Azk\S3FileMover\Exceptions\BaseException;

class ConfigItemIsUndefinedException extends BaseException
{
    public function message(): ?string
    {
        return 'Credentials is undefined.';
    }
}
