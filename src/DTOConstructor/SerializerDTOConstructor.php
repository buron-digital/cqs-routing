<?php

declare(strict_types=1);

namespace BuronDigital\CQSRouting\DTOConstructor;

use BuronDigital\CQSRouting\Command\Command;
use BuronDigital\CQSRouting\Query\Query;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final readonly class SerializerDTOConstructor implements DTOConstructor
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(
        private DenormalizerInterface $serializer,
    ) {
    }

    /**
     * @psalm-template T of Command|Query
     *
     * @psalm-param class-string<T> $dtoClass
     *
     * @psalm-return T
     */
    #[\Override]
    public function constructDTO(array $requestData, string $dtoClass): Command | Query
    {
        /**
         * @psalm-var T
         */
        return $this->serializer->denormalize($requestData, $dtoClass);
    }
}
