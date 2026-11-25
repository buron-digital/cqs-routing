<?php

declare(strict_types=1);

namespace BuronDigital\CQSRouting\Test\Domain\News\WriteSide\CreateNewsArticle;

use BuronDigital\CQSRouting\Command\Command;
use BuronDigital\CQSRouting\Test\ValueObject\UserId;

final readonly class CreateNewsArticleCommand implements Command
{
    public function __construct(
        public UserId $userId,
        public string $title,
        public string $content,
        public bool $isPublished,
    ) {
    }
}
