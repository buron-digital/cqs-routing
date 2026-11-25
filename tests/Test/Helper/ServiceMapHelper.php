<?php

declare(strict_types=1);

namespace BuronDigital\CQSRouting\Test\Helper;

use BuronDigital\CQSRouting\Command\CommandHandler;
use BuronDigital\CQSRouting\DTOConstructor\DTOConstructor;
use BuronDigital\CQSRouting\DTOValidator\DTOValidator;
use BuronDigital\CQSRouting\HandlerWrapper\HandlerWrapper;
use BuronDigital\CQSRouting\Query\QueryHandler;
use BuronDigital\CQSRouting\RequestDataTransformer\RequestDataTransformer;
use BuronDigital\CQSRouting\RequestDecoder\RequestDecoder;
use BuronDigital\CQSRouting\RequestValidator\RequestValidator;
use BuronDigital\CQSRouting\ResponseConstructor\ResponseConstructor;
use BuronDigital\CQSRouting\ServiceMap\ServiceMap;
use BuronDigital\CQSRouting\Test\Utility\ServiceLocatorSimulator;

final readonly class ServiceMapHelper
{
    /**
     * @param array<int, RequestValidator>|null       $requestValidators
     * @param array<int, RequestDecoder>|null         $requestDecoders
     * @param array<int, RequestDataTransformer>|null $requestDataTransformers
     * @param array<int, DTOConstructor>|null         $dtoConstructors
     * @param array<int, DTOValidator>|null           $dtoValidators
     * @param array<int, HandlerWrapper>|null         $handlerWrappers
     * @param array<int, CommandHandler>|null         $commandHandlers
     * @param array<int, QueryHandler>|null           $queryHandlers
     * @param array<int, ResponseConstructor>|null    $responseConstructors
     */
    public static function serviceMap(
        ?array $requestValidators = null,
        ?array $requestDecoders = null,
        ?array $requestDataTransformers = null,
        ?array $dtoConstructors = null,
        ?array $dtoValidators = null,
        ?array $handlerWrappers = null,
        ?array $commandHandlers = null,
        ?array $queryHandlers = null,
        ?array $responseConstructors = null,
    ): ServiceMap {
        $requestValidatorsMap = [];
        foreach ($requestValidators ?? [] as $requestValidator) {
            $requestValidatorsMap[$requestValidator::class] = $requestValidator;
        }

        $requestDecodersMap = [];
        foreach ($requestDecoders ?? [] as $requestDecoder) {
            $requestDecodersMap[$requestDecoder::class] = $requestDecoder;
        }

        $requestDataTransformersMap = [];
        foreach ($requestDataTransformers ?? [] as $requestDataTransformer) {
            $requestDataTransformersMap[$requestDataTransformer::class] = $requestDataTransformer;
        }

        $dtoConstructorsMap = [];
        foreach ($dtoConstructors ?? [] as $dtoConstructor) {
            $dtoConstructorsMap[$dtoConstructor::class] = $dtoConstructor;
        }

        $dtoValidatorsMap = [];
        foreach ($dtoValidators ?? [] as $dtoValidator) {
            $dtoValidatorsMap[$dtoValidator::class] = $dtoValidator;
        }

        $handlerWrappersMap = [];
        foreach ($handlerWrappers ?? [] as $handlerWrapper) {
            $handlerWrappersMap[$handlerWrapper::class] = $handlerWrapper;
        }

        $commandHandlersMap = [];
        foreach ($commandHandlers ?? [] as $commandHandler) {
            $commandHandlersMap[$commandHandler::class] = $commandHandler;
        }

        $queryHandlersMap = [];
        foreach ($queryHandlers ?? [] as $queryHandler) {
            $queryHandlersMap[$queryHandler::class] = $queryHandler;
        }

        $responseConstructorsMap = [];
        foreach ($responseConstructors ?? [] as $responseConstructor) {
            $responseConstructorsMap[$responseConstructor::class] = $responseConstructor;
        }

        return new ServiceMap(
            requestValidators: new ServiceLocatorSimulator($requestValidatorsMap),
            requestDecoders: new ServiceLocatorSimulator($requestDecodersMap),
            requestDataTransformers: new ServiceLocatorSimulator($requestDataTransformersMap),
            dtoConstructors: new ServiceLocatorSimulator($dtoConstructorsMap),
            dtoValidators: new ServiceLocatorSimulator($dtoValidatorsMap),
            handlerWrappers: new ServiceLocatorSimulator($handlerWrappersMap),
            commandHandlers: new ServiceLocatorSimulator($commandHandlersMap),
            queryHandlers: new ServiceLocatorSimulator($queryHandlersMap),
            responseConstructors: new ServiceLocatorSimulator($responseConstructorsMap),
        );
    }
}
