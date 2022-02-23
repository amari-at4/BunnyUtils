<?php

namespace App\Command\Credentials;

use Defuse\Crypto\Exception\BadFormatException;
use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class Show extends Base
{
    protected static $defaultName = 'credentials:list';

    protected function configure(): void
    {
        $this
            ->addOption(
                'reveal',
                'r',
                InputOption::VALUE_NONE,
                'Reveals Password'
            )
            ->setHelp('List current registered credentials')
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if( $input->getOption('reveal') ) {
            $userPassword = $this->askUserPassword($input, $output);
        }
        return Command::SUCCESS;
    }
}