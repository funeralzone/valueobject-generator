<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator;

use Funeralzone\ValueObjectGenerator\Definitions\Definition;
use Funeralzone\ValueObjectGenerator\Definitions\Deltas\Delta;
use Funeralzone\ValueObjectGenerator\Output\Generators\CommandGenerator;
use Funeralzone\ValueObjectGenerator\Output\Generators\DeltaGenerator;
use Funeralzone\ValueObjectGenerator\Output\Generators\EventGenerator;
use Funeralzone\ValueObjectGenerator\Output\Generators\ModelGenerator;
use Funeralzone\ValueObjectGenerator\Output\Generators\QueryGenerator;
use Funeralzone\ValueObjectGenerator\Output\OutputFormatter;

final class VOGenerator
{
    private $formatter;
    private $modelGenerator;
    private $deltaGenerator;
    private $eventGenerator;
    private $commandGenerator;
    private $queryGenerator;

    public function __construct(
        OutputFormatter $formatter,
        ModelGenerator $modelGenerator,
        DeltaGenerator $deltaGenerator,
        EventGenerator $eventGenerator,
        CommandGenerator $commandGenerator,
        QueryGenerator $queryGenerator
    ) {
        $this->formatter = $formatter;
        $this->modelGenerator = $modelGenerator;
        $this->deltaGenerator = $deltaGenerator;
        $this->eventGenerator = $eventGenerator;
        $this->commandGenerator = $commandGenerator;
        $this->queryGenerator = $queryGenerator;
    }

    public function generate(Definition $definition)
    {
        $this->formatter->format();

        $this->generateModel($definition);
        $this->generateDeltas($definition);
        $this->generateEvents($definition);
        $this->generateCommands($definition);
        $this->generateQueries($definition);
    }

    private function generateModel(Definition $definition): void
    {
        foreach ($definition->models()->all() as $model) {
            $this->modelGenerator->generate($model);
        }
    }

    private function generateDeltas(Definition $definition): void
    {
        foreach ($definition->deltas()->all() as $delta) {
            /** @var Delta $delta */
            if ($delta->createable()) {
                $this->deltaGenerator->generate($delta);
            }
        }
    }

    private function generateEvents(Definition $definition): void
    {
        foreach ($definition->events()->all() as $event) {
            $this->eventGenerator->generate($event);
        }
    }

    private function generateCommands(Definition $definition): void
    {
        foreach ($definition->commands()->all() as $event) {
            $this->commandGenerator->generate($event);
        }
    }

    private function generateQueries(Definition $definition): void
    {
        foreach ($definition->queries()->all() as $event) {
            $this->queryGenerator->generate($event);
        }
    }
}
