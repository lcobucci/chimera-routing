<?php
declare(strict_types=1);

namespace Lcobucci\Chimera\Routing\Tests\Handler;

use Fig\Http\Message\StatusCodeInterface;
use Lcobucci\Chimera\ExecuteQuery;
use Lcobucci\Chimera\MessageCreator;
use Lcobucci\Chimera\Routing\Handler\FetchOnly;
use Lcobucci\Chimera\ServiceBus;
use Lcobucci\ContentNegotiation\UnformattedResponse;
use Middlewares\Utils\Factory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zend\Diactoros\ServerRequest;
use function assert;

/**
 * @coversDefaultClass \Lcobucci\Chimera\Routing\Handler\FetchOnly
 */
final class FetchOnlyTest extends TestCase
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
    public function handleShouldExecuteTheQueryAndReturnItsContent(): void
    {
        $handler = new FetchOnly(
            new ExecuteQuery($this->bus, $this->creator, 'query'),
            [Factory::class, 'createResponse']
        );

        $query = (object) ['a' => 'b'];

        $this->creator->expects(self::once())
                      ->method('create')
                      ->willReturn($query);

        $this->bus->expects(self::once())
                  ->method('handle')
                  ->with($query)
                  ->willReturn('result');

        $response = $handler->handle(new ServerRequest());

        self::assertInstanceOf(UnformattedResponse::class, $response);
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        assert($response instanceof UnformattedResponse);
        self::assertSame([ExecuteQuery::class => 'query'], $response->getAttributes());
        self::assertSame('result', $response->getUnformattedContent());
    }
}
