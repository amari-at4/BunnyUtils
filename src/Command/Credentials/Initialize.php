<?php

namespace App\Command\Credentials;

use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class Initialize extends Base
{
    protected static $defaultName = 'credentials:initialize';

    private OutputInterface $output;

    protected function configure(): void
    {
        $this
            ->setHelp('With this command you can add new Bunny credentials')
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'If the key exists force to recreate, this operation is dangerous because the current encrypted credentials will be deleted'
            )
        ;
    }

    /**
     * @throws EnvironmentIsBrokenException
     */
    private function createNewKey()
    {
        $this->encryptionHelper->createNewKeyFile();
        $this
            ->output
            ->writeln(
                sprintf(
                    '<info>Credentials key initialised at: %s</info>',
                    $this->encryptionHelper->keyFile()
                )
            );
    }

    /**
     * @throws EnvironmentIsBrokenException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;
        if( file_exists($this->encryptionHelper->keyFile()) ) {
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
                $this->encryptionHelper->removeCredentials();
            }
        }
        $this->createNewKey();
        return Command::SUCCESS;
    }
}