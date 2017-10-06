<?php
declare(strict_types=1);

namespace Lcobucci\Chimera\Routing;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ResponseGenerator
{
    public function generateResponse(ServerRequestInterface $request, $result = null): ResponseInterface;
}
