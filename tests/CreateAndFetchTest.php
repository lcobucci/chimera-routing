<?php
declare(strict_types=1);

namespace Lcobucci\Chimera\Routing\Tests;

use Lcobucci\Chimera\Routing\CreateAndFetch;
use Zend\Diactoros\ServerRequest;

final class CreateAndFetchTest extends RoutingTestCase
{
    private const COMMAND = 'Testing';
    private const QUERY   = 'Testing2';

    /**
     * @test
     *
     * @covers \Lcobucci\Chimera\Routing\CreateAndFetch
     */
    public function processShouldModifyTheRequestHandleTheCommandAndCallTheRequestHandler(): void
    {
        $request        = new ServerRequest();
        $handledRequest = $this->appendCreationData($request);

        $middleware = new CreateAndFetch(
            $this->createCommandBus($handledRequest, self::COMMAND),
            $this->createQueryBus($handledRequest, self::QUERY, self::RESULT),
            self::COMMAND,
            self::QUERY,
            function (): string {
                return self::GENERATED_ID;
            },
            self::ROUTE_NAME
        );

        $this->assertCorrectResponse($middleware, $request, $this->appendResultData($handledRequest));
    }
}
