<?php
declare(strict_types=1);

namespace Lcobucci\Chimera\Routing\Tests;

use Lcobucci\Chimera\Routing\RouteParamsExtraction;
use Lcobucci\Chimera\Routing\RouteParamsExtractor;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

/**
 * @coversDefaultClass \Lcobucci\Chimera\Routing\RouteParamsExtraction
 */
final class RouteParamsExtractionTest extends TestCase
{
    /**
     * @test
     *
     * @covers ::__construct()
     * @covers ::process()
     */
    public function processShouldUseExtractorAndAddResultAsRequestAttribute(): void
    {
        $request  = new ServerRequest();
        $response = new Response();

        $extractor = $this->createMock(RouteParamsExtractor::class);
        $handler   = $this->createMock(RequestHandlerInterface::class);

        $extractor->method('getParams')
                  ->willReturn(['id' => '123']);

        $handler->expects(self::once())
                ->method('handle')
                ->with($request->withAttribute(RouteParamsExtraction::class, ['id' => '123']))
                ->willReturn($response);

        $middleware = new RouteParamsExtraction($extractor);

        self::assertSame($response, $middleware->process($request, $handler));
    }
}
