<?php

declare(strict_types=1);

namespace BuronDigital\CQSRouting\Test\Domain\Tasks\ReadSide\GetTasks;

use BuronDigital\CQSRouting\Query\Query;
use BuronDigital\CQSRouting\Test\ValueObject\ActionId;
use BuronDigital\CQSRouting\Test\ValueObject\UserId;

final readonly class GetTasksQuery implements Query
{
    public function __construct(
        public UserId $userId,
        public ActionId $actionId,
    ) {
    }
}
