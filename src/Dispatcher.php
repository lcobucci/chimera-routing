<?php
declare(strict_types=1);

namespace Lcobucci\Chimera\Routing;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

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

    public function process(ServerRequestInterface $request, RequestHandlerInterface $delegate): ResponseInterface
    {
        $processed = $request->getAttribute(Attributes::PROCESSED, false);

        if ($processed === false) {
            return $delegate->handle($request);
        }

        return $this->generator->generateResponse(
            $request,
            $request->getAttribute(Attributes::QUERY_RESULT)
        );
    }
}
