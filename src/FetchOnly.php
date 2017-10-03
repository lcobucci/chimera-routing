<?php
declare(strict_types=1);

namespace Lcobucci\Chimera\Routing;

use Lcobucci\Chimera\QueryBus;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class FetchOnly implements MiddlewareInterface
{
    /**
     * @var QueryBus
     */
    private $queryBus;

    /**
     * @var string
     */
    private $query;

    public function __construct(
        QueryBus $queryBus,
        string $query
    ) {
        $this->queryBus = $queryBus;
        $this->query    = $query;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $result = $this->queryBus->handle($this->query, $request);

        $request = $request->withAttribute(Attributes::QUERY_RESULT, $result)
                           ->withAttribute(Attributes::PROCESSED, true);

        return $handler->handle($request);
    }
}
