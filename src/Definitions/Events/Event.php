<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Events;

use Funeralzone\ValueObjectGenerator\Definitions\Location;

final class Event
{
    private $eventLocation;
    private $definitionName;
    private $payload;
    private $meta;

    public function __construct(
        Location $eventLocation,
        string $definitionName,
        EventPayload $payload,
        EventMeta $meta
    ) {
        $this->eventLocation = $eventLocation;
        $this->definitionName = $definitionName;
        $this->payload = $payload;
        $this->meta = $meta;
    }

    public function eventLocation(): Location
    {
        return $this->eventLocation;
    }

    public function definitionName(): string
    {
        return $this->definitionName;
    }

    public function payload(): EventPayload
    {
        return $this->payload;
    }

    public function meta(): EventMeta
    {
        return $this->meta;
    }
}
