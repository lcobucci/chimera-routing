<?php
declare(strict_types=1);

namespace Lcobucci\Chimera\Routing;

use Lcobucci\Chimera\Input;
use Psr\Http\Message\ServerRequestInterface;
use function assert;
use function is_array;

/**
 * Implementation for input data that comes from HTTP
 */
final class HttpRequest implements Input
{
    /**
     * @var ServerRequestInterface
     */
    private $request;

    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttribute(string $name, $default = null)
    {
        return $this->request->getAttribute($name, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function getData(): array
    {
        $data = $this->request->getParsedBody() ?? [];

        return (array) $data + $this->getContext();
    }

    /**
     * @return string[]
     */
    private function getContext(): array
    {
        $routeParams = $this->request->getAttribute(RouteParamsExtraction::class, []);
        assert(is_array($routeParams));

        return $routeParams + $this->request->getQueryParams();
    }
}
