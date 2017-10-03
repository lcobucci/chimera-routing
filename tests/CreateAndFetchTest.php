<?php
declare(strict_types=1);

namespace Lcobucci\Chimera\Routing\Tests;

use Lcobucci\Chimera\Routing\Attributes;
use Lcobucci\Chimera\Routing\CreateAndFetch;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\ServerRequest;

final class CreateAndFetchTest extends RoutingTestCase
{
    private const COMMAND      = 'Testing';
    private const QUERY        = 'Testing2';
    private const ROUTE_NAME   = 'testing';
    private const GENERATED_ID = '1';
    private const RESULT       = ['foo' => 'bar'];

    /**
     * @test
     *
     * @covers \Lcobucci\Chimera\Routing\CreateAndFetch
     */
    public function processShouldModifyTheRequestHandleTheCommandAndCallTheRequestHandler(): void
    {
        $response = new EmptyResponse();
        $request  = new ServerRequest();

        $handledRequest = $request->withAttribute(Attributes::GENERATED_ID, self::GENERATED_ID)
                                  ->withAttribute(Attributes::RESOURCE_LOCATION, self::ROUTE_NAME);

        $processedRequest = $handledRequest->withAttribute(Attributes::QUERY_RESULT, self::RESULT)
                                           ->withAttribute(Attributes::PROCESSED, true);

        $commandBus = $this->createCommandBus($handledRequest, self::COMMAND);
        $queryBus   = $this->createQueryBus($handledRequest, self::QUERY, self::RESULT);
        $handler    = $this->createDelegate($processedRequest, $response);

        $middleware = new CreateAndFetch(
            $commandBus,
            $queryBus,
            self::COMMAND,
            self::QUERY,
            function (): string {
                return self::GENERATED_ID;
            },
            self::ROUTE_NAME
        );

        self::assertSame($response, $middleware->process($request, $handler));
    }
}
