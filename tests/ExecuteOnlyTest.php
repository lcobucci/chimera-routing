<?php
declare(strict_types=1);

namespace Lcobucci\Chimera\Routing\Tests;

use Lcobucci\Chimera\Routing\ExecuteOnly;
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
        $request = new ServerRequest();

        $middleware = new ExecuteOnly(
            $this->createCommandBus($request, self::COMMAND),
            self::COMMAND
        );

        $this->assertCorrectResponse($middleware, $request, $this->flagAsProcessed($request));
    }
}
