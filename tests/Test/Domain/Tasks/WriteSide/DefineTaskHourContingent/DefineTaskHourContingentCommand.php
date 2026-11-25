<?php

declare(strict_types=1);

namespace BuronDigital\CQSRouting\Test\Domain\Tasks\WriteSide\DefineTaskHourContingent;

use BuronDigital\CQSRouting\Command\Command;

final readonly class DefineTaskHourContingentCommand implements Command
{
    public function __construct(
        public float $hourContingent,
    ) {
    }
}
