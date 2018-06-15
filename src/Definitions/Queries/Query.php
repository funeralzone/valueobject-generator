<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Queries;

use Funeralzone\ValueObjectGenerator\Definitions\Location;
use Funeralzone\ValueObjectGenerator\Definitions\Models\ModelPayload;

final class Query
{
    private $location;
    private $definitionName;
    private $payload;

    public function __construct(
        Location $location,
        string $definitionName,
        ModelPayload $payload
    ) {
        $this->location = $location;
        $this->definitionName = $definitionName;
        $this->payload = $payload;
    }

    public function location(): Location
    {
        return $this->location;
    }

    public function definitionName(): string
    {
        return $this->definitionName;
    }

    public function payload(): ModelPayload
    {
        return $this->payload;
    }
}
