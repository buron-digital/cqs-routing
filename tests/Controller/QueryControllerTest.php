<?php

declare(strict_types=1);

namespace BuronDigital\CQSRouting\Controller;

use BuronDigital\CQSRouting\DTOConstructor\SerializerDTOConstructor;
use BuronDigital\CQSRouting\RequestDecoder\JsonRequestDecoder;
use BuronDigital\CQSRouting\ResponseConstructor\EmptyJsonResponseConstructor;
use BuronDigital\CQSRouting\ResponseConstructor\SerializerJsonResponseConstructor;
use BuronDigital\CQSRouting\Routing\RouteConfigurationBuilder;
use BuronDigital\CQSRouting\Routing\RoutePayload;
use BuronDigital\CQSRouting\Test\Application\AddActionIdRequestDataTransformer;
use BuronDigital\CQSRouting\Test\Application\UserIdValidator;
use BuronDigital\CQSRouting\Test\Domain\Tasks\ReadSide\GetTasks\Exception\TasksNotAccessible;
use BuronDigital\CQSRouting\Test\Domain\Tasks\ReadSide\GetTasks\FailingGetTasksQueryHandler;
use BuronDigital\CQSRouting\Test\Domain\Tasks\ReadSide\GetTasks\GetTasksHandlerWrapper;
use BuronDigital\CQSRouting\Test\Domain\Tasks\ReadSide\GetTasks\GetTasksQuery;
use BuronDigital\CQSRouting\Test\Domain\Tasks\ReadSide\GetTasks\GetTasksQueryHandler;
use BuronDigital\CQSRouting\Test\Entity\Task;
use BuronDigital\CQSRouting\Test\Helper\ServiceMapHelper;
use BuronDigital\CQSRouting\Test\Repository\TasksInMemoryRepository;
use BuronDigital\CQSRouting\Test\RequestValidator\GuardAgainstFileWithVirusRequestValidator;
use BuronDigital\CQSRouting\Test\Utility\LockSimulator;
use BuronDigital\CQSRouting\Test\Utility\SecuritySimulator;
use BuronDigital\CQSRouting\Test\Utility\VirusScannerSimulator;
use BuronDigital\CQSRouting\Test\ValueObject\TaskId;
use BuronDigital\CQSRouting\Test\ValueObject\UserId;
use BuronDigital\SelfAwareNormalizers\Serializer\ArrayNormalizableNormalizer;
use BuronDigital\SelfAwareNormalizers\Serializer\StringNormalizableNormalizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;

