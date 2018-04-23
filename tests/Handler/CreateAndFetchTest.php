<?php
declare(strict_types=1);

namespace Lcobucci\Chimera\Routing\Tests\Handler;

use Fig\Http\Message\StatusCodeInterface;
use Lcobucci\Chimera\ExecuteCommand;
use Lcobucci\Chimera\ExecuteQuery;
use Lcobucci\Chimera\IdentifierGenerator;
use Lcobucci\Chimera\MessageCreator;
use Lcobucci\Chimera\Routing\Handler\CreateAndFetch;
use Lcobucci\Chimera\Routing\UriGenerator;
use Lcobucci\Chimera\ServiceBus;
use Lcobucci\ContentNegotiation\UnformattedResponse;
use Middlewares\Utils\Factory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zend\Diactoros\ServerRequest;
use function assert;

/**
 * @coversDefaultClass \Lcobucci\Chimera\Routing\Handler\CreateAndFetch
 */
final class CreateAndFetchTest extends TestCase
{
    /**
     * @var ServiceBus|MockObject
     */
    private $bus;

    /**
     * @var MessageCreator|MockObject
     */
    private $creator;

    /**
     * @var UriGenerator|MockObject
     */
    private $uriGenerator;

    /**
     * @var IdentifierGenerator|MockObject
     */
    private $idGenerator;

    /**
     * @before
     */
    public function createDependencies(): void
    {
        $this->bus          = $this->createMock(ServiceBus::class);
        $this->creator      = $this->createMock(MessageCreator::class);
        $this->uriGenerator = $this->createMock(UriGenerator::class);
        $this->idGenerator  = $this->createMock(IdentifierGenerator::class);
    }

    /**
     * @test
     *
     * @covers ::__construct()
     * @covers ::handle()
     * @covers ::generateResponse()
     *
     * @uses \Lcobucci\Chimera\Routing\HttpRequest
     */
    public function handleShouldExecuteTheCommandAndReturnAnEmptyResponse(): void
    {
        $handler = new CreateAndFetch(
            new ExecuteCommand($this->bus, $this->creator, 'command'),
            new ExecuteQuery($this->bus, $this->creator, 'query'),
            [Factory::class, 'createResponse'],
            'info',
            $this->uriGenerator,
            $this->idGenerator
        );

        $request = new ServerRequest();
        $command = (object) ['a' => 'b'];
        $query   = (object) ['c' => 'd'];

        $this->creator->expects(self::exactly(2))
                      ->method('create')
                      ->willReturn($command, $query);

        $this->bus->expects(self::exactly(2))
                  ->method('handle')
                  ->withConsecutive([$command], [$query])
                  ->willReturn(null, 'result');

        $this->idGenerator->method('generate')
                          ->willReturn(1);

        $this->uriGenerator->expects(self::once())
                           ->method('generateRelativePath')
                           ->with($request->withAttribute(IdentifierGenerator::class, 1), 'info')
                           ->willReturn('/testing/1');

        $response = $handler->handle($request);

        self::assertInstanceOf(UnformattedResponse::class, $response);
        self::assertSame(StatusCodeInterface::STATUS_CREATED, $response->getStatusCode());
        self::assertSame('/testing/1', $response->getHeaderLine('Location'));

        assert($response instanceof UnformattedResponse);
        self::assertSame([ExecuteQuery::class => 'query'], $response->getAttributes());
        self::assertSame('result', $response->getUnformattedContent());
    }
}
