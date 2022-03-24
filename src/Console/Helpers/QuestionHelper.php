<?php

namespace Azk\S3FileMover\Console\Helpers;

use Closure;
use Symfony\Component\Console\Helper\QuestionHelper as SymphonyQuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class QuestionHelper
{
    private Closure $askClosure;

    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        SymphonyQuestionHelper $helper
    ) {
        $this->askClosure = static fn(Question $q) => $helper->ask($input, $output, $q);
    }

    public function stringQuestion(string $question): mixed
    {
        return ($this->askClosure)(new Question($question));
    }

    public function choiceQuestion(
        string $question,
        array $options = [],
        string $errorMessage = 'Answer %s is invalid.',
        int $defaultOptionKey = 0
    ): mixed {
        $questionObj = (new ChoiceQuestion(
            $question,
            $options,
            $defaultOptionKey
        ))->setErrorMessage($errorMessage);

        return $this->ask($questionObj);
    }

    protected function ask(Question $q): mixed
    {
        return ($this->askClosure)($q);
    }
}