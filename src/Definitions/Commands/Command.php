<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Commands;

use Funeralzone\ValueObjectGenerator\Definitions\Deltas\DeltaPayload;
use Funeralzone\ValueObjectGenerator\Definitions\Location;
use Funeralzone\ValueObjectGenerator\Definitions\Models\ModelPayload;

final class Command
{
    private $location;
    private $definitionName;
    private $payload;
    private $deltas;

    public function __construct(
        Location $location,
        string $definitionName,
        ModelPayload $payload,
        DeltaPayload $deltas
    ) {
        $this->location = $location;
        $this->definitionName = $definitionName;
        $this->payload = $payload;
        $this->deltas = $deltas;
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

    public function deltas(): DeltaPayload
    {
        return $this->deltas;
    }
}
