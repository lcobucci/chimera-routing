<?php
declare(strict_types=1);

namespace Lcobucci\Chimera\Routing;

interface Attributes
{
    public const ASYNCHRONOUS      = 'chimera.async_process';
    public const PROCESSED         = 'chimera.request_processed';
    public const QUERY_RESULT      = 'chimera.query_result';
    public const GENERATED_ID      = 'chimera.generated_id';
    public const RESOURCE_LOCATION = 'chimera.resource_location';
}
