<?php
declare(strict_types=1);

namespace Lcobucci\Chimera\Routing\Tests\Handler;

use Fig\Http\Message\StatusCodeInterface;
use Lcobucci\Chimera\ExecuteCommand;
use Lcobucci\Chimera\ExecuteQuery;
use Lcobucci\Chimera\MessageCreator;
use Lcobucci\Chimera\Routing\Handler\ExecuteAndFetch;
use Lcobucci\Chimera\ServiceBus;
use Lcobucci\ContentNegotiation\UnformattedResponse;
use Middlewares\Utils\Factory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\ServerRequest;

/**
 * @coversDefaultClass \Lcobucci\Chimera\Routing\Handler\ExecuteAndFetch
 */
final class ExecuteAndFetchTest extends TestCase
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
     * @before
     */
    public function createDependencies(): void
    {
        $this->bus     = $this->createMock(ServiceBus::class);
        $this->creator = $this->createMock(MessageCreator::class);
    }

    /**
     * @test
     *
     * @covers ::__construct()
     * @covers ::handle()
     *
     * @uses \Lcobucci\Chimera\Routing\HttpRequest
     */
    public function handleShouldExecuteTheCommandAndReturnAnEmptyResponse(): void
    {
        $handler = new ExecuteAndFetch(
            new ExecuteCommand($this->bus, $this->creator, 'command'),
            new ExecuteQuery($this->bus, $this->creator, 'query'),
            [Factory::class, 'createResponse']
        );

        $command = (object) ['a' => 'b'];
        $query   = (object) ['c' => 'd'];

        $this->creator->expects(self::exactly(2))
                      ->method('create')
                      ->willReturn($command, $query);

        $this->bus->expects(self::exactly(2))
                  ->method('handle')
                  ->withConsecutive([$command], [$query])
                  ->willReturn(null, 'result');

        /** @var ResponseInterface|UnformattedResponse $response */
        $response = $handler->handle(new ServerRequest());

        self::assertInstanceOf(UnformattedResponse::class, $response);
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
        self::assertSame([ExecuteQuery::class => 'query'], $response->getAttributes());
        self::assertSame('result', $response->getUnformattedContent());
    }
}
