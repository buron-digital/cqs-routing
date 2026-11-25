<?php

declare(strict_types=1);

namespace BuronDigital\CQSRouting\Test\Domain\Tasks\WriteSide\MarkTaskAsAccepted;

use BuronDigital\CQSRouting\Command\CommandHandler;
use BuronDigital\CQSRouting\Test\Domain\Tasks\WriteSide\MarkTaskAsAccepted\Exception\TaskAlreadyAccepted;

final readonly class MarkTaskAsAcceptedCommandHandler implements CommandHandler
{
    public function handle(MarkTaskAsAcceptedCommand $command): void
    {
        // Task was already marked as accepted
        throw new TaskAlreadyAccepted();
        // Mark task as accepted...
    }
}
