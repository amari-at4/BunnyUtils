<?php

namespace App\Command\Credentials;

use App\Helper\CredentialsHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

abstract class Base extends Command
{
    protected static $defaultName = 'credentials:';
    
    protected CredentialsHelper $credentialsHelper;

    protected OutputInterface $output;

    protected QuestionHelper $questionHelper;

    public function __construct(CredentialsHelper $credentialsHelper)
    {
        parent::__construct();
        $this->credentialsHelper = $credentialsHelper;
        $this->questionHelper = $this->getHelper('question');
    }

    protected function askUserPassword(InputInterface $input, OutputInterface $output): ?string
    {
        if( $this->credentialsHelper->isEncryptedKey() ) {
            $credentialsHelper = $this->credentialsHelper;
            $question = new Question('Please enter user password to decrypt key');
            $question
                ->setHidden(true)
                ->setHiddenFallback(false);
            $question->setValidator(function ($answer) use ($credentialsHelper) {
                $credentialsHelper->getKey($answer);
                return $answer;
            });
            return $this->questionHelper->ask($input, $output, $question);
        } else {
            $this->credentialsHelper->getKey();
        }
        return null;
    }
}