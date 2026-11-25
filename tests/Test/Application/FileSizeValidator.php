<?php

declare(strict_types=1);

namespace BuronDigital\CQSRouting\Test\Application;

use BuronDigital\CQSRouting\Command\Command;
use BuronDigital\CQSRouting\DTOValidator\DTOValidator;
use BuronDigital\CQSRouting\Query\Query;
use BuronDigital\CQSRouting\Test\Application\Exception\FileSizeTooLarge;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

final class FileSizeValidator implements DTOValidator
{
    /** @param int $parameters Max upload size in MB */
    #[\Override]
    public function validateDTO(
        Request $request,
        Command | Query $dto,
        mixed $parameters,
    ): void {
        $reflection = new \ReflectionClass($dto);
        foreach ($reflection->getProperties() as $prop) {
            $name = $prop->name;
            /** @psalm-suppress MixedAssignment */
            $dtoProp = $dto->$name;
            if ($dtoProp instanceof UploadedFile
                && $dtoProp->getSize() > self::megabyteToByte($parameters)
            ) {
                throw new FileSizeTooLarge(self::byteToMegabyte($dtoProp->getSize()), $parameters);
            }
        }
    }

    private static function megabyteToByte(int $mb): int
    {
        return $mb * 1024 * 1024;
    }

    public static function byteToMegabyte(int $bytes): int
    {
        return (int) round($bytes / 1024 / 1024);
    }

    /** @param int $parameters */
    #[\Override]
    public static function areParametersValid(mixed $parameters): bool
    {
        return is_int($parameters)
            && $parameters > 0;
    }
}
