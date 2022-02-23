<?php

namespace App\Model\Account;

use Generator;
use IteratorAggregate;
use Symfony\Component\Serializer\Annotation\Groups;

class Accounts implements IteratorAggregate
{
    use SerializableTrait;

    /**
     * @var Account[]
     * @Groups({"serialize", "deserialize"})
     */
    private array $accounts = [];

    public function getIterator(): Generator
    {
        yield from $this->accounts;
    }

    public function addAccount(Account $account): Accounts
    {
        $this->accounts[] = $account;

        return $this;
    }
}