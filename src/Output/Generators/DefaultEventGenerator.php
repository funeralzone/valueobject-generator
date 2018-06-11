<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Output\Generators;

use Funeralzone\ValueObjectGenerator\Definitions\Events\Event;
use Funeralzone\ValueObjectGenerator\Definitions\Location;
use Funeralzone\ValueObjectGenerator\Definitions\Models\Model;
use Funeralzone\ValueObjectGenerator\Definitions\Models\ModelSet;
use Funeralzone\ValueObjectGenerator\Output\OutputTemplateRenderer;
use Funeralzone\ValueObjectGenerator\Output\OutputWriterFactory;

class DefaultEventGenerator implements EventGenerator
{
    private $writerFactory;
    private $outputTemplateRenderer;
    private $eventTemplateName;
    private $eventCommandTemplateName;
    private $eventDeleteTemplateName;

    public function __construct(
        OutputWriterFactory $writerFactory,
        OutputTemplateRenderer $outputTemplateRenderer,
        string $eventTemplateName,
        string $eventCommandTemplateName,
        string $eventDeleteTemplateName
    ) {
        $this->writerFactory = $writerFactory;
        $this->outputTemplateRenderer = $outputTemplateRenderer;
        $this->eventTemplateName = $eventTemplateName;
        $this->eventCommandTemplateName = $eventCommandTemplateName;
        $this->eventDeleteTemplateName = $eventDeleteTemplateName;
    }

    public function generate(Event $event)
    {
        $this->generateDelta($event);
        $this->generateCommand($event);
        $this->generateEvent($event);
    }

    private function generateEvent(Event $event): void
    {
        $outputWriter = $this->writerFactory->makeWriter($event->eventLocation());

        $hasDelta = false;
        $useStatements = [];
        foreach ($event->payload()->all() as $model) {
            /** @var Model $model */
            if ($model->nullable()) {
                $hasDelta = true;
                $useStatements[] = $event->deltaLocation()->path();
            } else {
                $useStatements[] = $model->instantiationLocation()->path();
                if (!$model->referenceLocation()->isSame($model->instantiationLocation())) {
                    $useStatements[] = $model->referenceLocation()->path();
                }
            }
        }

        $source = $this->outputTemplateRenderer->render($this->eventTemplateName, [
            'event' => $event,
            'hasDelta' => $hasDelta,
            'useStatements' => array_unique($useStatements),
            'payload' => $event->payload()
        ]);

        $outputWriter->write($event->definitionName() . '.php', $source);
    }

    private function generateCommand(Event $event): void
    {
        $outputWriter = $this->writerFactory->makeWriter($event->commandLocation());

        $useStatements = [];
        $hasDelta = false;
        foreach ($event->payload()->all() as $model) {
            /** @var Model $model */
            if ($model->nullable()) {
                $hasDelta = true;
                $useStatements[] = $event->deltaLocation()->path();
            } else {
                $useStatements[] = $model->referenceLocation()->path();
                $useStatements[] = $model->instantiationLocation()->path();
            }
        }

        $source = $this->outputTemplateRenderer->render($this->eventCommandTemplateName, [
            'event' => $event,
            'hasDelta' => $hasDelta,
            'useStatements' => array_unique($useStatements),
            'payload' => $event->payload()
        ]);

        $outputWriter->write($event->commandName() . '.php', $source);
    }

    private function generateDelta(Event $event): void
    {
        $this->generateDeltaInstance(
            $event,
            $event->payload(),
            $event->deltaLocation()
        );
    }

    private function generateDeltaInstance(Event $event, ModelSet $payload, Location $deltaLocation): void
    {
        $hasNullableChildren = false;
        foreach ($payload->all() as $model) {
            /** @var Model $model */
            if ($model->nullable()) {
                $hasNullableChildren = true;
                break;
            }
        }

        if ($hasNullableChildren) {
            $useStatements = [];
            $payloadDeltaOverrides = [];
            $valueObjectFactoryOverrides = [];
            foreach ($payload->all() as $model) {
                /** @var Model $model */
                if ($model->nullable()) {
                    if (count($model->children()) > 1) {
                        $subDeltaLocation = new Location(
                            $deltaLocation->rootNamespace(),
                            $deltaLocation->relativeNamespace(),
                            $event->commandName() . $model->propertyNameUcFirst() . 'Delta'
                        );
                        $this->generateDeltaInstance(
                            $event,
                            $model->children(),
                            $subDeltaLocation
                        );
                        if ($subDeltaLocation !== null) {
                            $payloadDeltaOverrides[$model->definitionName()] = [
                                'deltaLocation' => $subDeltaLocation,
                                'model' => $model
                            ];
                        }
                    } else {
                        if ($model->referenceLocation()->isSame($model->instantiationLocation())) {
                            $useStatements[] = $model->referenceLocation()->path();
                        } else {
                            $useStatements[] = $model->referenceLocation()->path();
                            $useStatements[] = $model->instantiationLocation()->path();

                            $valueObjectFactoryOverrides[] = $model;
                        }
                    }
                }
            }

            $outputWriter = $this->writerFactory->makeWriter($event->deltaLocation());

            $source = $this->outputTemplateRenderer->render($this->eventDeleteTemplateName, [
                'event' => $event,
                'deltaLocation' => $deltaLocation,
                'payloadDeltaOverrides' => $payloadDeltaOverrides,
                'valueObjectFactoryOverrides' => $valueObjectFactoryOverrides,
                'useStatements' => array_unique($useStatements),
                'payload' => $payload
            ]);

            $outputWriter->write($deltaLocation->name() . '.php', $source);
        }
    }
}
