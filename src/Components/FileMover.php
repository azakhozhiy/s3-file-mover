<?php

declare(strict_types=1);

namespace Azk\S3FileMover\Components;

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
        ?callable $successMoveOne = null
    ): array {
        $filesFrom = $this->fromS3Client->getIterator('ListObjects', [
            'Bucket' => $this->fromBucket
        ]);

        // Move files from one s3 to another s3
        foreach ($filesFrom as $fileObject) {
            try {
                $beforeMoveOne($fileObject);
                $this->moveOne($fileObject, $successMoveOne);
            } catch (Throwable $e) {
                if ($errorHandler) {
                    $errorHandler($fileObject, $e);
                }
            }
        }

        return [];
    }

    private function moveOne(mixed $fileObject, ?callable $successMove = null): void
    {
        $filePath = $fileObject['Key'];

        $file = $this->fromS3Client->getObject([
            'Bucket' => $this->fromBucket,
            'Key' => $filePath,
        ]);

        $contentTypeOnToServer = null;
        $bodyOnToServer = null;

        $contentType = $file['ContentType'];
        $bodyFile = $file['Body'];

        if ($this->toS3Client->doesObjectExist($this->toBucket, $filePath)) {
            $fileOnToServer = $this->toS3Client->getObject([
                'Bucket' => $this->toBucket,
                'Key' => $filePath
            ]);

            $contentTypeOnToServer = $fileOnToServer['ContentType'];
            $bodyOnToServer = $fileOnToServer['Body'];
        }

        $contentTypeIsEquals = $contentTypeOnToServer === $contentType;
        $bodyIsEquals = $bodyOnToServer === $bodyFile;

        if ($contentTypeIsEquals && $bodyIsEquals) {
            return;
        }

        $this->toS3Client->putObject([
            'Bucket' => $this->toBucket,
            'Key' => $filePath,
            'Body' => $bodyFile,
            'ContentType' => $contentType,
        ]);

        $successMove($fileObject);
    }
}
