<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator;

use Funeralzone\ValueObjectGenerator\Definitions\Definition;
use Funeralzone\ValueObjectGenerator\Output\Generators\EventGenerator;
use Funeralzone\ValueObjectGenerator\Output\Generators\ModelGenerator;
use Funeralzone\ValueObjectGenerator\Output\OutputFormatter;

final class VOGenerator
{
    private $formatter;
    private $modelGenerator;
    private $eventGenerator;

    public function __construct(
        OutputFormatter $formatter,
        ModelGenerator $modelGenerator,
        EventGenerator $eventGenerator
    ) {
        $this->formatter = $formatter;
        $this->modelGenerator = $modelGenerator;
        $this->eventGenerator = $eventGenerator;
    }

    public function generate(Definition $definition)
    {
        $this->formatter->format();
        $this->generateModel($definition);
        $this->generateEvents($definition);
    }

    private function generateModel(Definition $definition): void
    {
        foreach ($definition->models()->all() as $model) {
            $this->modelGenerator->generate($model);
        }
    }

    private function generateEvents(Definition $definition): void
    {
        foreach ($definition->events()->all() as $event) {
            $this->eventGenerator->generate($event);
        }
    }
}
