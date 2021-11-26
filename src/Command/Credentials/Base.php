<?php

namespace App\Command\Credentials;

use App\Helper\EncryptionHelper;
use Symfony\Component\Console\Command\Command;

abstract class Base extends Command
{
    protected static $defaultName = 'credentials:';
    
    protected EncryptionHelper $encryptionHelper;

    public function __construct(EncryptionHelper $encryptionHelper)
    {
        parent::__construct();
        $this->encryptionHelper = $encryptionHelper;
    }
}