<?php

namespace App\Model\Account;

use Defuse\Crypto\Key;
use Symfony\Component\Serializer\Annotation\Groups;

class Storage extends Account
{
    /**
     * @var string
     * @Groups({"serialize", "deserialize"})
     */
    private string $storageName;

    /**
     * @return string
     */
    public function getStorageName(): string
    {
        return $this->storageName;
    }

    /**
     * @param string $storageName
     * @return Storage
     */
    public function setStorageName(string $storageName): Storage
    {
        $this->storageName = $storageName;
        return $this;
    }

    /**
     * @param array $data
     * @param Key   $key
     */
    public function processData(array $data, Key $key)
    {
        $this->storageName = $data['storageName'];
        $this->password = new EncryptedString();
        $this
            ->password
            ->setKey($key)
            ->setDecryptedString($data['password'])
        ;
    }
}