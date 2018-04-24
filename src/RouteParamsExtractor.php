<?php
declare(strict_types=1);

namespace Lcobucci\Chimera\Routing;

use Psr\Http\Message\ServerRequestInterface;

interface RouteParamsExtractor
{
    /**
     * Returns the parameters identified in the matched route
     *
     * @return string[]
     */
    public function getParams(ServerRequestInterface $request): array;
}
