<?php

declare(strict_types=1);

namespace Azk\S3FileMover\Components;

use Aws\Result;
use Aws\S3\S3ClientInterface;
use Throwable;

class FileMover
{
    private S3ClientInterface $fromS3Client;
    private S3ClientInterface $toS3Client;
    private string $fromBucket;
    private string $toBucket;

    public function __construct(
        S3ClientInterface $fromS3Client,
        string $fromBucket,
        S3ClientInterface $toS3Client,
        string $toBucket
    ) {
        $this->fromS3Client = $fromS3Client;
        $this->toS3Client = $toS3Client;
        $this->fromBucket = $fromBucket;
        $this->toBucket = $toBucket;
    }

    public function moveAll(
        ?callable $errorHandler = null,
        ?callable $beforeMoveOne = null,
        ?callable $afterMoveOne = null
    ): array {
        $filesFrom = $this->fromS3Client->getIterator('ListObjects', [
            'Bucket' => $this->fromBucket
        ]);

        // Move files from one s3 to another s3
        foreach ($filesFrom as $fileObject) {
            try {
                $beforeMoveOne($fileObject);
                $this->moveOne($fileObject);
                $afterMoveOne($fileObject);
            } catch (Throwable $e) {
                if ($errorHandler) {
                    $errorHandler($fileObject, $e);
                }
            }
        }

        return [];
    }

    private function moveOne(mixed $fileObject): Result
    {
        $filePath = $fileObject['Key'];

        $bodyFile = $this->fromS3Client->getObject([
            'Bucket' => $this->fromBucket,
            'Key' => $filePath,
        ])['Body'];

        return $this->toS3Client->putObject([
            'Bucket' => $this->toBucket,
            'Key' => $filePath,
            'Body' => $bodyFile,
        ]);
    }
}
