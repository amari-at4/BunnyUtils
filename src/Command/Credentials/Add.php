<?php

namespace App\Command\Credentials;

use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class Add extends Base
{
    protected static $defaultName = 'credentials:add';

    protected function configure(): void
    {
        $this
            ->setHelp('Add new Bunny account credentials')
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param QuestionHelper  $helper
     * @return array
     * @noinspection PhpUnusedPrivateMethodInspection Dynamically called
     */
    private function getStorageData(InputInterface $input, OutputInterface $output, QuestionHelper $helper): array
    {
        $question = new Question('Please enter the storage name');
        $question->setNormalizer(function ($value) {
            // $value can be null here
            return $value ? trim($value) : '';
        });
        $question->setValidator(function ($answer) {
            if( !is_string($answer) || strlen($answer) < 4 || strlen($answer) > 64 ) {
                throw new RuntimeException(
                    'The storage name must be a string with a minimum length of 4 and maximum of 64'
                );
            }
            if( ! preg_match('/[a-zA-Z0-9-]/', $answer) ) {
                throw new RuntimeException(
                    'Only letters, number and dashes are accepted as storage name'
                );
            }
            return $answer;
        });
        $question->setMaxAttempts(2);
        $data['storageName'] = $helper->ask($input, $output, $question);

        $question = new Question('Please enter storage password');
        $question
            ->setHidden(true)
            ->setHiddenFallback(false);
        $question->setNormalizer(function ($value) {
            // $value can be null here
            return $value ? trim($value) : '';
        });
        $question->setValidator(function ($value) {
            if( ! preg_match('/[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}-[a-f0-9]{4}-[a-f0-9]{4}/', $value) ) {
                throw new RuntimeException(
                    'Wrong storage password pattern, please fix it'
                );
            }
            return $value;
        });
        $question->setMaxAttempts(2);
        $data['password'] = $helper->ask($input, $output, $question);

        return $data;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');
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
            $userPassword = $helper->ask($input, $output, $question);
        }
        $question = new Question('Please enter a descriptive name for you account');
        $question->setTrimmable(true);
        $question->setNormalizer(function ($value) {
            // $value can be null here
            return $value ? trim($value) : '';
        });
        $question->setValidator(function ($answer) {
            if( !is_string($answer) || strlen($answer) < 6 ) {
                throw new RuntimeException(
                    'The name must be a string with a minimum length of 6'
                );
            }

            return $answer;
        });
        $question->setMaxAttempts(2);
        $accountName = $helper->ask($input, $output, $question);

        $question = new ChoiceQuestion(
            'Please select account type',
            ['cdn', 'storage']
        );
        $question->setErrorMessage('Account type %s is invalid.');
        $accountTYpe = $helper->ask($input, $output, $question);

        $methodName = sprintf('get%sData', ucfirst($accountTYpe));


        $this->credentialsHelper->saveCredential($accountName, $accountTYpe, $this->$methodName($input, $output, $helper));
        return Command::SUCCESS;
    }
}