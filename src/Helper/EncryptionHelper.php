<?php

namespace App\Helper;

use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Key;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;

class EncryptionHelper
{
    private Kernel $kernel;
    private Filesystem $filesystem;

    public function __construct()
    {
        $this->kernel = $GLOBALS['kernel'];
        $this->filesystem = new Filesystem();
        $this->createCredentialsDir();
    }

    private function createCredentialsDir()
    {
        if( ! $this->filesystem->exists($this->getBaseCredentialsDir()) ) {
            $this->filesystem->mkdir($this->getBaseCredentialsDir(), 0700);
        }
        if( ! $this->filesystem->exists($this->getAccountsDir()) ) {
            $this->filesystem->mkdir($this->getAccountsDir(), 0700);
        }
    }

    public function removeCredentials()
    {
        if( $this->filesystem->exists($this->getBaseCredentialsDir()) ) {
            $this->filesystem->remove($this->getBaseCredentialsDir());
            $this->createCredentialsDir();
        }
    }

    public function getBaseCredentialsDir(): string
    {
        return sprintf(
            '%1$s%2$sconfig%2$scredentials',
            $this->kernel->getProjectDir(),
            DIRECTORY_SEPARATOR
        );
    }

    public function getAccountsDir(): string
    {
        return sprintf('%s%saccounts', $this->getBaseCredentialsDir(), DIRECTORY_SEPARATOR);
    }

    public function keyFile(): string
    {
        return sprintf(
            '%s%skey_file.txt',
            $this->getBaseCredentialsDir(),
            DIRECTORY_SEPARATOR
        );
    }

    /**
     * @throws EnvironmentIsBrokenException
     */
    public function createNewKeyFile()
    {
        file_put_contents(
            $this->keyFile(),
            Key::createNewRandomKey()->saveToAsciiSafeString()
        );
        chmod($this->keyFile(), 0600);
    }
}