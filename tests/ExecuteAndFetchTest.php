<?php
declare(strict_types=1);

namespace Lcobucci\Chimera\Routing\Tests;

use Lcobucci\Chimera\Routing\ExecuteAndFetch;
use Zend\Diactoros\ServerRequest;

final class ExecuteAndFetchTest extends RoutingTestCase
{
    private const COMMAND = 'Testing';
    private const QUERY   = 'Testing2';

    /**
     * @test
     *
     * @covers \Lcobucci\Chimera\Routing\ExecuteAndFetch
     */
    public function processShouldHandleTheCommandAndCallTheRequestHandler(): void
    {
        $request = new ServerRequest();

        $middleware = new ExecuteAndFetch(
            $this->createCommandBus($request, self::COMMAND),
            $this->createQueryBus($request, self::QUERY, self::RESULT),
            self::COMMAND,
            self::QUERY
        );

        $this->assertCorrectResponse($middleware, $request, $this->appendResultData($request));
    }
}
