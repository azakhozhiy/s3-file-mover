<?php

namespace Azk\S3FileMover\Console;

use Aws\S3\S3ClientInterface;
use Azk\S3FileMover\Components\S3ClientFactory;
use Azk\S3FileMover\Components\StorageManager;
use Azk\S3FileMover\Contracts\Entity\StorageConfigItemInterface;
use Azk\S3FileMover\Contracts\StorageInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class FileMoverCommand extends Command
{
    private StorageManager $storageManager;
    private ?QuestionHelper $questionHelper = null;
    private ?InputInterface $input;
    private ?OutputInterface $output;

    public function __construct(StorageManager $storageManager, string $name = null)
    {
        parent::__construct($name);
        $this->storageManager = $storageManager;
    }

    public function configure(): void
    {
        $this->setName('move')
            ->setDescription('Move files for file storage to other file storage.')
            ->addOption(
                'from-storage-driver',
                'fsd',
                InputOption::VALUE_REQUIRED,
                'Storage driver where the files come from'
            )
            ->addOption(
                'to-storage-driver',
                'tsd',
                InputOption::VALUE_REQUIRED,
                'Storage driver where to move the files'
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_OPTIONAL,
                'Forces install even if the directory already exists'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->write('Running the file shifter...');
        $this->questionHelper = $this->getHelper('question');
        $this->input = $input;
        $this->output = $output;

        $fromStorageDriver = $this->storageManager->driver($input->getOption('from-storage-driver'));
        $toStorageDriver = $this->storageManager->driver($input->getOption('to-storage-driver'));

        $output->write("\n");

        $fromStorageDriver = $this->fillStorageDriverConfig($fromStorageDriver, $input, $output);
        $toStorageDriver = $this->fillStorageDriverConfig($toStorageDriver, $input, $output);

        $fromS3Client = S3ClientFactory::createByStorage($fromStorageDriver);
        $toS3Client = S3ClientFactory::createByStorage($toStorageDriver);

        $output->write("\n");
        $fromBucket = $this->selectBucket($fromS3Client);

        $output->write("\n");
        $toBucket = $this->selectBucket($toS3Client, false);

        $filesFrom = $fromS3Client->getIterator('ListObjects', [
            'Bucket' => $fromBucket
        ]);

        foreach ($filesFrom as $fileObject) {
            $filePath = $fileObject['Key'];

            $bodyFile = $fromS3Client->getObject([
                'Bucket' => $fromBucket,
                'Key' => $filePath,
            ])['Body'];

            $toS3Client->putObject([
                'Bucket' => $toBucket,
                'Key' => $filePath,
                'Body' => $bodyFile,
            ]);

            $outputMessage = "File $filePath successfully uploaded to {$toStorageDriver::getName()}, bucket: {$toBucket}.";
            $this->output->writeln($outputMessage);
        }

        return Command::SUCCESS;
    }

    private function fillStorageDriverConfig(
        StorageInterface $storageDriver,
        InputInterface $input,
        OutputInterface $output
    ): StorageInterface {
        $output->write("Fill config for {$storageDriver::getName()} storage \n");

        /**
         * @var int $key
         * @var StorageConfigItemInterface $configItem
         */
        foreach ($storageDriver->getConfigItems() as $key => $configItem) {
            $configItem = $this->fillConfigItem($configItem, $input, $output);
            $storageDriver->resetConfigItem($key, $configItem);
        }

        return $storageDriver;
    }

    private function fillConfigItem(
        StorageConfigItemInterface $configItem,
        InputInterface $input,
        OutputInterface $output,
        ?string $prefixForKeyInQuestion = null
    ): StorageConfigItemInterface {
        $configItemValue = $configItem->getValue();

        if (is_array($configItemValue)) {
            $resetItems = [];
            foreach ($configItemValue as $childItemKey => $childItemValue) {
                $childItem = $this->fillConfigItem($childItemValue, $input, $output, $configItem->getKey());
                $resetItems[$childItemKey] = $childItem;
            }
            $configItem->setValue($resetItems);
        } else {
            $key = $prefixForKeyInQuestion
                ? "$prefixForKeyInQuestion.{$configItem->getKey()}"
                : $configItem->getKey();

            $defaultValue = $configItem->getValue();
            $question = $defaultValue
                ? "Entry value for {$key} (default value: $defaultValue) config key: "
                : "Entry value for {$key} config key: ";

            $questionObject = new Question($question);
            $questionResult = $this->questionHelper->ask($input, $output, $questionObject);
            if ($questionResult) {
                $configItem->setValue($questionResult);
            }
        }

        return $configItem;
    }

    private function selectBucket(S3ClientInterface $s3Client, bool $isFrom = true): string
    {
        $question = $isFrom
            ? "Please select from which bucket to transfer the files"
            : "Please choose in which bucket to upload the files";

        $buckets = array_map(static fn(array $bucket) => $bucket['Name'], $s3Client->listBuckets());

        $questionObj = (new ChoiceQuestion(
            $question,
            $buckets,
            0
        ))->setErrorMessage('Bucket %s is invalid.');

        return $this->questionHelper->ask($this->input, $this->output, $questionObj);
    }
}