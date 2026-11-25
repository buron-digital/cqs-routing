<?php

declare(strict_types=1);

namespace BuronDigital\CQSRouting\Routing;

use BuronDigital\CQSRouting\Command\Command;
use BuronDigital\CQSRouting\Command\CommandHandler;
use BuronDigital\CQSRouting\DTOConstructor\DTOConstructor;
use BuronDigital\CQSRouting\DTOValidator\DTOValidator;
use BuronDigital\CQSRouting\HandlerWrapper\HandlerWrapper;
use BuronDigital\CQSRouting\Query\Query;
use BuronDigital\CQSRouting\Query\QueryHandler;
use BuronDigital\CQSRouting\RequestDataTransformer\RequestDataTransformer;
use BuronDigital\CQSRouting\RequestDecoder\RequestDecoder;
use BuronDigital\CQSRouting\RequestValidator\RequestValidator;
use BuronDigital\CQSRouting\ResponseConstructor\ResponseConstructor;

/**
 * The configuration is the result of the route payload in combination with the defaults to be used by the controller.
 *
 * @psalm-import-type NormalizedConfigurationParameters from RoutePayload
 */
final readonly class RouteConfiguration
{
    /**
     * @param class-string<Command>|class-string<Query>                                      $dtoClass
     * @param class-string<CommandHandler>|class-string<QueryHandler>                        $handlerClass
     * @param array<class-string<RequestValidator>, NormalizedConfigurationParameters>       $requestValidatorClasses
     * @param class-string<RequestDecoder>                                                   $requestDecoderClass
     * @param array<class-string<RequestDataTransformer>, NormalizedConfigurationParameters> $requestDataTransformerClasses
     * @param class-string<DTOConstructor>                                                   $dtoConstructorClass
     * @param array<class-string<DTOValidator>, NormalizedConfigurationParameters>           $dtoValidatorClasses
     * @param array<class-string<HandlerWrapper>, NormalizedConfigurationParameters>         $handlerWrapperClasses
     * @param class-string<ResponseConstructor>                                              $responseConstructorClass
     */
    public function __construct(
        public string $dtoClass,
        public string $handlerClass,
        public array $requestValidatorClasses,
        public string $requestDecoderClass,
        public array $requestDataTransformerClasses,
        public string $dtoConstructorClass,
        public array $dtoValidatorClasses,
        public array $handlerWrapperClasses,
        public string $responseConstructorClass,
    ) {
    }
}
