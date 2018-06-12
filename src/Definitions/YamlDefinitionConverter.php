<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions;

use Funeralzone\ValueObjectGenerator\Definitions\Events\Event;
use Funeralzone\ValueObjectGenerator\Definitions\Events\EventMeta;
use Funeralzone\ValueObjectGenerator\Definitions\Events\EventMetaItem;
use Funeralzone\ValueObjectGenerator\Definitions\Events\EventPayload;
use Funeralzone\ValueObjectGenerator\Definitions\Events\EventPayloadItem;
use Funeralzone\ValueObjectGenerator\Definitions\Events\EventSet;
use Funeralzone\ValueObjectGenerator\Definitions\Exceptions\InvalidDefinition;
use Funeralzone\ValueObjectGenerator\Definitions\Models\DefinedModel;
use Funeralzone\ValueObjectGenerator\Definitions\Models\ReferencedModel;
use Funeralzone\ValueObjectGenerator\Definitions\Models\Model;
use Funeralzone\ValueObjectGenerator\Definitions\Models\ModelProperties;
use Funeralzone\ValueObjectGenerator\Definitions\Models\ModelSet;
use Funeralzone\ValueObjectGenerator\Repositories\ModelDecorators\ModelDecoratorRepository;
use Funeralzone\ValueObjectGenerator\Repositories\ModelTypes\ModelType;
use Funeralzone\ValueObjectGenerator\Repositories\ModelTypes\ModelTypeRepository;
use Funeralzone\ValueObjectGenerator\Repositories\ModelTypes\NullModelType;
use Symfony\Component\Yaml\Yaml;

final class YamlDefinitionConverter implements DefinitionConverter
{
    private $modelTypeRepository;
    private $modelDecoratorRepository;
    private $validator;
    private $definitionErrorRenderer;
    private $rootNamespace;

    public function __construct(
        ModelTypeRepository $modelTypeRepository,
        ModelDecoratorRepository $modelDecoratorRepository,
        DefinitionInputValidator $validator,
        DefinitionErrorRenderer $modelDefinitionErrorRenderer,
        array $rootNamespace
    ) {
        $this->modelTypeRepository = $modelTypeRepository;
        $this->modelDecoratorRepository = $modelDecoratorRepository;
        $this->validator = $validator;
        $this->definitionErrorRenderer = $modelDefinitionErrorRenderer;
        $this->rootNamespace = $rootNamespace;
    }

    public function convert(string $definitionInput): Definition
    {
        $parsedDefinitionInput = Yaml::parse($definitionInput);

        $this->validateInput($parsedDefinitionInput);

        $models = $this->convertModel($parsedDefinitionInput);
        $events = $this->convertEvents($models, $parsedDefinitionInput);

        return new Definition(
            $models,
            $events
        );
    }

    private function validateInput(array $modelDefinitionInput): void
    {
        if (!$this->validator->validate($modelDefinitionInput)) {
            $this->definitionErrorRenderer->render($this->validator->errors());

            throw new InvalidDefinition;
        }
    }

    private function convertModel(array $parsedDefinitionInput): ModelSet
    {
        $models = [];
        if (array_key_exists('model', $parsedDefinitionInput)) {
            $existingModels = [];
            foreach ($parsedDefinitionInput['model'] as $modelDefinitionInput) {
                $models[] = $this->convertModelElement(
                    [],
                    $modelDefinitionInput,
                    $existingModels
                );
            }
        }
        return new ModelSet($models);
    }

