<?php
declare(strict_types=1);

namespace Lcobucci\Chimera\Routing;

use Lcobucci\Chimera\CommandBus;
use Lcobucci\Chimera\QueryBus;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class CreateAndFetch implements MiddlewareInterface
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

    /**
     * @var callable
     */
    private $idGenerator;

    /**
     * @var string
     */
    private $routeName;

    public function __construct(
        CommandBus $commandBus,
        QueryBus $queryBus,
        string $command,
        string $query,
        callable $idGenerator,
        string $routeName
    ) {
        $this->commandBus  = $commandBus;
        $this->queryBus    = $queryBus;
        $this->command     = $command;
        $this->query       = $query;
        $this->idGenerator = $idGenerator;
        $this->routeName   = $routeName;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $request = $request->withAttribute(Attributes::GENERATED_ID, ($this->idGenerator)())
                           ->withAttribute(Attributes::RESOURCE_LOCATION, $this->routeName);

        $this->commandBus->handle($this->command, $request);

        $result = $this->queryBus->handle($this->query, $request);

        $request = $request->withAttribute(Attributes::QUERY_RESULT, $result)
                           ->withAttribute(Attributes::PROCESSED, true);

        return $handler->handle($request);
    }
}
