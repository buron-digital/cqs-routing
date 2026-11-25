<?php

declare(strict_types=1);

namespace BuronDigital\CQSRouting\Controller;

use BuronDigital\CQSRouting\DTOConstructor\SerializerDTOConstructor;
use BuronDigital\CQSRouting\HandlerWrapper\SilentExceptionWrapper;
use BuronDigital\CQSRouting\RequestDecoder\JsonRequestDecoder;
use BuronDigital\CQSRouting\ResponseConstructor\EmptyJsonResponseConstructor;
use BuronDigital\CQSRouting\ResponseConstructor\SerializerJsonResponseConstructor;
use BuronDigital\CQSRouting\Routing\RouteConfigurationBuilder;
use BuronDigital\CQSRouting\Routing\RoutePayload;
use BuronDigital\CQSRouting\Test\Application\ConnectionTransactionWrapper;
use BuronDigital\CQSRouting\Test\Application\UserIdValidator;
use BuronDigital\CQSRouting\Test\Domain\News\WriteSide\CreateNewsArticle\CreateNewsArticleCommand;
use BuronDigital\CQSRouting\Test\Domain\News\WriteSide\CreateNewsArticle\CreateNewsArticleCommandHandler;
use BuronDigital\CQSRouting\Test\Domain\News\WriteSide\CreateNewsArticle\CreateNewsArticleHandlerWrapper;
use BuronDigital\CQSRouting\Test\Domain\News\WriteSide\CreateNewsArticle\CreateNewsArticleRequestDataTransformer;
use BuronDigital\CQSRouting\Test\Domain\News\WriteSide\CreateNewsArticle\Exception\NewsArticleAlreadyExists;
use BuronDigital\CQSRouting\Test\Domain\News\WriteSide\CreateNewsArticle\FailingCreateNewsArticleCommandHandler;
use BuronDigital\CQSRouting\Test\Helper\ServiceMapHelper;
use BuronDigital\CQSRouting\Test\Repository\NewsArticleInMemoryRepository;
use BuronDigital\CQSRouting\Test\RequestValidator\GuardAgainstFileWithVirusRequestValidator;
use BuronDigital\CQSRouting\Test\Utility\ConnectionSimulator;
use BuronDigital\CQSRouting\Test\Utility\LockSimulator;
use BuronDigital\CQSRouting\Test\Utility\SecuritySimulator;
use BuronDigital\CQSRouting\Test\Utility\VirusScannerSimulator;
use BuronDigital\CQSRouting\Test\ValueObject\UserId;
use BuronDigital\SelfAwareNormalizers\Serializer\ArrayNormalizableNormalizer;
use BuronDigital\SelfAwareNormalizers\Serializer\StringNormalizableNormalizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;

