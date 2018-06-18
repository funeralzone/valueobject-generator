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

    public function __construct(
        ModelTypeRepository $modelTypeRepository,
        ModelDecoratorRepository $modelDecoratorRepository,
        DefinitionInputValidator $validator,
        DefinitionErrorRenderer $modelDefinitionErrorRenderer
    ) {
        $this->modelTypeRepository = $modelTypeRepository;
        $this->modelDecoratorRepository = $modelDecoratorRepository;
        $this->validator = $validator;
        $this->definitionErrorRenderer = $modelDefinitionErrorRenderer;
    }

    public function convert(array $rootNamespace, string $definitionInput): Definition
    {
        $parsedDefinitionInput = Yaml::parse($definitionInput);

        $this->validateInput($parsedDefinitionInput);

        $models = $this->convertModel($rootNamespace, $parsedDefinitionInput);
        $deltas = $this->convertDeltas($rootNamespace, $models, $parsedDefinitionInput);
        $commands = $this->convertCommands($rootNamespace, $models, $deltas, $parsedDefinitionInput);
        $queries = $this->convertQueries($rootNamespace, $models, $parsedDefinitionInput);
        $events = $this->convertEvents($rootNamespace, $models, $deltas, $parsedDefinitionInput);

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

    private function convertModel(array $rootNamespace, array $parsedDefinitionInput): ModelSet
    {
        $models = [];
        if (array_key_exists('model', $parsedDefinitionInput)) {
            $existingModels = [];
            foreach ($parsedDefinitionInput['model'] as $modelDefinitionInput) {
                $models[] = $this->convertModelElement(
                    $rootNamespace,
                    [],
                    $modelDefinitionInput,
                    $existingModels
                );
            }
        }
        return new ModelSet($models);
    }

    private function convertModelElement(
        array $rootNamespace,
        array $parentNamespace,
        array $modelDefinitionInput,
        array &$existingModels,
        ModelType $parentModelType = null
    ): Model {

        $modelDefinitionName = $modelDefinitionInput['name'];

        if (array_key_exists('namespace', $modelDefinitionInput)) {
            $external = true;

            $modelRootNamespace = [];
            $elementNamespace = explode('\\', trim($modelDefinitionInput['namespace'], '\\'));
        } else {
            $external = false;
            $modelRootNamespace = $rootNamespace;

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
                        $rootNamespace,
                        $elementNamespace,
                        $childModelDefinition,
                        $existingModels,
                        $modelType
                    );
                }
            }

            $model = new DefinedModel(
                new Location(
                    $modelRootNamespace,
                    $elementNamespace,
                    $referenceName
                ),
                new Location(
                    $modelRootNamespace,
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

    private function convertDeltas(array $rootNamespace, ModelSet $models, array $parsedDefinitionInput): DeltaSet
    {
        $deltas = [];
        if (array_key_exists('deltas', $parsedDefinitionInput)) {
            $existingDeltas = [];
            foreach ($parsedDefinitionInput['deltas'] as $deltaDefinitionInput) {
                $deltas[] = $this->convertDeltaElement(
                    $rootNamespace,
                    $models,
                    $deltaDefinitionInput,
                    $existingDeltas
                );
            }
        }
        return new DeltaSet($deltas);
    }

    private function convertDeltaElement(
        array $rootNamespace,
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
                $model = $models->getByName($modelName);

                $payloadModels[] = new ModelPayloadItem(
                    $model,
                    $payloadModelDefinition['propertyName'],
                    false
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
                $rootNamespace,
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

    private function convertCommands(
        array $rootNamespace,
        ModelSet $models,
        DeltaSet $deltas,
        array $parsedDefinitionInput
    ): CommandSet {
        $commands = [];
        if (array_key_exists('commands', $parsedDefinitionInput)) {
            foreach ($parsedDefinitionInput['commands'] as $commandDefinitionInput) {
                $commands[] = $this->convertCommandElement(
                    $rootNamespace,
                    $models,
                    $deltas,
                    $commandDefinitionInput
                );
            }
        }
        return new CommandSet($commands);
    }

    private function convertCommandElement(
        array $rootNamespace,
        ModelSet $models,
        DeltaSet $deltas,
        array $commandDefinitionInput
    ): Command {

        $commandDefinitionName = $commandDefinitionInput['name'];

        $payloadModels = [];
        if (array_key_exists('payload', $commandDefinitionInput)) {
            foreach ($commandDefinitionInput['payload'] as $payloadModelDefinition) {
                $modelName = $payloadModelDefinition['name'];
                $required = (bool) ($payloadModelDefinition['required'] ?? false);
                $model = $models->getByName($modelName);

                $payloadModels[] = new ModelPayloadItem(
                    $model,
                    $payloadModelDefinition['propertyName'],
                    $required
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
                $rootNamespace,
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

    private function convertQueries(
        array $rootNamespace,
        ModelSet $models,
        array $parsedDefinitionInput
    ): QuerySet {
        $queries = [];
        if (array_key_exists('queries', $parsedDefinitionInput)) {
            foreach ($parsedDefinitionInput['queries'] as $queryDefinitionInput) {
                $queries[] = $this->convertQueryElement(
                    $rootNamespace,
                    $models,
                    $queryDefinitionInput
                );
            }
        }
        return new QuerySet($queries);
    }

    private function convertQueryElement(
        array $rootNamespace,
        ModelSet $models,
        array $queryDefinitionInput
    ): Query {

        $queryDefinitionName = $queryDefinitionInput['name'];

        $payloadModels = [];
        if (array_key_exists('payload', $queryDefinitionInput)) {
            foreach ($queryDefinitionInput['payload'] as $payloadModelDefinition) {
                $modelName = $payloadModelDefinition['name'];
                $required = (bool) ($payloadModelDefinition['required'] ?? false);
                $model = $models->getByName($modelName);

                $payloadModels[] = new ModelPayloadItem(
                    $model,
                    $payloadModelDefinition['propertyName'],
                    $required
                );
            }
        }

        $delta = new Query(
            new Location(
                $rootNamespace,
                ['Queries'],
                $queryDefinitionName
            ),
            $queryDefinitionName,
            new ModelPayload($payloadModels)
        );

        $existingDeltas[$queryDefinitionName] = $delta;

        return $delta;
    }

    private function convertEvents(
        array $rootNamespace,
        ModelSet $models,
        DeltaSet $deltas,
        array $parsedDefinitionInput
    ): EventSet {
        $events = [];
        if (array_key_exists('events', $parsedDefinitionInput)) {
            foreach ($parsedDefinitionInput['events'] as $eventDefinitionInput) {
                $events[] = $this->convertEventElement(
                    $rootNamespace,
                    $models,
                    $deltas,
                    $eventDefinitionInput
                );
            }
        }
        return new EventSet($events);
    }

    private function convertEventElement(
        array $rootNamespace,
        ModelSet $models,
        DeltaSet $deltas,
        array $eventDefinitionInput
    ): Event {

        $eventName = $eventDefinitionInput['name'];

        $modelPayloadItems = [];
        if (array_key_exists('payload', $eventDefinitionInput)) {
            foreach ($eventDefinitionInput['payload'] as $payloadItem) {
                $modelName = $payloadItem['name'];
                $required = (bool) ($payloadItem['required'] ?? false);
                $model = $models->getByName($modelName);

                $modelPayloadItems[] = new ModelPayloadItem(
                    $model,
                    $payloadItem['propertyName'],
                    $required
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
                $model = $models->getByName($modelName);
                $required = (bool) ($metaItem['required'] ?? false);

                $eventMetaItems[] = new EventMetaItem(
                    $model,
                    $metaItem['propertyName'],
                    $metaItem['key'],
                    $required
                );
            }
        }

        return new Event(
            new Location(
                $rootNamespace,
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
