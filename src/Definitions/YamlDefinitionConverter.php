<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions;

use Funeralzone\ValueObjectGenerator\Definitions\Events\Event;
use Funeralzone\ValueObjectGenerator\Definitions\Events\EventSet;
use Funeralzone\ValueObjectGenerator\Definitions\Exceptions\InvalidDefinition;
use Funeralzone\ValueObjectGenerator\Definitions\Models\DefinedModel;
use Funeralzone\ValueObjectGenerator\Definitions\Models\LinkedModel;
use Funeralzone\ValueObjectGenerator\Definitions\Models\Model;
use Funeralzone\ValueObjectGenerator\Definitions\Models\ModelProperties;
use Funeralzone\ValueObjectGenerator\Definitions\Models\ModelSet;
use Funeralzone\ValueObjectGenerator\Repositories\ExternalModels\ArrayExternalModelRepository;
use Funeralzone\ValueObjectGenerator\Repositories\ExternalModels\ExternalModelRepository;
use Funeralzone\ValueObjectGenerator\Repositories\ExternalModels\NullExternalModelRepository;
use Funeralzone\ValueObjectGenerator\Repositories\ModelDecorators\ModelDecoratorRepository;
use Funeralzone\ValueObjectGenerator\Repositories\ModelTypes\ModelType;
use Funeralzone\ValueObjectGenerator\Repositories\ModelTypes\ModelTypeRepository;
use Symfony\Component\Yaml\Yaml;

final class YamlDefinitionConverter implements DefinitionConverter
{
    private $modelTypeRepository;
    private $externalModelRepository;
    private $modelDecoratorRepository;
    private $validator;
    private $definitionErrorRenderer;
    private $rootNamespace;

