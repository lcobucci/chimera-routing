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
        $request    = new ServerRequest();
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
        $generator = $this->createMock(ResponseGenerator::class);
        $request   = $this->appendResultData(new ServerRequest());
        $response  = new EmptyResponse();

        $generator->expects($this->once())
                  ->method('generateResponse')
                  ->with($request, self::RESULT)
                  ->willReturn($response);

        $dispatcher = new Dispatcher($generator);

        self::assertSame(
            $response,
            $dispatcher->process($request, $this->createMock(DelegateInterface::class))
        );
    }
}
