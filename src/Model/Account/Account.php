<?php

namespace App\Model\Account;

use App\Enum\AccountType;
use Defuse\Crypto\Key;
use JsonSerializable;
use Symfony\Component\Serializer\Annotation\DiscriminatorMap;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @DiscriminatorMap(typeProperty="accountType", mapping={
 *      "storage"="App\Model\Account\Storage",
 * })
 */
abstract class Account implements JsonSerializable
{
    use SerializableTrait;

    /**
     * @var EncryptedString
     * @Groups({"serialize", "deserialize"})
     */
    protected EncryptedString $password;

    /**
     * @var string
     * @Groups({"serialize", "deserialize"})
     */
    protected string $accountName;

    /**
     * @var AccountType
     * @Groups({"serialize", "deserialize"})
     */
    protected AccountType $accountType;

    /**
     * @return string
     */
    public function getAccountName(): string
    {
        return $this->accountName;
    }

    /**
     * @param string $accountName
     * @return Account
     */
    public function setAccountName(string $accountName): Account
    {
        $this->accountName = $accountName;
        return $this;
    }

    /**
     * @return AccountType
     */
    public function getAccountType(): AccountType
    {
        return $this->accountType;
    }

    /**
     * @param AccountType|string $accountType
     * @return Account
     */
    public function setAccountType(AccountType|string $accountType): Account
    {
        if( is_string($accountType) ) {
            $this->accountType = AccountType::get($accountType);
        } else {
            $this->accountType = $accountType;
        }
        return $this;
    }

    /**
     * @return EncryptedString
     */
    public function getPassword(): EncryptedString
    {
        return $this->password;
    }

    /**
     * @param EncryptedString|array $password
     * @return Storage
     */
    public function setPassword(EncryptedString|array $password): Storage
    {
        if( is_array($password) ) {
            $this->password = (new EncryptedString())->setEncryptedString($password['encryptedString']);

        } else {
            $this->password = $password;
        }
        return $this;
    }

    abstract public function processData(array $data, Key $key);
}