#[CoversClass(CommandController::class)]
final class CommandControllerTest extends TestCase
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
    public function command_controller_works_with_all_components(): void
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
        $newsArticleInMemoryRepository = new NewsArticleInMemoryRepository();
        $lockSimulator = new LockSimulator();
        $securitySimulator = new SecuritySimulator();
        $securitySimulator->fixateAuthenticatedUserId($authenticatedUserId);

        $controller = new CommandController(
            ServiceMapHelper::serviceMap(
                requestValidators: [
                    new GuardAgainstFileWithVirusRequestValidator(new VirusScannerSimulator()),
                ],
                requestDecoders: [
                    new JsonRequestDecoder(),
                ],
                requestDataTransformers: [
                    new CreateNewsArticleRequestDataTransformer(),
                ],
                dtoConstructors: [
                    new SerializerDTOConstructor($serializer),
                ],
                dtoValidators: [
                    new UserIdValidator($securitySimulator),
                ],
                handlerWrappers: [
                    new CreateNewsArticleHandlerWrapper($lockSimulator),
                ],
                commandHandlers: [
                    new CreateNewsArticleCommandHandler($newsArticleInMemoryRepository),
                ],
                responseConstructors: [
                    new EmptyJsonResponseConstructor(),
                ],
            ),
            $this->defaultRouteConfigurationBuilder,
        );

        $content = [
            'userId' => (string) $authenticatedUserId,
            'title' => 'New feature released',
            'content' => '<p>We just released <strong>a new feature</strong> <em>but this em is not allowed</em></p>',
            'isPublished' => false,
        ];

        $request = new Request(content: json_encode($content, JSON_THROW_ON_ERROR));
        $routePayload = RoutePayload::generatePayload(
            dtoClass: CreateNewsArticleCommand::class,
            handlerClass: CreateNewsArticleCommandHandler::class,
            requestValidatorClasses: [
                GuardAgainstFileWithVirusRequestValidator::class => null,
            ],
            requestDataTransformerClasses: [
                CreateNewsArticleRequestDataTransformer::class => null,
            ],
            dtoValidatorClasses: [
                UserIdValidator::class => null,
            ],
            handlerWrapperClasses: [
                CreateNewsArticleHandlerWrapper::class => null,
            ],
        );

        // -- Act
        $controller->handle($request, $routePayload);

        // -- Assert
        self::assertCount(1, $newsArticleInMemoryRepository->newsArticles);
        self::assertCount(1, $lockSimulator->lockedActions);
        self::assertCount(1, $lockSimulator->unlockedActions);
    }

    #[Test]
    public function command_controller_works_with_handler_wrapper_in_catch_case_with_multiple_catches(): void
    {
        // -- Arrange

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
        $connectionSimulator = new ConnectionSimulator();

        $controller = new CommandController(
            ServiceMapHelper::serviceMap(
                requestDecoders: [
                    new JsonRequestDecoder(),
                ],
                dtoConstructors: [
                    new SerializerDTOConstructor($serializer),
                ],
                handlerWrappers: [
                    new SilentExceptionWrapper(),
                    new ConnectionTransactionWrapper($connectionSimulator),
                ],
                commandHandlers: [
                    new FailingCreateNewsArticleCommandHandler(),
                ],
                responseConstructors: [
                    new EmptyJsonResponseConstructor(),
                ],
            ),
            $this->defaultRouteConfigurationBuilder,
        );

        $content = [
            'userId' => (string) UserId::generateRandom(),
            'title' => 'New feature released',
            'content' => '<p>We just released <strong>a new feature</strong> <em>but this em is not allowed</em></p>',
            'isPublished' => false,
        ];

        $request = new Request(content: json_encode($content, JSON_THROW_ON_ERROR));
        $routePayload = RoutePayload::generatePayload(
            dtoClass: CreateNewsArticleCommand::class,
            handlerClass: FailingCreateNewsArticleCommandHandler::class,
            handlerWrapperClasses: [
                SilentExceptionWrapper::class => [
                    NewsArticleAlreadyExists::class,
                ],
                ConnectionTransactionWrapper::class => null,
            ],
        );

        // -- Act
        $response = $controller->handle($request, $routePayload);

        // -- Assert
        self::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        self::assertFalse($connectionSimulator->hasActiveTransaction);
        self::assertFalse($connectionSimulator->hasCommitted);
    }

    #[Test]
    public function command_controller_works_with_handler_wrapper_with_catch_and_throw(): void
    {
        // -- Assert
        $this->expectException(NewsArticleAlreadyExists::class);

        // -- Arrange

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
        $connectionSimulator = new ConnectionSimulator();

        $controller = new CommandController(
            ServiceMapHelper::serviceMap(
                requestDecoders: [
                    new JsonRequestDecoder(),
                ],
                dtoConstructors: [
                    new SerializerDTOConstructor($serializer),
                ],
                handlerWrappers: [
                    new ConnectionTransactionWrapper($connectionSimulator),
                ],
                commandHandlers: [
                    new FailingCreateNewsArticleCommandHandler(),
                ],
                responseConstructors: [
                    new EmptyJsonResponseConstructor(),
                ],
            ),
            $this->defaultRouteConfigurationBuilder,
        );

        $content = [
            'userId' => (string) UserId::generateRandom(),
            'title' => 'New feature released',
            'content' => '<p>We just released <strong>a new feature</strong> <em>but this em is not allowed</em></p>',
            'isPublished' => false,
        ];

        $request = new Request(content: json_encode($content, JSON_THROW_ON_ERROR));
        $routePayload = RoutePayload::generatePayload(
            dtoClass: CreateNewsArticleCommand::class,
            handlerClass: FailingCreateNewsArticleCommandHandler::class,
            handlerWrapperClasses: [
                ConnectionTransactionWrapper::class => null,
            ],
        );

        // -- Act
        $controller->handle($request, $routePayload);
    }
}
