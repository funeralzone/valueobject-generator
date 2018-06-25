<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions;

use Funeralzone\ValueObjectGenerator\Definitions\Commands\CommandSet;
use Funeralzone\ValueObjectGenerator\Definitions\Deltas\DeltaSet;
use Funeralzone\ValueObjectGenerator\Definitions\Events\EventSet;
use Funeralzone\ValueObjectGenerator\Definitions\Models\ModelSet;
use Funeralzone\ValueObjectGenerator\Definitions\Queries\QuerySet;

final class Definition
{
    private $models;
    private $deltas;
    private $events;
    private $commands;
    private $queries;

    public function __construct(
        ModelSet $models,
        DeltaSet $deltas,
        EventSet $events,
        CommandSet $commands,
        QuerySet $queries
    ) {
        $this->models = $models;
        $this->deltas = $deltas;
        $this->events = $events;
        $this->commands = $commands;
        $this->queries = $queries;
    }

    public function models(): ModelSet
    {
        return $this->models;
    }

    public function deltas(): DeltaSet
    {
        return $this->deltas;
    }

    public function events(): EventSet
    {
        return $this->events;
    }

    public function commands(): CommandSet
    {
        return $this->commands;
    }

    public function queries(): QuerySet
    {
        return $this->queries;
    }

    public function merge(Definition ...$definitions): Definition
    {
        if (count($definitions)) {
            array_unshift($definitions, $this);

            $models = [];
            $deltas = [];
            $events = [];
            $commands = [];
            $queries = [];
            foreach ($definitions as $definition) {
                foreach ($definition->models->all() as $model) {
                    $models[] = $model;
                }
                foreach ($definition->deltas->all() as $delta) {
                    $deltas[] = $delta;
                }
                foreach ($definition->events->all() as $event) {
                    $events[] = $event;
                }
                foreach ($definition->commands->all() as $command) {
                    $commands[] = $command;
                }
                foreach ($definition->queries->all() as $query) {
                    $queries[] = $query;
                }
            }

            return new Definition(
                new ModelSet($models),
                new DeltaSet($deltas),
                new EventSet($events),
                new CommandSet($commands),
                new QuerySet($queries)
            );
        } else {
            return $this;
        }
    }
}
