<?php

declare(strict_types=1);

namespace BuronDigital\CQSRouting\Test\Application\Authentication;

use BuronDigital\CQSRouting\Command\Command;
use BuronDigital\CQSRouting\DTOValidator\DTOValidator;
use BuronDigital\CQSRouting\Query\Query;
use BuronDigital\CQSRouting\Test\Application\Authentication\Exception\NotRelevantForDTO;
use BuronDigital\CQSRouting\Test\Utility\SecuritySimulator;
use BuronDigital\CQSRouting\Test\ValueObject\UserId;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class UserIdValidator implements DTOValidator
{
    public function __construct(
        private readonly SecuritySimulator $securitySimulator,
    ) {
    }

    /** @param null $parameters */
    #[\Override]
    public function validateDTO(
        Request $request,
        Command | Query $dto,
        mixed $parameters,
    ): void {
        $reflection = new \ReflectionClass($dto);
        if (!$reflection->hasProperty('userId')) {
            throw new NotRelevantForDTO($dto);
        }

        /**
         * @var UserId $userId
         *
         * @psalm-suppress UndefinedPropertyFetch
         * @psalm-suppress NoInterfaceProperties
         */
        $userId = $dto->userId;

        if ($this->securitySimulator->getAuthenticatedUserId()->isNotEqualTo($userId)) {
            throw new \DomainException('Invalid user id', Response::HTTP_FORBIDDEN);
        }
    }

    /** @param null $parameters */
    #[\Override]
    public static function areParametersValid(mixed $parameters): bool
    {
        return $parameters === null;
    }
}
