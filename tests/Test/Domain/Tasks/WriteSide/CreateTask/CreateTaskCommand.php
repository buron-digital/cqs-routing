<?php

declare(strict_types=1);

namespace BuronDigital\CQSRouting\Test\Domain\Tasks\WriteSide\CreateTask;

use BuronDigital\CQSRouting\Command\Command;

final readonly class CreateTaskCommand implements Command
{
    public function __construct(
        public string $title,
        public string $content,
        public string $priority,
    ) {
    }
}
