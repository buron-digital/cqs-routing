<?php

declare(strict_types=1);

namespace BuronDigital\CQSRouting\Test\Domain\Tasks\WriteSide\CreateTask;

use BuronDigital\CQSRouting\Command\CommandHandler;

final class CreateTaskCommandHandler implements CommandHandler
{
    public function __invoke(CreateTaskCommand $command): void
    {
        // Create new task and store it ...
    }
}
