<?php
declare(strict_types=1);

namespace Lcobucci\Chimera\Routing\Tests;

use Lcobucci\Chimera\Routing\CreateOnly;
use Zend\Diactoros\ServerRequest;

final class CreateOnlyTest extends RoutingTestCase
{
    private const COMMAND = 'Testing';

    /**
     * @test
     *
     * @covers \Lcobucci\Chimera\Routing\CreateOnly
     */
    public function processShouldModifyTheRequestHandleTheCommandAndCallTheRequestHandler(): void
    {
        $request        = new ServerRequest();
        $handledRequest = $this->appendCreationData($request);

        $middleware = new CreateOnly(
            $this->createCommandBus($handledRequest, self::COMMAND),
            self::COMMAND,
            function (): string {
                return self::GENERATED_ID;
            },
            self::ROUTE_NAME
        );

        $this->assertCorrectResponse($middleware, $request, $this->flagAsProcessed($handledRequest));
    }
}
