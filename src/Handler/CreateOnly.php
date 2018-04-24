<?php
declare(strict_types=1);

namespace Lcobucci\Chimera\Routing\Handler;

use Lcobucci\Chimera\ExecuteCommand;
use Lcobucci\Chimera\IdentifierGenerator;
use Lcobucci\Chimera\Routing\HttpRequest;
use Lcobucci\Chimera\Routing\UriGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function assert;

/**
 * Generates an identifier and executes (or schedule) a command, returning
 * an empty response with a link to the new resource
 */
final class CreateOnly implements RequestHandlerInterface
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
     * @var string
     */
    private $routeName;

    /**
     * @var UriGenerator
     */
    private $uriGenerator;

    /**
     * @var int
     */
    private $statusCode;
    /**
     * @var IdentifierGenerator
     */
    private $identifierGenerator;

    public function __construct(
        ExecuteCommand $action,
        callable $responseFactory,
        string $routeName,
        UriGenerator $uriGenerator,
        IdentifierGenerator $identifierGenerator,
        int $statusCode
    ) {
        $this->action              = $action;
        $this->responseFactory     = $responseFactory;
        $this->routeName           = $routeName;
        $this->uriGenerator        = $uriGenerator;
        $this->identifierGenerator = $identifierGenerator;
        $this->statusCode          = $statusCode;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $request = $request->withAttribute(
            IdentifierGenerator::class,
            $this->identifierGenerator->generate()
        );

        $this->action->execute(new HttpRequest($request));

        return $this->generateResponse($request);
    }

    private function generateResponse(ServerRequestInterface $request): ResponseInterface
    {
        $response = ($this->responseFactory)();
        assert($response instanceof ResponseInterface);

        $resourceUri = $this->uriGenerator->generateRelativePath($request, $this->routeName);

        return $response->withStatus($this->statusCode)
                        ->withAddedHeader('Location', $resourceUri);
    }
}
