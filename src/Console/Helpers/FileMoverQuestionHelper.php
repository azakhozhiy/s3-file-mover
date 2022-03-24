<?php

namespace Azk\S3FileMover\Console\Helpers;

class FileMoverQuestionHelper extends QuestionHelper
{
    public function choiceBucket(array $buckets, bool $isFrom = true)
    {
        $question = $isFrom
            ? "Please select from which bucket to transfer the files"
            : "Please choose in which bucket to upload the files";

        $buckets = array_map(static fn(array $bucket) => $bucket['Name'], $buckets);

        return $this->choiceQuestion($question, $buckets, 'Bucket %s is invalid.');
    }

    public function storageQuestion(array $storages, bool $isFrom = true)
    {
        $question = $isFrom
            ? 'Please select type of from storage:'
            : 'Please select type of to storage:';

        return $this->choiceQuestion($question, $storages);
    }
}