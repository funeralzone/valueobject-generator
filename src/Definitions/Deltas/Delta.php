<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Deltas;

use Funeralzone\ValueObjectGenerator\Definitions\Location;
use Funeralzone\ValueObjectGenerator\Definitions\Models\ModelPayload;

final class Delta
{
    private $location;
    private $definitionName;
    private $payload;
    private $subDeltas;
    private $createable;

    public function __construct(
        Location $deltaLocation,
        string $definitionName,
        ModelPayload $payload,
        DeltaPayload $subDeltas,
        bool $createable
    ) {
        $this->location = $deltaLocation;
        $this->definitionName = $definitionName;
        $this->payload = $payload;
        $this->subDeltas = $subDeltas;
        $this->createable = $createable;
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

    public function subDeltas(): DeltaPayload
    {
        return $this->subDeltas;
    }

    public function createable(): bool
    {
        return $this->createable;
    }
}
