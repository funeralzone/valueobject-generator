<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions;

use Funeralzone\ValueObjectGenerator\Definitions\Events\EventSet;
use Funeralzone\ValueObjectGenerator\Definitions\Models\ModelSet;

final class Definition
{
    private $models;
    private $events;

    public function __construct(
        ModelSet $models,
        EventSet $events
    ) {
        $this->models = $models;
        $this->events = $events;
    }

    public function models(): ModelSet
    {
        return $this->models;
    }

    public function events(): EventSet
    {
        return $this->events;
    }
}
