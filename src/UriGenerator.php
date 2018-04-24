<?php
declare(strict_types=1);

namespace Lcobucci\Chimera\Routing;

use Psr\Http\Message\ServerRequestInterface;

interface UriGenerator
{
    /**
     * Generates the relative path for the given route
     *
     * @param string[] $substitutions
     */
    public function generateRelativePath(
        ServerRequestInterface $request,
        string $routeName,
        array $substitutions = []
    ): string;
}
