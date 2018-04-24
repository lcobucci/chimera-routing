<?php
declare(strict_types=1);

namespace Lcobucci\Chimera\Routing\Handler;

use Fig\Http\Message\StatusCodeInterface;
use Lcobucci\Chimera\ExecuteCommand;
use Lcobucci\Chimera\ExecuteQuery;
use Lcobucci\Chimera\IdentifierGenerator;
use Lcobucci\Chimera\Routing\HttpRequest;
use Lcobucci\Chimera\Routing\UriGenerator;
use Lcobucci\ContentNegotiation\UnformattedResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function assert;

/**
 * Generates an identifier, executes a command, and then a query. Returns the
 * query result in an unformatted response with a link to the new resource
 */
final class CreateAndFetch implements RequestHandlerInterface
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

    /**
     * @var string
     */
    private $routeName;

    /**
     * @var UriGenerator
     */
    private $uriGenerator;

    /**
     * @var IdentifierGenerator
     */
    private $identifierGenerator;

    public function __construct(
        ExecuteCommand $writeAction,
        ExecuteQuery $readAction,
        callable $responseFactory,
        string $routeName,
        UriGenerator $uriGenerator,
        IdentifierGenerator $identifierGenerator
    ) {
        $this->writeAction         = $writeAction;
        $this->readAction          = $readAction;
        $this->responseFactory     = $responseFactory;
        $this->routeName           = $routeName;
        $this->uriGenerator        = $uriGenerator;
        $this->identifierGenerator = $identifierGenerator;
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

        $input = new HttpRequest($request);

        $this->writeAction->execute($input);

        return new UnformattedResponse(
            $this->generateResponse($request),
            $this->readAction->fetch($input),
            [ExecuteQuery::class => $this->readAction->getQuery()]
        );
    }

    private function generateResponse(ServerRequestInterface $request): ResponseInterface
    {
        $response = ($this->responseFactory)();
        assert($response instanceof ResponseInterface);

        $resourceUri = $this->uriGenerator->generateRelativePath($request, $this->routeName);

        return $response->withStatus(StatusCodeInterface::STATUS_CREATED)
                        ->withAddedHeader('Location', $resourceUri);
    }
}
