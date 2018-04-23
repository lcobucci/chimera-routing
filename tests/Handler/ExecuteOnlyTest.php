<?php
declare(strict_types=1);

namespace Lcobucci\Chimera\Routing\Tests\Handler;

use Fig\Http\Message\StatusCodeInterface;
use Lcobucci\Chimera\ExecuteCommand;
use Lcobucci\Chimera\MessageCreator;
use Lcobucci\Chimera\Routing\Handler\ExecuteOnly;
use Lcobucci\Chimera\ServiceBus;
use Lcobucci\ContentNegotiation\UnformattedResponse;
use Middlewares\Utils\Factory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zend\Diactoros\ServerRequest;

/**
 * @coversDefaultClass \Lcobucci\Chimera\Routing\Handler\ExecuteOnly
 */
final class ExecuteOnlyTest extends TestCase
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
        $handler = new ExecuteOnly(
            new ExecuteCommand($this->bus, $this->creator, 'command'),
            [Factory::class, 'createResponse'],
            StatusCodeInterface::STATUS_NO_CONTENT
        );

        $command = (object) ['a' => 'b'];

        $this->creator->expects(self::once())
                      ->method('create')
                      ->willReturn($command);

        $this->bus->expects(self::once())
                  ->method('handle')
                  ->with($command);

        $response = $handler->handle(new ServerRequest());

        self::assertNotInstanceOf(UnformattedResponse::class, $response);
        self::assertSame(StatusCodeInterface::STATUS_NO_CONTENT, $response->getStatusCode());
    }
}
