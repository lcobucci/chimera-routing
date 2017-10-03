<?php
declare(strict_types=1);

namespace Lcobucci\Chimera\Routing;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Lcobucci\Chimera\QueryBus;
use Psr\Http\Message\ServerRequestInterface;

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

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $result = $this->queryBus->handle($this->query, $request);

        $request = $request->withAttribute(Attributes::QUERY_RESULT, $result)
                           ->withAttribute(Attributes::PROCESSED, true);

        return $delegate->process($request);
    }
}
