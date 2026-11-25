<?php

declare(strict_types=1);

namespace BuronDigital\CQSRouting\DTOValidator;

use BuronDigital\CQSRouting\Command\Command;
use BuronDigital\CQSRouting\Query\Query;
use BuronDigital\CQSRouting\Routing\RoutePayload;
use Symfony\Component\HttpFoundation\Request;

/**
 * DTO validators are there to validate data within the DTO against information on an application and infrastructure level.
 *
 * Multiple DTO validators can be applied on each request.
 *
 * It must not be used to:
 * - Validate the integrity of the DTO itself
 * - Validate any of the value objects in it (that's the task of the constructors).
 * - Validate any kind of business logic including access validation.
 *
 * @see https://github.com/buron-digital/cqs-routing/blob/main/docs/process.md
 * @see https://github.com/buron-digital/cqs-routing/blob/main/docs/examples/dto-validator.md
 *
 * @psalm-import-type NormalizedConfigurationParameters from RoutePayload
 */
interface DTOValidator
{
    /**
     * @param NormalizedConfigurationParameters $parameters
     */
    public function validateDTO(
        Request $request,
        Command | Query $dto,
        mixed $parameters,
    ): void;

    /**
     * @param NormalizedConfigurationParameters $parameters
     */
    public static function areParametersValid(mixed $parameters): bool;
}
