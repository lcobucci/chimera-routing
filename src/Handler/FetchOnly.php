<?php
declare(strict_types=1);

namespace Lcobucci\Chimera\Routing\Handler;

use Lcobucci\Chimera\ExecuteQuery;
use Lcobucci\Chimera\Routing\HttpRequest;
use Lcobucci\ContentNegotiation\UnformattedResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function assert;

/**
 * Executes a query, returning an unformatted response with its result.
 */
final class FetchOnly implements RequestHandlerInterface
{
    /**
     * @var ExecuteQuery
     */
    private $action;

    /**
     * @var callable
     */
    private $responseFactory;

    public function __construct(ExecuteQuery $action, callable $responseFactory)
    {
        $this->action          = $action;
        $this->responseFactory = $responseFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = ($this->responseFactory)();
        assert($response instanceof ResponseInterface);

        return new UnformattedResponse(
            $response,
            $this->action->fetch(new HttpRequest($request)),
            [ExecuteQuery::class => $this->action->getQuery()]
        );
    }
}
