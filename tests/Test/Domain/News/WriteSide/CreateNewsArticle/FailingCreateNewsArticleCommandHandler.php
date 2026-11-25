<?php

declare(strict_types=1);

namespace BuronDigital\CQSRouting\Test\Domain\News\WriteSide\CreateNewsArticle;

use BuronDigital\CQSRouting\Command\CommandHandler;
use BuronDigital\CQSRouting\Test\Domain\News\WriteSide\CreateNewsArticle\Exception\NewsArticleAlreadyExists;

final readonly class FailingCreateNewsArticleCommandHandler implements CommandHandler
{
    public function __invoke(CreateNewsArticleCommand $command): void
    {
        // Some logic that validates it

        throw new NewsArticleAlreadyExists();
    }
}