    private function convertModelElement(
        array $parentNamespace,
        array $modelDefinitionInput,
        array &$existingModels,
        ModelType $parentModelType = null
    ): Model {

        $modelDefinitionName = $modelDefinitionInput['name'];

        if (array_key_exists('namespace', $modelDefinitionInput)) {
            $rootNamespace = [];
            $elementNamespace = explode('\\', trim($modelDefinitionInput['namespace'], '\\'));
        } else {
            $rootNamespace = $this->rootNamespace;

            if (array_key_exists('relativeNamespace', $modelDefinitionInput)) {
                $elementNamespace = explode('\\', trim($modelDefinitionInput['relativeNamespace'], '\\'));
            } else {
                $elementNamespace = $parentNamespace;
                $elementNamespace[] = $modelDefinitionName;
            }
        }

        if (array_key_exists($modelDefinitionName, $existingModels)) {
            /** @var Model $existingModel */
            $existingModel = $existingModels[$modelDefinitionName];

            return new ReferencedModel(
                $existingModel,
                $modelDefinitionName,
                $this->distillModelPropertiesFromSchema($existingModel->type(), $modelDefinitionInput, $parentModelType)
            );
        } else {
            $external = (bool)($modelDefinitionInput['external'] ?? false);
            $referenceName = $modelDefinitionInput['referenceName'] ?? $modelDefinitionName;
            $instantiationName = $modelDefinitionInput['instantiationName'] ?? $modelDefinitionName;

            if ($external) {
                $modelType = new NullModelType;
            } else {
                $modelTypeKey = $modelDefinitionInput['type'];
                $modelType = $this->modelTypeRepository->get((string)$modelTypeKey);
            }

            $decoratorName = $modelDefinitionInput['decorator'] ?? null;
            if ($this->modelDecoratorRepository->has((string)$decoratorName)) {
                $decorator = $this->modelDecoratorRepository->get((string)$decoratorName);
            } else {
                $decorator = null;
            }

            $childModels = [];
            if (array_key_exists('children', $modelDefinitionInput)) {
                $childNamespace = $parentNamespace;
                $childNamespace[] = $referenceName;
                foreach ($modelDefinitionInput['children'] as $childModelDefinition) {
                    $childModels[] = $this->convertModelElement(
                        $elementNamespace,
                        $childModelDefinition,
                        $existingModels,
                        $modelType
                    );
                }
            }

            $model = new DefinedModel(
                new Location(
                    $rootNamespace,
                    $elementNamespace,
                    $referenceName
                ),
                new Location(
                    $rootNamespace,
                    $elementNamespace,
                    $instantiationName
                ),
                $modelDefinitionName,
                $modelType,
                $external,
                new ModelSet($childModels),
                $this->distillModelPropertiesFromSchema($modelType, $modelDefinitionInput, $parentModelType),
                $decorator
            );
            $existingModels[$modelDefinitionName] = $model;

            return $model;
        }
    }

    private function distillModelPropertiesFromSchema(
        ModelType $modelType,
        array $modelDefinition,
        ModelType $parentModelType = null
    ): ModelProperties {
        $schemaValidationRules = $modelType->ownSchemaValidationRules();
        if ($parentModelType) {
            $schemaValidationRules = array_merge(
                $schemaValidationRules,
                $parentModelType->childSchemaValidationRules()
            );
        }

        $properties = [];
        foreach (array_keys($schemaValidationRules) as $key) {
            if (array_key_exists($key, $modelDefinition)) {
                $properties[$key] = $modelDefinition[$key];
            }
        }

        return new ModelProperties($properties);
    }

    private function convertEvents(ModelSet $models, array $parsedDefinitionInput): EventSet
    {
        $events = [];
        if (array_key_exists('events', $parsedDefinitionInput)) {
            foreach ($parsedDefinitionInput['events'] as $eventDefinitionInput) {
                $events[] = $this->convertEventElement(
                    $models,
                    $eventDefinitionInput
                );
            }
        }
        return new EventSet($events);
    }

    private function convertEventElement(
        ModelSet $models,
        array $eventDefinitionInput
    ): Event {

        $eventName = $eventDefinitionInput['name'];

        $eventPayloadItems = [];
        foreach ($eventDefinitionInput['payload'] as $payloadItem) {
            $modelName = $payloadItem['name'];
            $model = $models->getByname($modelName);

            $eventPayloadItems[] = new EventPayloadItem(
                $model,
                $payloadItem['propertyName']
            );
        }

        $eventMetaItems = [];
        if (array_key_exists('meta', $eventDefinitionInput)) {
            foreach ($eventDefinitionInput['meta'] as $metaItem) {
                $modelName = $metaItem['name'];
                $model = $models->getByname($modelName);

                $eventMetaItems[] = new EventMetaItem(
                    $model,
                    $metaItem['propertyName'],
                    $metaItem['key']
                );
            }
        }

        return new Event(
            new Location(
                $this->rootNamespace,
                ['Events'],
                $eventName
            ),
            $eventName,
            new EventPayload($eventPayloadItems),
            new EventMeta($eventMetaItems)
        );
    }
}
