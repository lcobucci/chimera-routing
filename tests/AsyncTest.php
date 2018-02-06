<?php
declare(strict_types=1);

namespace Lcobucci\Chimera\Routing\Tests;

use Lcobucci\Chimera\Routing\Async;
use Lcobucci\Chimera\Routing\Attributes;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
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
                public function process(
                    ServerRequestInterface $request,
                    RequestHandlerInterface $delegate
                ): ResponseInterface {
                    return $delegate->handle($request);
                }
            }
        );

        $this->assertCorrectResponse($middleware, $request, $request->withAttribute(Attributes::ASYNCHRONOUS, true));
    }
}
