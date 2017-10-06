<?php
declare(strict_types=1);

namespace Lcobucci\Chimera\Routing;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;

final class Dispatcher implements MiddlewareInterface
{
    /**
     * @var ResponseGenerator
     */
    private $generator;

    public function __construct(ResponseGenerator $generator)
    {
        $this->generator = $generator;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $processed = $request->getAttribute(Attributes::PROCESSED, false);

        if ($processed === false) {
            return $delegate->process($request);
        }

        return $this->generator->generateResponse(
            $request,
            $request->getAttribute(Attributes::QUERY_RESULT)
        );
    }
}
