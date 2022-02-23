<?php

namespace App\Command\Credentials;

use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class Initialize extends Base
{
    protected static $defaultName = 'credentials:initialize';

    protected function configure(): void
    {
        $this
            ->setHelp('Initialize key to crypt Bunny accounts')
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'If the key exists force to recreate, this operation is dangerous because the current encrypted credentials will be deleted'
            )
            ->addOption(
                'crypt',
                'c',
                InputOption::VALUE_NONE,
                'Crypt the key with an user password, the scripts can not run as unattended mode'
            )
        ;
    }

    /**
     * @throws EnvironmentIsBrokenException
     */
    private function createNewKey(?string $password = null)
    {
        $this->credentialsHelper->createNewKeyFile($password);
        $this
            ->output
            ->writeln(
                sprintf(
                    '<info>Credentials key initialised at: %s</info>',
                    $this->credentialsHelper->keyFile((bool) $password)
                )
            );
    }

    /**
     * @throws EnvironmentIsBrokenException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;
        if( file_exists($this->credentialsHelper->keyFile()) || file_exists($this->credentialsHelper->keyFile(true)) ) {
            if( ! $input->getOption('force') ) {
                $output
                    ->writeln('<error>Key file exists use --force if you want to reinitialize all the database.  Take care about it because all current credentials will be deleted</error>');
                return Command::FAILURE;
            } else {
                $output
                    ->writeln('<error>Entering in forced mode will delete all credentials. Do you want to continue (y/n)?</error>');
                $helper = $this->getHelper('question');
                $question = new ConfirmationQuestion('', false);
                if( ! $helper->ask($input, $output, $question) ) {
                    $output
                        ->writeln("<info>Ok. We don't delete current credentials</info>");
                    return Command::SUCCESS;
                }
                $this->credentialsHelper->removeCredentials();
            }
        }
        $password = null;
        if( $input->getOption('crypt') ) {
            $helper = $this->getHelper('question');

            $question = new Question('Please enter user password');
            $question
                ->setHidden(true)
                ->setHiddenFallback(false);
            $question->setNormalizer(function ($value) {
                // $value can be null here
                return $value ? trim($value) : '';
            });
            $question->setValidator(function ($value) {
                if( strlen(trim($value)) < 8 ) {
                    throw new Exception('The password min length is 8');
                }
                return $value;
            });
            $question->setMaxAttempts(20);
            $password = $helper->ask($input, $output, $question);
            $question = new Question('Please re-enter user password');
            $question
                ->setHidden(true)
                ->setHiddenFallback(false);
            $question->setNormalizer(function ($value) {
                // $value can be null here
                return $value ? trim($value) : '';
            });
            $question->setValidator(function ($value) use ($password) {
                if( trim($value) != $password ) {
                    throw new Exception('Password mismatch');
                }
                return $value;
            });
            $question->setMaxAttempts(20);
            $rePassword = $helper->ask($input, $output, $question);
        }
        $this->createNewKey($password);
        return Command::SUCCESS;
    }
}