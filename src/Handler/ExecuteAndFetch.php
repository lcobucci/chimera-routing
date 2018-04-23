<?php
declare(strict_types=1);

namespace Lcobucci\Chimera\Routing\Handler;

use Lcobucci\Chimera\ExecuteCommand;
use Lcobucci\Chimera\ExecuteQuery;
use Lcobucci\Chimera\Routing\HttpRequest;
use Lcobucci\ContentNegotiation\UnformattedResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function assert;

/**
 * Executes a command and then a query, returning its result in an unformatted
 * response.
 */
final class ExecuteAndFetch implements RequestHandlerInterface
{
    /**
     * @var ExecuteCommand
     */
    private $writeAction;

    /**
     * @var ExecuteQuery
     */
    private $readAction;

    /**
     * @var callable
     */
    private $responseFactory;

    public function __construct(
        ExecuteCommand $writeAction,
        ExecuteQuery $readAction,
        callable $responseFactory
    ) {
        $this->writeAction     = $writeAction;
        $this->readAction      = $readAction;
        $this->responseFactory = $responseFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $input = new HttpRequest($request);

        $this->writeAction->execute($input);

        $response = ($this->responseFactory)();
        assert($response instanceof ResponseInterface);

        return new UnformattedResponse(
            $response,
            $this->readAction->fetch($input),
            [ExecuteQuery::class => $this->readAction->getQuery()]
        );
    }
}
