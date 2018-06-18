<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Output\Generators;

use Funeralzone\ValueObjectGenerator\Conventions\ModelNamer;
use Funeralzone\ValueObjectGenerator\Definitions\Deltas\DeltaPayloadItem;
use Funeralzone\ValueObjectGenerator\Definitions\Events\Event;
use Funeralzone\ValueObjectGenerator\Definitions\Events\EventMetaItem;
use Funeralzone\ValueObjectGenerator\Definitions\Models\ModelPayloadItem;
use Funeralzone\ValueObjectGenerator\Output\OutputTemplateRenderer;
use Funeralzone\ValueObjectGenerator\Output\OutputWriterFactory;

class DefaultEventGenerator implements EventGenerator
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

    public function generate(Event $event, string $outputFolderPath)
    {
        $modelNamer = new ModelNamer;
        $useStatements = [];
        foreach ($event->payload()->all() as $payloadItem) {
            /** @var ModelPayloadItem $payloadItem */
            $model = $payloadItem->model();

            if ($payloadItem->required()) {
                $nonNullModelName = $modelNamer->makeNonNullClassName($model->definitionName());
                $useStatements[] = $model->instantiationLocation()->namespaceAsString() . '\\' . $nonNullModelName;
            } else {
                $useStatements[] = $model->instantiationLocation()->path();
            }
        }

        foreach ($event->deltas()->all() as $deltaPayloadItem) {
            /** @var DeltaPayloadItem $deltaPayloadItem */
            $useStatements[] = $deltaPayloadItem->delta()->location()->path();
        }

        foreach ($event->meta()->all() as $metaItem) {
            /** @var EventMetaItem $metaItem */
            $model = $metaItem->model();

            if ($metaItem->required()) {
                $nonNullModelName = $modelNamer->makeNonNullClassName($model->definitionName());
                $useStatements[] = $model->instantiationLocation()->namespaceAsString() . '\\' . $nonNullModelName;
            } else {
                $useStatements[] = $model->instantiationLocation()->path();
            }
        }

        $source = $this->outputTemplateRenderer->render($this->templateName, [
            'event' => $event,
            'useStatements' => array_unique($useStatements),
        ]);

        $outputWriter = $this->writerFactory->makeWriter($outputFolderPath, $event->location());
        $outputWriter->write($event->definitionName() . '.php', $source);
    }
}
