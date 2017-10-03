<?php
declare(strict_types=1);

namespace Lcobucci\Chimera\Routing\Tests;

use Lcobucci\Chimera\Routing\Attributes;
use Lcobucci\Chimera\Routing\ExecuteOnly;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\ServerRequest;

final class ExecuteOnlyTest extends RoutingTestCase
{
    private const COMMAND = 'Testing';

    /**
     * @test
     *
     * @covers \Lcobucci\Chimera\Routing\ExecuteOnly
     */
    public function processShouldHandleTheCommandAndCallTheRequestHandler(): void
    {
        $request  = new ServerRequest();
        $response = new EmptyResponse();

        $expectedRequest = $request->withAttribute(Attributes::PROCESSED, true);

        $commandBus = $this->createCommandBus($request, self::COMMAND);
        $handler    = $this->createDelegate($expectedRequest, $response);

        $middleware = new ExecuteOnly($commandBus, self::COMMAND);

        self::assertSame($response, $middleware->process($request, $handler));
    }
}
