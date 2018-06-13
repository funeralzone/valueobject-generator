<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions;

use Funeralzone\ValueObjectGenerator\Conventions\ModelNamer;
use Funeralzone\ValueObjectGenerator\Definitions\Commands\Command;
use Funeralzone\ValueObjectGenerator\Definitions\Commands\CommandSet;
use Funeralzone\ValueObjectGenerator\Definitions\Deltas\Delta;
use Funeralzone\ValueObjectGenerator\Definitions\Deltas\DeltaPayload;
use Funeralzone\ValueObjectGenerator\Definitions\Deltas\DeltaPayloadItem;
use Funeralzone\ValueObjectGenerator\Definitions\Deltas\DeltaSet;
use Funeralzone\ValueObjectGenerator\Definitions\Events\Event;
use Funeralzone\ValueObjectGenerator\Definitions\Events\EventMeta;
use Funeralzone\ValueObjectGenerator\Definitions\Events\EventMetaItem;
use Funeralzone\ValueObjectGenerator\Definitions\Events\EventSet;
use Funeralzone\ValueObjectGenerator\Definitions\Exceptions\InvalidDefinition;
use Funeralzone\ValueObjectGenerator\Definitions\Models\DefinedModel;
use Funeralzone\ValueObjectGenerator\Definitions\Models\Model;
use Funeralzone\ValueObjectGenerator\Definitions\Models\ModelPayload;
use Funeralzone\ValueObjectGenerator\Definitions\Models\ModelPayloadItem;
use Funeralzone\ValueObjectGenerator\Definitions\Models\ModelProperties;
use Funeralzone\ValueObjectGenerator\Definitions\Models\ModelSet;
use Funeralzone\ValueObjectGenerator\Definitions\Models\ReferencedModel;
use Funeralzone\ValueObjectGenerator\Definitions\Queries\Query;
use Funeralzone\ValueObjectGenerator\Definitions\Queries\QuerySet;
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
        $deltas = $this->convertDeltas($models, $parsedDefinitionInput);
        $commands = $this->convertCommands($models, $deltas, $parsedDefinitionInput);
        $queries = $this->convertQueries($models, $deltas, $parsedDefinitionInput);
        $events = $this->convertEvents($models, $deltas, $parsedDefinitionInput);

        return new Definition(
            $models,
            $deltas,
            $events,
            $commands,
            $queries
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
            $external = true;

            $rootNamespace = [];
            $elementNamespace = explode('\\', trim($modelDefinitionInput['namespace'], '\\'));
        } else {
            $external = false;
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
            $instantiationName = $modelDefinitionInput['instantiationName'] ?? $modelDefinitionName;

            if (array_key_exists('referenceName', $modelDefinitionInput)) {
                $referenceName = $modelDefinitionInput['referenceName'];
            } else {
                $modelNamer = new ModelNamer;
                $referenceName = $modelNamer->makeNullableImplementationInterfaceName($modelDefinitionName);
            }

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

    private function convertDeltas(ModelSet $models, array $parsedDefinitionInput): DeltaSet
    {
        $deltas = [];
        if (array_key_exists('deltas', $parsedDefinitionInput)) {
            $existingDeltas = [];
            foreach ($parsedDefinitionInput['deltas'] as $deltaDefinitionInput) {
                $deltas[] = $this->convertDeltaElement(
                    $models,
                    $deltaDefinitionInput,
                    $existingDeltas
                );
            }
        }
        return new DeltaSet($deltas);
    }

    private function convertDeltaElement(
        ModelSet $models,
        array $deltaDefinitionInput,
        array &$existingDeltas
    ): Delta {

        $deltaDefinitionName = $deltaDefinitionInput['name'];
        $location = $deltaDefinitionInput['location'] ?? null;

        $payloadModels = [];
        if (! $location && array_key_exists('payload', $deltaDefinitionInput)) {
            foreach ($deltaDefinitionInput['payload'] as $payloadModelDefinition) {
                $modelName = $payloadModelDefinition['name'];
                $model = $models->getByname($modelName);

                $payloadModels[] = new ModelPayloadItem(
                    $model,
                    $payloadModelDefinition['propertyName']
                );
            }
        }

        $subDeltaPayloadItems = [];
        if (array_key_exists('deltas', $deltaDefinitionInput)) {
            foreach ($deltaDefinitionInput['deltas'] as $subDeltaDefinition) {
                $subDeltaPayloadItems[] = new DeltaPayloadItem(
                    $existingDeltas[$subDeltaDefinition['name']],
                    $subDeltaDefinition['propertyName']
                );
            }
        }

        if ($location) {
            $locationElements = explode('\\', $location);
            $locationName = array_pop($locationElements);

            $deltaLocation = new Location(
                $locationElements,
                [],
                $locationName
            );
        } else {
            $deltaLocation = new Location(
                $this->rootNamespace,
                ['Deltas'],
                $deltaDefinitionName
            );
        }

        $delta = new Delta(
            $deltaLocation,
            $deltaDefinitionName,
            new ModelPayload($payloadModels),
            new DeltaPayload($subDeltaPayloadItems),
            $location == null
        );

        $existingDeltas[$deltaDefinitionName] = $delta;

        return $delta;
    }

    private function convertCommands(ModelSet $models, DeltaSet $deltas, array $parsedDefinitionInput): CommandSet
    {
        $commands = [];
        if (array_key_exists('commands', $parsedDefinitionInput)) {
            foreach ($parsedDefinitionInput['commands'] as $commandDefinitionInput) {
                $commands[] = $this->convertCommandElement(
                    $models,
                    $deltas,
                    $commandDefinitionInput
                );
            }
        }
        return new CommandSet($commands);
    }

    private function convertCommandElement(
        ModelSet $models,
        DeltaSet $deltas,
        array $commandDefinitionInput
    ): Command {

        $commandDefinitionName = $commandDefinitionInput['name'];

        $payloadModels = [];
        if (array_key_exists('payload', $commandDefinitionInput)) {
            foreach ($commandDefinitionInput['payload'] as $payloadModelDefinition) {
                $modelName = $payloadModelDefinition['name'];
                $model = $models->getByname($modelName);

                $payloadModels[] = new ModelPayloadItem(
                    $model,
                    $payloadModelDefinition['propertyName']
                );
            }
        }

        $deltaPayloadItems = [];
        if (array_key_exists('deltas', $commandDefinitionInput)) {
            foreach ($commandDefinitionInput['deltas'] as $subDeltaDefinition) {
                $deltaPayloadItems[] = new DeltaPayloadItem(
                    $deltas->getByname($subDeltaDefinition['name']),
                    $subDeltaDefinition['propertyName']
                );
            }
        }

        $delta = new Command(
            new Location(
                $this->rootNamespace,
                ['Commands'],
                $commandDefinitionName
            ),
            $commandDefinitionName,
            new ModelPayload($payloadModels),
            new DeltaPayload($deltaPayloadItems)
        );

        $existingDeltas[$commandDefinitionName] = $delta;

        return $delta;
    }

    private function convertQueries(ModelSet $models, DeltaSet $deltas, array $parsedDefinitionInput): QuerySet
    {
        $queries = [];
        if (array_key_exists('queries', $parsedDefinitionInput)) {
            foreach ($parsedDefinitionInput['queries'] as $queryDefinitionInput) {
                $queries[] = $this->convertQueryElement(
                    $models,
                    $deltas,
                    $queryDefinitionInput
                );
            }
        }
        return new QuerySet($queries);
    }

    private function convertQueryElement(
        ModelSet $models,
        DeltaSet $deltas,
        array $queryDefinitionInput
    ): Query {

        $queryDefinitionName = $queryDefinitionInput['name'];

        $payloadModels = [];
        if (array_key_exists('payload', $queryDefinitionInput)) {
            foreach ($queryDefinitionInput['payload'] as $payloadModelDefinition) {
                $modelName = $payloadModelDefinition['name'];
                $model = $models->getByname($modelName);

                $payloadModels[] = new ModelPayloadItem(
                    $model,
                    $payloadModelDefinition['propertyName']
                );
            }
        }

        $deltaPayloadItems = [];
        if (array_key_exists('deltas', $queryDefinitionInput)) {
            foreach ($queryDefinitionInput['deltas'] as $subDeltaDefinition) {
                $deltaPayloadItems[] = new DeltaPayloadItem(
                    $deltas->getByname($subDeltaDefinition['name']),
                    $subDeltaDefinition['propertyName']
                );
            }
        }

        $delta = new Query(
            new Location(
                $this->rootNamespace,
                ['Queries'],
                $queryDefinitionName
            ),
            $queryDefinitionName,
            new ModelPayload($payloadModels),
            new DeltaPayload($deltaPayloadItems)
        );

        $existingDeltas[$queryDefinitionName] = $delta;

        return $delta;
    }

    private function convertEvents(ModelSet $models, DeltaSet $deltas, array $parsedDefinitionInput): EventSet
    {
        $events = [];
        if (array_key_exists('events', $parsedDefinitionInput)) {
            foreach ($parsedDefinitionInput['events'] as $eventDefinitionInput) {
                $events[] = $this->convertEventElement(
                    $models,
                    $deltas,
                    $eventDefinitionInput
                );
            }
        }
        return new EventSet($events);
    }

    private function convertEventElement(
        ModelSet $models,
        DeltaSet $deltas,
        array $eventDefinitionInput
    ): Event {

        $eventName = $eventDefinitionInput['name'];

        $modelPayloadItems = [];
        if (array_key_exists('payload', $eventDefinitionInput)) {
            foreach ($eventDefinitionInput['payload'] as $payloadItem) {
                $modelName = $payloadItem['name'];
                $model = $models->getByname($modelName);

                $modelPayloadItems[] = new ModelPayloadItem(
                    $model,
                    $payloadItem['propertyName']
                );
            }
        }

        $deltaPayloadItems = [];
        if (array_key_exists('deltas', $eventDefinitionInput)) {
            foreach ($eventDefinitionInput['deltas'] as $deltaDefinition) {
                $deltaPayloadItems[] = new DeltaPayloadItem(
                    $deltas->getByname($deltaDefinition['name']),
                    $deltaDefinition['propertyName']
                );
            }
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
            new ModelPayload($modelPayloadItems),
            new DeltaPayload($deltaPayloadItems),
            new EventMeta($eventMetaItems)
        );
    }
}
