<?php
declare(strict_types=1);

namespace Lcobucci\Chimera\Routing;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;

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

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        return $this->processor->process(
            $request->withAttribute(Attributes::ASYNCHRONOUS, true),
            $delegate
        );
    }
}
