<?php
declare(strict_types=1);

namespace Lcobucci\Chimera\Routing\Tests;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Lcobucci\Chimera\Routing\Async;
use Lcobucci\Chimera\Routing\Attributes;
use Lcobucci\Chimera\Routing\Tests\RoutingTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\ServerRequest;

final class AsyncTest extends RoutingTestCase
{
    /**
     * @test
     *
     * @covers \Lcobucci\Chimera\Routing\Async
     */
    public function processShouldAddAsyncAttributeAndForwardRequestToInternalProcessor(): void
    {
        $request = new ServerRequest();

        $middleware = new Async(
            new class implements MiddlewareInterface
            {
                public function process(ServerRequestInterface $request, DelegateInterface $delegate)
                {
                    return $delegate->process($request);
                }
            }
        );

        $this->assertCorrectResponse($middleware, $request, $request->withAttribute(Attributes::ASYNCHRONOUS, true));
    }
}
