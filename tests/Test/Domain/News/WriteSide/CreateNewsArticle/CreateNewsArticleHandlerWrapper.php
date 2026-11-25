<?php

declare(strict_types=1);

namespace BuronDigital\CQSRouting\Test\Domain\News\WriteSide\CreateNewsArticle;

use BuronDigital\CQSRouting\Command\Command;
use BuronDigital\CQSRouting\HandlerWrapper\HandlerWrapper;
use BuronDigital\CQSRouting\Query\Query;
use BuronDigital\CQSRouting\Test\Utility\LockSimulator;
use Symfony\Component\HttpFoundation\Request;

final readonly class CreateNewsArticleHandlerWrapper implements HandlerWrapper
{
    public function __construct(
        private LockSimulator $lockSimulator,
    ) {
    }

    /** @param CreateNewsArticleCommand $dto */
    #[\Override]
    public function prepare(
        Command | Query $dto,
        Request $request,
        mixed $parameters,
    ): void {
        $this->lockSimulator->lockAction((string) $dto->userId);
    }

    /** @param CreateNewsArticleCommand $dto */
    #[\Override]
    public function then(
        Command | Query $dto,
        Request $request,
        mixed $parameters,
    ): void {
        $this->lockSimulator->unlockAction((string) $dto->userId);
    }

    /** @param CreateNewsArticleCommand $dto */
    #[\Override]
    public function catch(
        Command | Query $dto,
        Request $request,
        mixed $parameters,
        \Exception $exception,
    ): ?\Exception {
        $this->lockSimulator->unlockAction((string) $dto->userId);

        return $exception;
    }

    #[\Override]
    public static function preparePriority(): int
    {
        return 250;
    }

    #[\Override]
    public static function catchPriority(): int
    {
        return -250;
    }

    #[\Override]
    public static function thenPriority(): int
    {
        return -250;
    }

    /** @param null $parameters */
    #[\Override]
    public static function areParametersValid(mixed $parameters): bool
    {
        return $parameters === null;
    }
}
