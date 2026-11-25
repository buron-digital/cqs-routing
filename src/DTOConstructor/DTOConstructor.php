<?php

declare(strict_types=1);

namespace BuronDigital\CQSRouting\DTOConstructor;

use BuronDigital\CQSRouting\Command\Command;
use BuronDigital\CQSRouting\Query\Query;

/**
 * The DTO constructor is there to construct the command or query from the request data. It also has to throw exceptions when there is data
 * missing. Depending on the implementation that might already be handled by the hydration method.
 *
 * @see https://github.com/buron-digital/cqs-routing/blob/main/docs/process.md
 * @see https://github.com/buron-digital/cqs-routing/blob/main/docs/examplesl/dto-constructor.md
 */
interface DTOConstructor
{
    /**
     * @psalm-template T of Command|Query
     *
     * @psalm-param class-string<T> $dtoClass
     *
     * @psalm-return T
     */
    public function constructDTO(array $requestData, string $dtoClass): Command | Query;
}
