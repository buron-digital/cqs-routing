<?php

declare(strict_types=1);

namespace BuronDigital\CQSRouting\Test\Application;

use BuronDigital\CQSRouting\Command\Command;
use BuronDigital\CQSRouting\DTOValidator\DTOValidator;
use BuronDigital\CQSRouting\Query\Query;
use BuronDigital\CQSRouting\Test\Domain\News\WriteSide\CreateNewsArticle\CreateNewsArticleCommand;
use BuronDigital\CQSRouting\Test\Utility\SecuritySimulator;
use Symfony\Component\HttpFoundation\Request;

final readonly class UserIdValidator implements DTOValidator
{
    public function __construct(
        private SecuritySimulator $securitySimulator,
    ) {
    }

    /**
     * @param CreateNewsArticleCommand $dto
     * @param null                     $parameters
     */
    #[\Override]
    public function validateDTO(
        Request $request,
        Command | Query $dto,
        mixed $parameters,
    ): void {
        if ($this->securitySimulator->getAuthenticatedUserId()->isNotEqualTo($dto->userId)) {
            throw new \DomainException('Supplied user id is invalid');
        }
    }

    /** @param null $parameters */
    #[\Override]
    public static function areParametersValid(mixed $parameters): bool
    {
        return $parameters === null;
    }
}
