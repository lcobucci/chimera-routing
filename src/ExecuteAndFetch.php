<?php
declare(strict_types=1);

namespace Lcobucci\Chimera\Routing;

use Lcobucci\Chimera\CommandBus;
use Lcobucci\Chimera\QueryBus;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ExecuteAndFetch implements MiddlewareInterface
{
    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var QueryBus
     */
    private $queryBus;

    /**
     * @var string
     */
    private $command;

    /**
     * @var string
     */
    private $query;

    public function __construct(
        CommandBus $commandBus,
        QueryBus $queryBus,
        string $command,
        string $query
    ) {
        $this->commandBus = $commandBus;
        $this->queryBus   = $queryBus;
        $this->command    = $command;
        $this->query      = $query;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->commandBus->handle($this->command, $request);

        $result = $this->queryBus->handle($this->query, $request);

        $request = $request->withAttribute(Attributes::QUERY_RESULT, $result)
                           ->withAttribute(Attributes::PROCESSED, true);

        return $handler->handle($request);
    }
}
