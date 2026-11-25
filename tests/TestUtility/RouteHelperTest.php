<?php

declare(strict_types=1);

namespace BuronDigital\CQSRouting\TestUtility;

use BuronDigital\CQSRouting\Controller\CommandController;
use BuronDigital\CQSRouting\Controller\QueryController;
use BuronDigital\CQSRouting\DTOConstructor\SerializerDTOConstructor;
use BuronDigital\CQSRouting\RequestDecoder\JsonRequestDecoder;
use BuronDigital\CQSRouting\ResponseConstructor\EmptyResponseConstructor;
use BuronDigital\CQSRouting\ResponseConstructor\SerializerJsonResponseConstructor;
use BuronDigital\CQSRouting\Routing\RouteBuilder;
use BuronDigital\CQSRouting\Routing\RouteConfigurationBuilder;
use BuronDigital\CQSRouting\Routing\RoutePayload;
use BuronDigital\CQSRouting\Test\AppTestCase;
use BuronDigital\CQSRouting\Test\Domain\Tasks\ReadSide\GetTasks\GetTasksQuery;
use BuronDigital\CQSRouting\Test\Domain\Tasks\ReadSide\GetTasks\GetTasksQueryHandler;
use BuronDigital\CQSRouting\Test\Domain\Tasks\WriteSide\CreateTask\CreateTaskCommand;
use BuronDigital\CQSRouting\Test\Domain\Tasks\WriteSide\CreateTask\CreateTaskCommandHandler;
use BuronDigital\CQSRouting\Test\Router\RouterFake;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

#[CoversClass(RouteHelper::class)]
#[CoversClass(Exception\RouteNotFound::class)]
#[CoversClass(Exception\RouteIsNoNotACQSRoute::class)]
final class RouteHelperTest extends AppTestCase
{
    #[Test]
    public function get_route_configuration_works_for_query(): void
    {
        // -- Arrange
        $routeCollection = new RouteCollection();
        $routeCollection->add(
            'test_route',
            new Route(
                path: '/api/test-route',
                defaults: [
                    '_controller' => QueryController::class,
                    RouteBuilder::ROUTE_PAYLOAD_KEY => RoutePayload::generatePayload(
                        dtoClass: GetTasksQuery::class,
                        handlerClass: GetTasksQueryHandler::class,
                        requestDecoderClass: JsonRequestDecoder::class,
                        dtoConstructorClass: SerializerDTOConstructor::class,
                        responseConstructorClass: SerializerJsonResponseConstructor::class,
                    ),
                ],
            ),
        );

        $router = new RouterFake($routeCollection);

        $routeHelper = new RouteHelper(
            router: $router,
            routeConfigurationBuilder: $this->getContainerService(RouteConfigurationBuilder::class),
        );

        // -- Act
        $routeConfiguration = $routeHelper->getRouteConfiguration('test_route');

        // -- Assert
        self::assertSame(GetTasksQuery::class, $routeConfiguration->dtoClass);
    }

    #[Test]
    public function get_route_configuration_works_for_command(): void
    {
        // -- Arrange
        $routeCollection = new RouteCollection();
        $routeCollection->add(
            'test_route',
            new Route(
                path: '/api/test-route',
                defaults: [
                    '_controller' => CommandController::class,
                    RouteBuilder::ROUTE_PAYLOAD_KEY => RoutePayload::generatePayload(
                        dtoClass: CreateTaskCommand::class,
                        handlerClass: CreateTaskCommandHandler::class,
                        requestDecoderClass: JsonRequestDecoder::class,
                        dtoConstructorClass: SerializerDTOConstructor::class,
                        responseConstructorClass: EmptyResponseConstructor::class,
                    ),
                ],
            ),
        );

        $router = new RouterFake($routeCollection);

        $routeHelper = new RouteHelper(
            router: $router,
            routeConfigurationBuilder: $this->getContainerService(RouteConfigurationBuilder::class),
        );

        // -- Act
        $routeConfiguration = $routeHelper->getRouteConfiguration('test_route');

        // -- Assert
        self::assertSame(CreateTaskCommand::class, $routeConfiguration->dtoClass);
    }

    #[Test]
    public function get_route_configuration_fails_without_route(): void
    {
        // -- Assert
        $this->expectException(Exception\RouteNotFound::class);

        // -- Arrange
        $routeCollection = new RouteCollection();

        $router = new RouterFake($routeCollection);

        $routeHelper = new RouteHelper(
            router: $router,
            routeConfigurationBuilder: $this->getContainerService(RouteConfigurationBuilder::class),
        );

        // -- Act
        $routeHelper->getRouteConfiguration('test_route');
    }

    #[Test]
    public function get_route_configuration_fails_with_route_that_is_not_a_cqs_route(): void
    {
        // -- Assert
        $this->expectException(Exception\RouteIsNoNotACQSRoute::class);

        // -- Arrange
        $routeCollection = new RouteCollection();
        $routeCollection->add(
            'test_route',
            new Route(
                path: '/api/test-route',
                defaults: [
                    '_controller' => 'TestController',
                ],
            ),
        );

        $router = new RouterFake($routeCollection);

        $routeHelper = new RouteHelper(
            router: $router,
            routeConfigurationBuilder: $this->getContainerService(RouteConfigurationBuilder::class),
        );

        // -- Act
        $routeHelper->getRouteConfiguration('test_route');
    }
}
