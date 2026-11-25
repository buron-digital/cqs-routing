<?php

declare(strict_types=1);

namespace BuronDigital\CQSRouting\Test\RequestValidator;

use BuronDigital\CQSRouting\RequestValidator\RequestValidator;
use Symfony\Component\HttpFoundation\Request;

final readonly class GuardAgainstTokenInHeaderRequestValidator implements RequestValidator
{
    /** @param null $parameters */
    #[\Override]
    public function validateRequest(
        Request $request,
        mixed $parameters,
    ): void {
        if ($request->headers->has('X-TOKEN')) {
            throw new \InvalidArgumentException('Token must not be supplied');
        }
    }

    /** @param null $parameters */
    #[\Override]
    public static function areParametersValid(mixed $parameters): bool
    {
        return $parameters === null;
    }
}
