<?php

namespace App\Enum;

/**
 * @method static STORAGE()
 * @method static CDN()
 */
class AccountType extends Base
{
    public const STORAGE = 'storage';
    public const CDN = 'cdn';
}