<?php

declare(strict_types=1);

namespace BuronDigital\CQSRouting\Routing\Exception;

/**
 * @psalm-immutable
 *
 * @codeCoverageIgnore
 *
 * @internal
 */
final class ClassIsNoDTOValidator extends \InvalidArgumentException
{
    public function __construct(string $class)
    {
        parent::__construct(sprintf('The class %s is no DTO validator', $class));
    }
}
