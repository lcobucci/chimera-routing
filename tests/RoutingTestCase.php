<?php
declare(strict_types=1);

namespace Lcobucci\Chimera\Routing\Tests;

use Lcobucci\Chimera\CommandBus;
use Lcobucci\Chimera\QueryBus;
use Lcobucci\Chimera\Routing\Attributes;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\EmptyResponse;

abstract class RoutingTestCase extends \PHPUnit\Framework\TestCase
{
    protected const GENERATED_ID = '1';
    protected const ROUTE_NAME   = 'testing';
    protected const RESULT       = ['foo' => 'bar'];

    protected function createCommandBus(
        ServerRequestInterface $request,
        string $expectedCommand
    ): CommandBus {
        $bus = $this->createMock(CommandBus::class);

        $bus->expects($this->once())
            ->method('handle')
            ->with($expectedCommand, $this->equalTo($request));

        return $bus;
    }

    protected function createQueryBus(
        ServerRequestInterface $request,
        string $expectedQuery,
        $result
    ): QueryBus {
        $bus = $this->createMock(QueryBus::class);

        $bus->expects($this->once())
            ->method('handle')
            ->with($expectedQuery, $this->equalTo($request))
            ->willReturn($result);

        return $bus;
    }

    protected function flagAsProcessed(ServerRequestInterface $request): ServerRequestInterface
    {
        return $request->withAttribute(Attributes::PROCESSED, true);
    }

    protected function appendCreationData(ServerRequestInterface $request): ServerRequestInterface
    {
        return $request->withAttribute(Attributes::GENERATED_ID, self::GENERATED_ID)
                       ->withAttribute(Attributes::RESOURCE_LOCATION, self::ROUTE_NAME);
    }

    protected function appendResultData(ServerRequestInterface $request): ServerRequestInterface
    {
        return $this->flagAsProcessed($request)
                    ->withAttribute(Attributes::QUERY_RESULT, self::RESULT);
    }

    protected function assertCorrectResponse(
        MiddlewareInterface $middleware,
        ServerRequestInterface $initialRequest,
        ServerRequestInterface $dispatchedRequest
    ): void {
        $response = new EmptyResponse();
        $handler  = $this->createHandler($dispatchedRequest, $response);

        self::assertSame($response, $middleware->process($initialRequest, $handler));
    }

    private function createHandler(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): RequestHandlerInterface {
        $handler = $this->createMock(RequestHandlerInterface::class);

        $handler->expects($this->once())
                ->method('handle')
                ->with($this->equalTo($request))
                ->willReturn($response);

        return $handler;
    }
}
