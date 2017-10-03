<?php
declare(strict_types=1);

namespace Lcobucci\Chimera\Routing;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Lcobucci\Chimera\CommandBus;
use Psr\Http\Message\ServerRequestInterface;

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

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $this->commandBus->handle($this->command, $request);

        return $delegate->process(
            $request->withAttribute(Attributes::PROCESSED, true)
        );
    }
}
