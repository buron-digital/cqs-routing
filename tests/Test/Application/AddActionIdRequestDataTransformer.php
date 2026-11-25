<?php

declare(strict_types=1);

namespace BuronDigital\CQSRouting\Test\Application;

use BuronDigital\CQSRouting\Command\Command;
use BuronDigital\CQSRouting\Query\Query;
use BuronDigital\CQSRouting\RequestDataTransformer\RequestDataTransformer;
use BuronDigital\CQSRouting\Test\ValueObject\ActionId;

final class AddActionIdRequestDataTransformer implements RequestDataTransformer
{
    /**
     * @param class-string<Command|Query> $dtoClass
     * @param null                        $parameters
     */
    #[\Override]
    public function transformRequestData(
        string $dtoClass,
        array $requestData,
        mixed $parameters,
    ): array {
        $requestData['actionId'] = (string) ActionId::generateRandom();

        return $requestData;
    }

    /** @param null $parameters */
    #[\Override]
    public static function areParametersValid(mixed $parameters): bool
    {
        return $parameters === null;
    }
}
