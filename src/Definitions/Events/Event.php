<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Events;

use Funeralzone\ValueObjectGenerator\Definitions\Location;
use Funeralzone\ValueObjectGenerator\Definitions\Models\Model;
use Funeralzone\ValueObjectGenerator\Definitions\Models\ModelSet;

final class Event
{
    private $deltaLocation;
    private $commandLocation;
    private $eventLocation;
    private $definitionName;
    private $aggregateIdModel;
    private $commandName;
    private $payload;

    public function __construct(
        Location $deltaLocation,
        Location $commandLocation,
        Location $eventLocation,
        string $definitionName,
        Model $aggregateIdModel,
        string $commandName,
        ModelSet $payload
    ) {
        $this->deltaLocation = $deltaLocation;
        $this->commandLocation = $commandLocation;
        $this->eventLocation = $eventLocation;
        $this->definitionName = $definitionName;
        $this->aggregateIdModel = $aggregateIdModel;
        $this->commandName = $commandName;
        $this->payload = $payload;
    }

    public function deltaLocation(): Location
    {
        return $this->deltaLocation;
    }

    public function commandLocation(): Location
    {
        return $this->commandLocation;
    }

    public function eventLocation(): Location
    {
        return $this->eventLocation;
    }

    public function payload(): ModelSet
    {
        return $this->payload;
    }

    public function definitionName(): string
    {
        return $this->definitionName;
    }

    public function aggregateIdModel(): Model
    {
        return $this->aggregateIdModel;
    }

    public function commandName(): string
    {
        return $this->commandName;
    }
}
