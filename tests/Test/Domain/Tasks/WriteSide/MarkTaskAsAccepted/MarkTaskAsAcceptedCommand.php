<?php

declare(strict_types=1);

namespace BuronDigital\CQSRouting\Test\Domain\Tasks\WriteSide\MarkTaskAsAccepted;

use BuronDigital\CQSRouting\Command\Command;
use BuronDigital\CQSRouting\Test\ValueObject\TaskId;

final readonly class MarkTaskAsAcceptedCommand implements Command
{
    public function __construct(
        public TaskId $taskId,
    ) {
    }
}
