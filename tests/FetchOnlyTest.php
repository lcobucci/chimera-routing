<?php
declare(strict_types=1);

namespace Lcobucci\Chimera\Routing\Tests;

use Lcobucci\Chimera\Routing\Attributes;
use Lcobucci\Chimera\Routing\FetchOnly;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\ServerRequest;

final class FetchOnlyTest extends RoutingTestCase
{
    private const QUERY  = 'Testing';
    private const RESULT = ['foo' => 'bar'];

    /**
     * @test
     *
     * @covers \Lcobucci\Chimera\Routing\FetchOnly
     */
    public function processShouldHandleTheQueryAndSendTheResultToTheNextRequestHandler(): void
    {
        $request  = new ServerRequest();
        $response = new EmptyResponse();

        $expectedRequest = $request->withAttribute(Attributes::QUERY_RESULT, self::RESULT)
                                   ->withAttribute(Attributes::PROCESSED, true);

        $queryBus = $this->createQueryBus($request, self::QUERY, self::RESULT);
        $handler  = $this->createDelegate($expectedRequest, $response);

        $middleware = new FetchOnly($queryBus, self::QUERY);

        self::assertSame($response, $middleware->process($request, $handler));
    }
}
