<?php

declare(strict_types=1);

namespace BuronDigital\CQSRouting\Test\Domain\Tasks\ReadSide\GetTasks;

use BuronDigital\CQSRouting\Query\QueryHandler;
use BuronDigital\CQSRouting\Test\Domain\Tasks\ReadSide\GetTasks\Exception\TasksNotAccessible;

final readonly class FailingGetTasksQueryHandler implements QueryHandler
{
    public function __invoke(GetTasksQuery $query): array
    {
        throw new TasksNotAccessible();
    }
}
