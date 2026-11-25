<?php

declare(strict_types=1);

namespace BuronDigital\CQSRouting\Test\Domain\News\WriteSide\CreateNewsArticle;

use BuronDigital\CQSRouting\Command\CommandHandler;
use BuronDigital\CQSRouting\Test\Entity\NewsArticle;
use BuronDigital\CQSRouting\Test\Repository\NewsArticleInMemoryRepository;
use BuronDigital\CQSRouting\Test\ValueObject\NewsArticleId;

final readonly class CreateNewsArticleCommandHandler implements CommandHandler
{
    public function __construct(
        private NewsArticleInMemoryRepository $newsArticleInMemoryRepository,
    ) {
    }

    public function __invoke(CreateNewsArticleCommand $command): void
    {
        $newsArticle = new NewsArticle(
            NewsArticleId::generateRandom(),
            $command->userId,
            $command->title,
            $command->content,
            $command->isPublished,
        );

        $this->newsArticleInMemoryRepository->store($newsArticle);
    }
}
