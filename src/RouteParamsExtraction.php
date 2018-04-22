<?php
declare(strict_types=1);

namespace Lcobucci\Chimera\Routing;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class RouteParamsExtraction implements MiddlewareInterface
{
    /**
     * @var RouteParamsExtractor
     */
    private $extractor;

    public function __construct(RouteParamsExtractor $extractor)
    {
        $this->extractor = $extractor;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $handler->handle(
            $request->withAttribute(__CLASS__, $this->extractor->getParams($request))
        );
    }
}
