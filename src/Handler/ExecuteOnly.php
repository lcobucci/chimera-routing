<?php
declare(strict_types=1);

namespace Lcobucci\Chimera\Routing\Handler;

use Lcobucci\Chimera\ExecuteCommand;
use Lcobucci\Chimera\Routing\HttpRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function assert;

/**
 * Executes or schedule a command, returning a response with empty body.
 */
final class ExecuteOnly implements RequestHandlerInterface
{
    /**
     * @var ExecuteCommand
     */
    private $action;

    /**
     * @var callable
     */
    private $responseFactory;

    /**
     * @var int
     */
    private $statusCode;

    public function __construct(ExecuteCommand $action, callable $responseFactory, int $statusCode)
    {
        $this->action          = $action;
        $this->responseFactory = $responseFactory;
        $this->statusCode      = $statusCode;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->action->execute(new HttpRequest($request));

        $response = ($this->responseFactory)();
        assert($response instanceof ResponseInterface);

        return $response->withStatus($this->statusCode);
    }
}
