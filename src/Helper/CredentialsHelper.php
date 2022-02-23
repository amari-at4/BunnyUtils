<?php

namespace App\Helper;

use App\Enum\AccountType;
use App\Model\Account\Account;
use App\Model\Account\Accounts;
use Defuse\Crypto\Exception\BadFormatException;
use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
use Defuse\Crypto\Key;
use Defuse\Crypto\KeyProtectedByPassword;
use Doctrine\Common\Annotations\AnnotationReader;
use Elao\Enum\Bridge\Symfony\Serializer\Normalizer\EnumNormalizer;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorFromClassMetadata;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class CredentialsHelper
{
    private Kernel $kernel;
    private Filesystem $filesystem;
    private array $database;
    private Key $key;

    public function __construct()
    {
        $this->kernel = $GLOBALS['kernel'];
        $this->filesystem = new Filesystem();
        $this->initializeCredentialsDatabase();
    }

    private function initializeCredentialsDatabase()
    {
        if( ! $this->filesystem->exists($this->getBaseCredentialsDir()) ) {
            $this->filesystem->mkdir($this->getBaseCredentialsDir(), 0700);
        }
        if( ! $this->filesystem->exists($this->getAccountsDir()) ) {
            $this->filesystem->mkdir($this->getAccountsDir(), 0700);
        }
        if( ! $this->filesystem->exists($this->getAccountDatabaseFile()) ) {
            $this->database = [];
            file_put_contents($this->getAccountDatabaseFile(), json_encode($this->database, JSON_PRETTY_PRINT));
            chmod($this->getAccountDatabaseFile(), 0600);
        } else {
            $this->loadDatabase();
        }
    }

    /**
     * @throws ExceptionInterface
     */
    private function saveDatabase()
    {
        file_put_contents($this->getAccountDatabaseFile(), json_encode($this->database, JSON_PRETTY_PRINT));
        chmod($this->getAccountDatabaseFile(), 0600);
    }

    private function loadDatabase()
    {
        $this->database = $this->deserializeDatabase(file_get_contents($this->getAccountDatabaseFile()));
    }

    public function removeCredentials()
    {
        if( $this->filesystem->exists($this->getBaseCredentialsDir()) ) {
            $this->filesystem->remove($this->getBaseCredentialsDir());
            $this->initializeCredentialsDatabase();
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

    public function keyFile(bool $crypt = false): string
    {
        return sprintf(
            '%s%skey_file%s.txt',
            $this->getBaseCredentialsDir(),
            DIRECTORY_SEPARATOR,
            $crypt ? '.crypt' : ''
        );
    }

    public function getAccountDatabaseFile(): string
    {
        return sprintf(
            '%s%sdatabase.json',
            $this->getAccountsDir(),
            DIRECTORY_SEPARATOR
        );
    }

    /**
     * @throws EnvironmentIsBrokenException
     */
    public function createNewKeyFile(?string $password = null)
    {
        if( $password ) {
            $randomKey = KeyProtectedByPassword::createRandomPasswordProtectedKey($password);
        } else {
            $randomKey = Key::createNewRandomKey();
        }
        $file = $this->keyFile((bool)$password);
        file_put_contents(
            $file,
            $randomKey->saveToAsciiSafeString()
        );
        chmod($file, 0600);
    }

    /**
     * @param string|null $password
     * @param bool        $force
     * @return Key
     * @throws BadFormatException
     * @throws EnvironmentIsBrokenException
     * @throws WrongKeyOrModifiedCiphertextException
     */
    public function getKey(?string $password = null, bool $force = false): Key
    {
        if( ! isset($this->key) || $force ) {
            if( $password && $this->filesystem->exists($this->keyFile(true)) ) {
                $protectedKey = KeyProtectedByPassword::loadFromAsciiSafeString(file_get_contents($this->keyFile(true)));
                $this->key = $protectedKey->unlockKey($password);
            } elseif( $this->filesystem->exists($this->keyFile()) ) {
                $this->key = Key::loadFromAsciiSafeString(file_get_contents($this->keyFile()));
            } else {
                throw new RuntimeException('Key file not exist');
            }
        }

        return $this->key;
    }

    /**
     * @return bool
     */
    public function isEncryptedKey(): bool
    {
        return $this->filesystem->exists($this->keyFile(true));
    }

    /**
     * @throws ExceptionInterface
     */
    public function saveCredential(string $accountName, string $accountType, array $data)
    {
        $className = match ($accountType) {
            'cdn' => '\App\Model\Account\Cdn',
            default => '\App\Model\Account\Storage',
        };
        /** @var Account $account */
        $account = new $className();
        $account
            ->setAccountName($accountName)
            ->setAccountType(AccountType::get($accountType))
        ;
        $account->processData($data, $this->key);

        $this->database[] = $account;
        $this->saveDatabase();
    }

    public function deserializeDatabase(string $data): array
    {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $discriminator = new ClassDiscriminatorFromClassMetadata($classMetadataFactory);
        $normalizer = [
            new ArrayDenormalizer(),
            new EnumNormalizer(),
            new ObjectNormalizer($classMetadataFactory, null, null, null, $discriminator),
        ];
        $encoders = [new JsonEncoder()];
        $serializer = new Serializer($normalizer, $encoders);
        return $serializer->deserialize($data, Account::class . '[]', 'json', ['groups' => 'deserialize']);
    }
}