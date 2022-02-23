<?php

namespace App\Model\Account;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
use Defuse\Crypto\Key;
use JsonSerializable;
use Symfony\Component\Serializer\Annotation\Groups;

class EncryptedString implements JsonSerializable
{
    use SerializableTrait;

    /**
     * @var string
     */
    private string $decryptedString;

    /**
     * @var string
     * @Groups({"serialize", "deserialize"})
     */
    private string $encryptedString;

    private Key $key;


    /**
     * @return string
     * @throws EnvironmentIsBrokenException
     * @throws WrongKeyOrModifiedCiphertextException
     */
    public function getDecryptedString(): string
    {
        if( !isset($this->decryptedString) && isset($this->key) && isset($this->encryptedString) ) {
            $this->decryptedString = Crypto::decrypt($this->encryptedString, $this->key);
        }
        return $this->decryptedString;
    }

    /**
     * @param string $decryptedString
     * @return EncryptedString
     */
    public function setDecryptedString(string $decryptedString): EncryptedString
    {
        $this->decryptedString = $decryptedString;
        return $this;
    }

    /**
     * @return string
     * @throws EnvironmentIsBrokenException
     */
    public function getEncryptedString(): string
    {
        if( !isset($this->encryptedString) && isset($this->key) && isset($this->decryptedString) ) {
            $this->encryptedString = Crypto::encrypt($this->decryptedString, $this->key);
        }
        return $this->encryptedString;
    }

    /**
     * @param string $encryptedString
     * @return EncryptedString
     */
    public function setEncryptedString(string $encryptedString): EncryptedString
    {
        $this->encryptedString = $encryptedString;
        return $this;
    }

    /**
     * @return Key
     */
    public function getKey(): Key
    {
        return $this->key;
    }

    /**
     * @param Key $key
     * @return EncryptedString
     */
    public function setKey(Key $key): EncryptedString
    {
        $this->key = $key;
        return $this;
    }

}