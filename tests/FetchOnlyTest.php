<?php
declare(strict_types=1);

namespace Lcobucci\Chimera\Routing\Tests;

use Lcobucci\Chimera\Routing\FetchOnly;
use Zend\Diactoros\ServerRequest;

final class FetchOnlyTest extends RoutingTestCase
{
    private const QUERY = 'Testing';

    /**
     * @test
     *
     * @covers \Lcobucci\Chimera\Routing\FetchOnly
     */
    public function processShouldHandleTheQueryAndSendTheResultToTheNextRequestHandler(): void
    {
        $request = new ServerRequest();

        $middleware = new FetchOnly(
            $this->createQueryBus($request, self::QUERY, self::RESULT),
            self::QUERY
        );

        $this->assertCorrectResponse($middleware, $request, $this->appendResultData($request));
    }
}
