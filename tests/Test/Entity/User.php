<?php

declare(strict_types=1);

namespace BuronDigital\CQSRouting\Test\Entity;

use BuronDigital\CQSRouting\Test\ValueObject\UserId;

final class User
{
    /** @psalm-readonly */
    public UserId $id;

    public string $emailAddress;
}
