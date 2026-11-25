<?php

declare(strict_types=1);

namespace BuronDigital\CQSRouting\Test\Entity;

use BuronDigital\CQSRouting\Test\ValueObject\TaskId;
use BuronDigital\CQSRouting\Test\ValueObject\UserId;

final class Task
{
    public function __construct(
        /** @psalm-readonly */
        public TaskId $taskId,
        public UserId $createdByUserId,
        public string $title,
    ) {
    }
}
