<?php
declare(strict_types=1);

namespace Lcobucci\Chimera\Routing;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class Async implements MiddlewareInterface
{
    /**
     * @var MiddlewareInterface
     */
    private $processor;

    public function __construct(MiddlewareInterface $processor)
    {
        $this->processor = $processor;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->processor->process(
            $request->withAttribute(Attributes::ASYNCHRONOUS, true),
            $handler
        );
    }
}