    public function __construct(
        ModelTypeRepository $modelTypeRepository,
        ExternalModelRepository $externalModelRepository,
        ModelDecoratorRepository $modelDecoratorRepository,
        DefinitionInputValidator $validator,
        DefinitionErrorRenderer $modelDefinitionErrorRenderer,
        array $rootNamespace
    ) {
        $this->modelTypeRepository = $modelTypeRepository;
        $this->externalModelRepository = $externalModelRepository;
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
            $internallyExportedModels = [];
            foreach ($parsedDefinitionInput['model'] as $modelName => $modelDefinitionInput) {
                $internallyExportedModels = array_merge(
                    $internallyExportedModels,
                    $this->findInternallyExportedModelsFromModelElement([], $modelName, $modelDefinitionInput)
                );
            }
            $internallyExportedModels = new ArrayExternalModelRepository($internallyExportedModels);

            foreach ($parsedDefinitionInput['model'] as $modelName => $modelDefinitionInput) {
                $models[] = $this->convertModelElement(
                    $internallyExportedModels,
                    [],
                    $modelName,
                    $modelDefinitionInput
                );
            }
        }
        return new ModelSet($models);
    }


    private function findInternallyExportedModelsFromModelElement(
        array $parentNamespace,
        string $modelDefinitionName,
        array $modelDefinition
    ): array {
        $internallyExportedModels = [];
        if (is_array($modelDefinition)) {
            $exported = $modelDefinition['export'] ?? false;
            if ($exported) {
                $internallyExportedModels[$modelDefinitionName] = $this->convertModelElement(
                    new NullExternalModelRepository,
                    $parentNamespace,
                    $modelDefinitionName,
                    $modelDefinition
                );
            }

            if (array_key_exists('children', (array)$modelDefinition)) {
                $currentPathElements = $parentNamespace;
                $currentPathElements[] = $modelDefinitionName;

                foreach ($modelDefinition['children'] as $childModelName => $childModel) {
                    $internallyExportedModels = array_merge(
                        $internallyExportedModels,
                        $this->findInternallyExportedModelsFromModelElement(
                            $currentPathElements,
                            $childModelName,
                            $childModel
                        )
                    );
                }
            }
        }
        return $internallyExportedModels;
    }

    private function convertModelElement(
        ExternalModelRepository $internallyExportedModels,
        array $parentNamespace,
        string $modelDefinitionName,
        array $modelDefinitionInput
    ): Model {
        $typeKey = $modelDefinitionInput['type'];

        $referenceName = $modelDefinitionInput['referenceName'] ?? $modelDefinitionName;
        $instantiationName = $modelDefinitionInput['instantiationName'] ?? $modelDefinitionName;
        $propertyName = $modelDefinitionInput['propertyName'] ?? lcfirst($modelDefinitionName);

        if (array_key_exists('namespace', $modelDefinitionInput)) {
            $rootNamespace = [];
            $elementNamespace = explode('\\', $modelDefinitionInput['namespace']);
        } else {
            $rootNamespace = $this->rootNamespace;
            $elementNamespace = $parentNamespace;
            $elementNamespace[] = $referenceName;
        }

        $childModels = [];
        if (array_key_exists('children', $modelDefinitionInput)) {
            $childNamespace = $parentNamespace;
            $childNamespace[] = $referenceName;
            foreach ($modelDefinitionInput['children'] as $childModelName => $childModelDefinition) {
                $childModels[] = $this->convertModelElement(
                    $internallyExportedModels,
                    $elementNamespace,
                    $childModelName,
                    $childModelDefinition
                );
            }
        }

        if ($internallyExportedModels->has($typeKey)) {
            return new LinkedModel(
                $internallyExportedModels->get($typeKey),
                $modelDefinitionName,
                $propertyName
            );
        } elseif ($this->externalModelRepository->has($typeKey)) {
            return new LinkedModel(
                $this->externalModelRepository->get($typeKey),
                $modelDefinitionName,
                $propertyName
            );
        } elseif ($this->modelTypeRepository->has($typeKey)) {
            $type = $this->modelTypeRepository->get((string)$typeKey);

            $decoratorName = $modelDefinitionInput['decorator'] ?? null;
            if ($this->modelDecoratorRepository->has((string)$decoratorName)) {
                $decorator = $this->modelDecoratorRepository->get((string)$decoratorName);
            } else {
                $decorator = null;
            }

            return new DefinedModel(
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
                $type,
                (bool)($modelDefinitionInput['nullable'] ?? false),
                $propertyName,
                (bool)($modelDefinitionInput['export'] ?? false),
                new ModelSet($childModels),
                $this->distillModelPropertiesFromSchema($type, $modelDefinitionInput),
                $decorator
            );
        } else {
            throw new InvalidDefinition();
        }
    }

    private function distillModelPropertiesFromSchema(ModelType $modelType, array $modelDefinition): ModelProperties
    {
        $schemaValidationRules = $modelType->schemaValidationRules();
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
            foreach ($parsedDefinitionInput['events'] as $eventName => $eventDefinitionInput) {
                $events[] = $this->convertEventElement(
                    $models,
                    $eventName,
                    $eventDefinitionInput
                );
            }
        }
        return new EventSet($events);
    }

    private function convertEventElement(
        ModelSet $models,
        string $eventName,
        array $eventDefinitionInput
    ): Event {

        $eventModels = [];
        foreach ($eventDefinitionInput['payload'] as $modelPath) {
            $pathElements = explode('\\', $modelPath);
            $eventModels[] = $models->getByPath($pathElements);
        }

        $aggregateIdModel = $models->getByPath(explode('\\', $eventDefinitionInput['aggregateIdModel']));

        return new Event(
            new Location(
                $this->rootNamespace,
                ['Deltas'],
                $eventDefinitionInput['commandName'].'Delta'
            ),
            new Location(
                $this->rootNamespace,
                ['Commands'],
                $eventDefinitionInput['commandName']
            ),
            new Location(
                $this->rootNamespace,
                ['Events'],
                $eventName
            ),
            $eventName,
            $aggregateIdModel,
            $eventDefinitionInput['commandName'],
            new ModelSet($eventModels)
        );
    }
}
