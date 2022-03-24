<?php

namespace Azk\S3FileMover\Console\Commands;

use Aws\S3\S3ClientInterface;
use Azk\S3FileMover\Components\S3ClientFactory;
use Azk\S3FileMover\Components\StorageManager;
use Azk\S3FileMover\Console\Helpers\FileMoverQuestionHelper;
use Azk\S3FileMover\Console\Helpers\MessageHelper;
use Azk\S3FileMover\Console\Services\StorageConfigFiller;
use Azk\S3FileMover\Contracts\StorageInterface;
use Azk\S3FileMover\Services\S3FileMover;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FileMoverCommand extends Command
{
    private StorageManager $storageManager;
    private ?FileMoverQuestionHelper $questionHelper;

    public function __construct(StorageManager $storageManager, string $name = null)
    {
        parent::__construct($name);
        $this->storageManager = $storageManager;
    }

    public function configure(): void
    {
        $this->setName('move')
            ->setDescription('Moving files for one s3 to another s3 storage.')
            ->addOption(
                'from-storage',
                'fs',
                InputOption::VALUE_OPTIONAL,
                'Storage driver where the files come from'
            )
            ->addOption(
                'to-storage',
                'ts',
                InputOption::VALUE_OPTIONAL,
                'Storage driver where to move the files'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->questionHelper = new FileMoverQuestionHelper($input, $output, $this->getHelper('question'));

        $fromStorage = $this->initStorage($input->getOption('from-storage'));
        $toStorage = $this->initStorage($input->getOption('to-storage'));

        $storageConfigFiller = new StorageConfigFiller($this->questionHelper);

        $fromStorageDriver = $storageConfigFiller->fill($fromStorage);
        $toStorageDriver = $storageConfigFiller->fill($toStorage);

        // Create s3 client instances
        $fromS3Client = S3ClientFactory::createByStorage($fromStorageDriver);
        $toS3Client = S3ClientFactory::createByStorage($toStorageDriver);

        $bucketPrepared = static fn(S3ClientInterface $s3) => array_map(
            static fn(array $bucket) => $bucket['Name'], $s3->listBuckets()
        );

        $fromBucket = $this->questionHelper->choiceBucket($bucketPrepared($fromS3Client));
        $toBucket = $this->questionHelper->choiceBucket($bucketPrepared($toS3Client));

        $s3FileMover = new S3FileMover($fromS3Client, $fromBucket, $toS3Client, $toBucket);

        $s3FileMover->moveAll(
            null,
            null,
            fn(array $file) => $output->writeln(
                MessageHelper::fileSuccessfullyMoved($file['Key'], $toStorage::getName(), $toBucket)
            )
        );

        return Command::SUCCESS;
    }

    private function initStorage(?string $name): StorageInterface
    {
        $storages = array_map(static fn(StorageInterface $s) => $s::getName(), $this->storageManager->getAll());

        if (!$name) {
            $name = $this->questionHelper->storageQuestion($storages);
        }

        return $this->storageManager->driver($name);
    }
}