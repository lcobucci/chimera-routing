<?php
declare(strict_types=1);

namespace Lcobucci\Chimera\Routing\Tests;

use Lcobucci\Chimera\Routing\Attributes;
use Lcobucci\Chimera\Routing\CreateOnly;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\ServerRequest;

final class CreateOnlyTest extends RoutingTestCase
{
    private const COMMAND      = 'Testing';
    private const ROUTE_NAME   = 'testing';
    private const GENERATED_ID = '1';

    /**
     * @test
     *
     * @covers \Lcobucci\Chimera\Routing\CreateOnly
     */
    public function processShouldModifyTheRequestHandleTheCommandAndCallTheRequestHandler(): void
    {
        $response = new EmptyResponse();
        $request  = new ServerRequest();

        $expectedRequest = $request->withAttribute(Attributes::GENERATED_ID, self::GENERATED_ID)
                                   ->withAttribute(Attributes::RESOURCE_LOCATION, self::ROUTE_NAME);

        $processedRequest = $expectedRequest->withAttribute(Attributes::PROCESSED, true);

        $commandBus = $this->createCommandBus($expectedRequest, self::COMMAND);
        $handler    = $this->createDelegate($processedRequest, $response);

        $middleware = new CreateOnly(
            $commandBus,
            self::COMMAND,
            function (): string {
                return self::GENERATED_ID;
            },
            self::ROUTE_NAME
        );

        self::assertSame($response, $middleware->process($request, $handler));
    }
}
