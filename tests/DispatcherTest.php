<?php
declare(strict_types=1);

namespace Lcobucci\Chimera\Routing\Tests;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Lcobucci\Chimera\Routing\Attributes;
use Lcobucci\Chimera\Routing\Dispatcher;
use Lcobucci\Chimera\Routing\ResponseGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\ServerRequest;

final class DispatcherTest extends RoutingTestCase
{
    /**
     * @test
     *
     * @covers \Lcobucci\Chimera\Routing\Dispatcher
     */
    public function processDelegatesTheRequestIfItHasNotBeenProcessedAlready(): void
    {
        $request = new ServerRequest();
        $request = $request->withAttribute(Attributes::PROCESSED, false);

        $dispatcher = new Dispatcher($this->createMock(ResponseGenerator::class));

        $this->assertCorrectResponse($dispatcher, $request, $request);
    }

    /**
     * @test
     *
     * @covers \Lcobucci\Chimera\Routing\Dispatcher
     */
    public function processReturnsTheResponseIfTheRequestHadBeenHandled(): void
    {
        $request = new ServerRequest();
        $request = $request->withAttribute(Attributes::PROCESSED, true);

        $expectedResponse = new EmptyResponse();

        $generator = $this->createMock(ResponseGenerator::class);
        $generator->expects($this->once())
            ->method('generateResponse')
            ->with($request, $request->getAttribute(Attributes::QUERY_RESULT))
            ->willReturn($expectedResponse);

        $dispatcher = new Dispatcher($generator);

        self::assertSame(
            $expectedResponse,
            $dispatcher->process(
                $request,
                $this->createMock(DelegateInterface::class)
            )
        );
    }
}
