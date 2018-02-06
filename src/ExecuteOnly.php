<?php
declare(strict_types=1);

namespace Lcobucci\Chimera\Routing;

use Lcobucci\Chimera\CommandBus;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ExecuteOnly implements MiddlewareInterface
{
    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var string
     */
    private $command;

    public function __construct(CommandBus $commandBus, string $command)
    {
        $this->commandBus = $commandBus;
        $this->command    = $command;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->commandBus->handle($this->command, $request);

        return $handler->handle(
            $request->withAttribute(Attributes::PROCESSED, true)
        );
    }
}
