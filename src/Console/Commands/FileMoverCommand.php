<?php

declare(strict_types=1);

namespace Azk\S3FileMover\Console\Commands;

use Aws\S3\S3ClientInterface;
use Azk\S3FileMover\Components\Factories\S3ClientFactory;
use Azk\S3FileMover\Components\FileMover;
use Azk\S3FileMover\Components\StorageManager;
use Azk\S3FileMover\Console\Helpers\FileMoverQuestionHelper;
use Azk\S3FileMover\Console\Helpers\MessageHelper;
use Azk\S3FileMover\Console\Services\StorageConfigFiller;
use Azk\S3FileMover\Contracts\StorageInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

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
        $toStorage = $this->initStorage($input->getOption('to-storage'), false);

        $storageConfigFiller = new StorageConfigFiller($this->questionHelper);

        $output->writeln("Fill (FROM) config for {$fromStorage::getName()}.");
        $fromStorageDriver = $storageConfigFiller->fill($fromStorage);
        // Create FROM s3 client instance
        $fromS3Client = S3ClientFactory::createByStorage($fromStorageDriver);

        $output->writeln("Fill (TO) config for  {$toStorage::getName()}.");
        $toStorageDriver = $storageConfigFiller->fill($toStorage);
        // Create TO s3 client instance
        $toS3Client = S3ClientFactory::createByStorage($toStorageDriver);

        $storageBuckets = static fn (S3ClientInterface $s3) => array_map(
            static fn (array $bucket) => $bucket['Name'],
            $s3->listBuckets()['Buckets']
        );

        $fromBucket = $this->questionHelper->choiceBucket($storageBuckets($fromS3Client));
        $toBucket = $this->questionHelper->choiceBucket($storageBuckets($toS3Client), false);

        $s3FileMover = new FileMover($fromS3Client, $fromBucket, $toS3Client, $toBucket);

        $s3FileMover->moveAll(
            fn (array $fileObject, Throwable $e) => $output->writeln($e->getMessage()),
            fn (array $file) => $output->writeln("Start move file {$file['Key']}. \n"),
            fn (array $file) => $output->writeln(
                MessageHelper::fileSuccessfullyMoved($file['Key'], $toStorage::getName(), $toBucket)
            )
        );

        return Command::SUCCESS;
    }

    private function initStorage(?string $name, bool $isFrom = true): StorageInterface
    {
        $storages = array_map(static fn (StorageInterface $s) => $s::getName(), $this->storageManager->getAll());

        if (!$name) {
            $name = $this->questionHelper->storageQuestion($storages, $isFrom);
        }

        return clone $this->storageManager->driver($name);
    }
}
