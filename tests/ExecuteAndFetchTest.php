<?php
declare(strict_types=1);

namespace Lcobucci\Chimera\Routing\Tests;

use Lcobucci\Chimera\Routing\Attributes;
use Lcobucci\Chimera\Routing\ExecuteAndFetch;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\ServerRequest;

final class ExecuteAndFetchTest extends RoutingTestCase
{
    private const COMMAND = 'Testing';
    private const QUERY   = 'Testing2';
    private const RESULT  = ['foo' => 'bar'];

    /**
     * @test
     *
     * @covers \Lcobucci\Chimera\Routing\ExecuteAndFetch
     */
    public function processShouldHandleTheCommandAndCallTheRequestHandler(): void
    {
        $request  = new ServerRequest();
        $response = new EmptyResponse();

        $expectedRequest = $request->withAttribute(Attributes::QUERY_RESULT, self::RESULT)
                                   ->withAttribute(Attributes::PROCESSED, true);

        $commandBus = $this->createCommandBus($request, self::COMMAND);
        $queryBus   = $this->createQueryBus($request, self::QUERY, self::RESULT);
        $handler    = $this->createDelegate($expectedRequest, $response);

        $middleware = new ExecuteAndFetch($commandBus, $queryBus, self::COMMAND, self::QUERY);

        self::assertSame($response, $middleware->process($request, $handler));
    }
}
