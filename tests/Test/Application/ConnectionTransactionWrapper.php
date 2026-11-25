<?php

declare(strict_types=1);

namespace BuronDigital\CQSRouting\Test\Application;

use BuronDigital\CQSRouting\Command\Command;
use BuronDigital\CQSRouting\HandlerWrapper\HandlerWrapper;
use BuronDigital\CQSRouting\Query\Query;
use BuronDigital\CQSRouting\Test\Utility\ConnectionSimulator;
use Symfony\Component\HttpFoundation\Request;

final class ConnectionTransactionWrapper implements HandlerWrapper
{
    public function __construct(
        private readonly ConnectionSimulator $connectionSimulator,
    ) {
    }

    /** @param null $parameters */
    #[\Override]
    public function prepare(
        Command | Query $dto,
        Request $request,
        mixed $parameters,
    ): void {
        $this->connectionSimulator->beginTransaction();
    }

    /** @param null $parameters */
    #[\Override]
    public function catch(
        Command | Query $dto,
        Request $request,
        mixed $parameters,
        \Exception $exception,
    ): ?\Exception {
        $this->connectionSimulator->rollBack();

        return $exception;
    }

    /** @param null $parameters */
    #[\Override]
    public function then(
        Command | Query $dto,
        Request $request,
        mixed $parameters,
    ): void {
        $this->connectionSimulator->commit();
    }

    // Priorities

    #[\Override]
    public static function preparePriority(): int
    {
        return 50;
    }

    #[\Override]
    public static function catchPriority(): int
    {
        return 50;
    }

    #[\Override]
    public static function thenPriority(): int
    {
        return 50;
    }

    /** @param null $parameters */
    #[\Override]
    public static function areParametersValid(mixed $parameters): bool
    {
        return $parameters === null;
    }
}
