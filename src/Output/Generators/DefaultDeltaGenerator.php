<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Output\Generators;

use Funeralzone\ValueObjectGenerator\Definitions\Deltas\Delta;
use Funeralzone\ValueObjectGenerator\Definitions\Deltas\DeltaPayloadItem;
use Funeralzone\ValueObjectGenerator\Definitions\Models\ModelPayloadItem;
use Funeralzone\ValueObjectGenerator\Output\OutputTemplateRenderer;
use Funeralzone\ValueObjectGenerator\Output\OutputWriterFactory;

class DefaultDeltaGenerator implements DeltaGenerator
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

    public function generate(Delta $delta, string $outputFolderPath): void
    {
        $useStatements = [];
        $valueObjectFactoryOverrides = [];
        foreach ($delta->payload()->all() as $payloadItem) {
            /** @var ModelPayloadItem $payloadItem */
            $model = $payloadItem->model();
            $useStatements[] = $model->referenceLocation()->path();
            if (! $model->referenceLocation()->isSame($model->instantiationLocation())) {
                $useStatements[] = $model->instantiationLocation()->path();

                $valueObjectFactoryOverrides[] = $payloadItem;
            }
        }

        foreach ($delta->subDeltas()->all() as $deltaPayloadItem) {
            /** @var DeltaPayloadItem $deltaPayloadItem */
            $useStatements[] = $deltaPayloadItem->delta()->location()->path();
        }

        $source = $this->outputTemplateRenderer->render($this->templateName, [
            'delta' => $delta,
            'valueObjectFactoryOverrides' => $valueObjectFactoryOverrides,
            'useStatements' => array_unique($useStatements)
        ]);

        $outputWriter = $this->writerFactory->makeWriter($outputFolderPath, $delta->location());
        $outputWriter->write($delta->location()->name() . '.php', $source);
    }
}