#[CoversClass(QueryController::class)]
final class QueryControllerTest extends TestCase
{
    private RouteConfigurationBuilder $defaultRouteConfigurationBuilder;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->defaultRouteConfigurationBuilder = new RouteConfigurationBuilder(
            defaultRequestValidatorClassesForCommand: [],
            defaultRequestDecoderClassForCommand: JsonRequestDecoder::class,
            defaultRequestDataTransformerClassesForCommand: [],
            defaultDTOConstructorClassForCommand: SerializerDTOConstructor::class,
            defaultDTOValidatorClassesForCommand: [],
            defaultHandlerWrapperClassesForCommand: [],
            defaultResponseConstructorClassForCommand: EmptyJsonResponseConstructor::class,
            defaultRequestValidatorClassesForQuery: [],
            defaultRequestDecoderClassForQuery: JsonRequestDecoder::class,
            defaultRequestDataTransformerClassesForQuery: [],
            defaultDTOConstructorClassForQuery: SerializerDTOConstructor::class,
            defaultDTOValidatorClassesForQuery: [],
            defaultHandlerWrapperClassesForQuery: [],
            defaultResponseConstructorClassForQuery: SerializerJsonResponseConstructor::class,
        );
    }

    #[Test]
    public function query_controller_works_with_all_components(): void
    {
        // -- Arrange

        $authenticatedUserId = UserId::generateRandom();
        $serializer = new Serializer([
            new ArrayDenormalizer(),
            new StringNormalizableNormalizer(),
            new ArrayNormalizableNormalizer(),
            new PropertyNormalizer(
                null,
                null,
                new PropertyInfoExtractor([], [new PhpDocExtractor(), new ReflectionExtractor()]),
            ),
        ], [
            new JsonEncoder(),
        ]);
        $tasksInMemoryRepository = new TasksInMemoryRepository([
            new Task(
                TaskId::generateRandom(),
                $authenticatedUserId,
                'Finish all tests',
            ),
            new Task(
                TaskId::generateRandom(),
                $authenticatedUserId,
                'Improve documentation',
            ),
        ]);
        $lockSimulator = new LockSimulator();

        $securitySimulator = new SecuritySimulator();
        $securitySimulator->fixateAuthenticatedUserId($authenticatedUserId);

        $controller = new QueryController(
            ServiceMapHelper::serviceMap(
                requestValidators: [
                    new GuardAgainstFileWithVirusRequestValidator(new VirusScannerSimulator()),
                ],
                requestDecoders: [
                    new JsonRequestDecoder(),
                ],
                requestDataTransformers: [
                    new AddActionIdRequestDataTransformer(),
                ],
                dtoConstructors: [
                    new SerializerDTOConstructor($serializer),
                ],
                dtoValidators: [
                    new UserIdValidator($securitySimulator),
                ],
                handlerWrappers: [
                    new GetTasksHandlerWrapper($lockSimulator),
                ],
                queryHandlers: [
                    new GetTasksQueryHandler($tasksInMemoryRepository),
                ],
                responseConstructors: [
                    new SerializerJsonResponseConstructor($serializer),
                ],
            ),
            $this->defaultRouteConfigurationBuilder,
        );

        $content = [
            'userId' => $authenticatedUserId->normalize(),
        ];

        $request = new Request(content: json_encode($content, JSON_THROW_ON_ERROR));
        $routePayload = RoutePayload::generatePayload(
            dtoClass: GetTasksQuery::class,
            handlerClass: GetTasksQueryHandler::class,
            requestValidatorClasses: [
                GuardAgainstFileWithVirusRequestValidator::class => null,
            ],
            requestDataTransformerClasses: [
                AddActionIdRequestDataTransformer::class => null,
            ],
            dtoValidatorClasses: [
                UserIdValidator::class => null,
            ],
            handlerWrapperClasses: [
                GetTasksHandlerWrapper::class => null,
            ],
        );

        // -- Act
        $response = $controller->handle($request, $routePayload);

        // -- Assert
        /** @var string $body */
        $body = $response->getContent();
        $tasks = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        self::assertCount(2, $tasks);
        self::assertCount(1, $lockSimulator->lockedActions);
        self::assertCount(1, $lockSimulator->unlockedActions);
    }

    #[Test]
    public function query_controller_works_with_handler_wrapper_in_catch_case_with_multiple_catches(): void
    {
        // -- Assert
        $this->expectException(TasksNotAccessible::class);

        // -- Arrange

        $authenticatedUserId = UserId::generateRandom();
        $serializer = new Serializer([
            new ArrayDenormalizer(),
            new StringNormalizableNormalizer(),
            new ArrayNormalizableNormalizer(),
            new PropertyNormalizer(
                null,
                null,
                new PropertyInfoExtractor([], [new PhpDocExtractor(), new ReflectionExtractor()]),
            ),
        ], [
            new JsonEncoder(),
        ]);
        $lockSimulator = new LockSimulator();

        $securitySimulator = new SecuritySimulator();
        $securitySimulator->fixateAuthenticatedUserId($authenticatedUserId);

        $controller = new QueryController(
            ServiceMapHelper::serviceMap(
                requestDecoders: [
                    new JsonRequestDecoder(),
                ],
                requestDataTransformers: [
                    new AddActionIdRequestDataTransformer(),
                ],
                dtoConstructors: [
                    new SerializerDTOConstructor($serializer),
                ],
                dtoValidators: [
                    new UserIdValidator($securitySimulator),
                ],
                handlerWrappers: [
                    new GetTasksHandlerWrapper($lockSimulator),
                ],
                queryHandlers: [
                    new FailingGetTasksQueryHandler(),
                ],
                responseConstructors: [
                    new SerializerJsonResponseConstructor($serializer),
                ],
            ),
            $this->defaultRouteConfigurationBuilder,
        );

        $content = [
            'userId' => (string) $authenticatedUserId,
        ];

        $request = new Request(content: json_encode($content, JSON_THROW_ON_ERROR));
        $routePayload = RoutePayload::generatePayload(
            dtoClass: GetTasksQuery::class,
            handlerClass: FailingGetTasksQueryHandler::class,
            requestDataTransformerClasses: [
                AddActionIdRequestDataTransformer::class => null,
            ],
            dtoValidatorClasses: [
                UserIdValidator::class => null,
            ],
            handlerWrapperClasses: [
                GetTasksHandlerWrapper::class => null,
            ],
        );

        // -- Act
        $controller->handle($request, $routePayload);
    }
}
