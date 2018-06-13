<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Output\Generators;

use Funeralzone\ValueObjectGenerator\Conventions\ModelNamer;
use Funeralzone\ValueObjectGenerator\Definitions\Commands\Command;
use Funeralzone\ValueObjectGenerator\Definitions\Deltas\DeltaPayloadItem;
use Funeralzone\ValueObjectGenerator\Definitions\Models\ModelPayloadItem;
use Funeralzone\ValueObjectGenerator\Output\OutputTemplateRenderer;
use Funeralzone\ValueObjectGenerator\Output\OutputWriterFactory;

class DefaultCommandGenerator implements CommandGenerator
{
    private $writerFactory;
    private $outputTemplateRenderer;
    private $templateName;

    public function __construct(
        OutputWriterFactory $writerFactory,
        OutputTemplateRenderer $outputTemplateRenderer,
        string $templateName
    ) {
        $this->writerFactory = $writerFactory;
        $this->outputTemplateRenderer = $outputTemplateRenderer;
        $this->templateName = $templateName;
    }

    public function generate(Command $command): void
    {
        $outputWriter = $this->writerFactory->makeWriter($command->location());

        $modelNamer = new ModelNamer;
        $useStatements = [];
        foreach ($command->payload()->all() as $payloadItem) {
            /** @var ModelPayloadItem $payloadItem */
            $model = $payloadItem->model();
            $nonNullModelName = $modelNamer->makeNonNullClassName($model->definitionName());
            $useStatements[] = $model->instantiationLocation()->namespaceAsString().'\\'.$nonNullModelName;

            $useStatements[] = $model->referenceLocation()->path();
        }

        foreach ($command->deltas()->all() as $deltaPayloadItem) {
            /** @var DeltaPayloadItem $deltaPayloadItem */
            $useStatements[] = $deltaPayloadItem->delta()->location()->path();
        }

        $source = $this->outputTemplateRenderer->render($this->templateName, [
            'command' => $command,
            'useStatements' => array_unique($useStatements),
        ]);

        $outputWriter->write($command->definitionName() . '.php', $source);
    }
}
