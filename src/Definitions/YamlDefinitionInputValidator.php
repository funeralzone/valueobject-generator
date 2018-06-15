<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions;

use Exception;
use Funeralzone\ValueObjectGenerator\Repositories\ModelDecorators\ModelDecoratorRepository;
use Funeralzone\ValueObjectGenerator\Repositories\ModelTypes\ModelType;
use Funeralzone\ValueObjectGenerator\Repositories\ModelTypes\ModelTypeRepository;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Validator;

final class YamlDefinitionInputValidator implements DefinitionInputValidator
{
    private $modelTypeRepository;
    private $modelTypeDecoratorRepository;
    private $errors;

    private $definedModelSchemaRules = [
        'name' => 'required|string',
        'type' => 'required|string',
        'children' => 'array',
        'export' => 'boolean',
        'instantiationName' => 'string',
        'referenceName' => 'string',
        'namespace' => 'string',
        'relativeNamespace' => 'string',
        'decorator' => 'string',
    ];

    private $referencedModelSchemaRules = [
        'name' => 'required|string',
    ];

    private $deltaSchemaRules = [
        'name' => 'required|string',
        'location' => 'string',

        'payload' => 'array',
        'payload.*.name' => 'required|string',
        'payload.*.propertyName' => 'required|string',

        'deltas' => 'array',
        'deltas.*.name' => 'required|string',
        'deltas.*.propertyName' => 'required|string',
    ];

    private $commandSchemaRules = [
        'name' => 'required|string',
        'require' => 'boolean',

        'payload' => 'required|array',
        'payload.*.name' => 'required|string',
        'payload.*.propertyName' => 'required|string',

        'deltas' => 'array',
        'deltas.*.name' => 'required|string',
        'deltas.*.propertyName' => 'required|string',
    ];

    private $querySchemaRules = [
        'name' => 'required|string',

        'payload' => 'required|array',
        'payload.*.name' => 'required|string',
        'payload.*.propertyName' => 'required|string',

        'deltas' => 'array',
        'deltas.*.name' => 'required|string',
        'deltas.*.propertyName' => 'required|string',
    ];

    private $eventSchemaRules = [
        'name' => 'required|string',
        'require' => 'boolean',

        'payload' => 'array',
        'payload.*.name' => 'required|string',
        'payload.*.propertyName' => 'required|string',

        'meta' => 'array',
        'meta.*.name' => 'required|string',
        'meta.*.propertyName' => 'required|string',
        'meta.*.key' => 'required|string',

        'deltas' => 'array',
        'deltas.*.name' => 'required|string',
        'deltas.*.propertyName' => 'required|string',
    ];

    public function __construct(
        ModelTypeRepository $modelTypeRepository,
        ModelDecoratorRepository $modelTypeDecoratorRepository
    ) {
        $this->modelTypeRepository = $modelTypeRepository;
        $this->modelTypeDecoratorRepository = $modelTypeDecoratorRepository;
    }

    public function validate(array $rawDefinition): bool
    {
        $this->errors = [];

        $modelNames = $this->validateModel($rawDefinition);
        $deltaNames = $this->validateDeltas($modelNames, $rawDefinition);

        $this->validateCommands($modelNames, $deltaNames, $rawDefinition);
        $this->validateQueries($modelNames, $deltaNames, $rawDefinition);
        $this->validateEvents($modelNames, $deltaNames, $rawDefinition);

        return count($this->errors) == 0;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    private function validateModel(array $definitionInput): array
    {
        $existingModelDefinitionNames = [];
        if (array_key_exists('model', $definitionInput) && is_array($definitionInput['model'])) {
            foreach ($definitionInput['model'] as $modelDefinition) {
                $existingModelDefinitionNames = array_unique(array_merge(
                    $existingModelDefinitionNames,
                    $this->validateModelElement($existingModelDefinitionNames, [], $modelDefinition)
                ));
            }
        } else {
            $this->errors[] = '"model" has not been defined or is empty';
        }
        return $existingModelDefinitionNames;
    }

    private function validateModelElement(
        array $existingModelDefinitionNames,
        array $parentPathElements,
        array $modelDefinition,
        ModelType $parentType = null
    ): array {

        if (array_key_exists('name', $modelDefinition)) {
            $modelDefinitionName = $modelDefinition['name'];
            $modelType = null;

            if (is_array($modelDefinition)) {
                $modelPath = ltrim(implode('\\', $parentPathElements) . '\\' . $modelDefinitionName, '\\');
                $modelHasChildren = array_key_exists('children', (array)$modelDefinition);

                if (in_array($modelDefinitionName, $existingModelDefinitionNames)) {
                    if ($modelHasChildren) {
                        $this->errors[] = sprintf(
                            'Model "%s" - defines children but its cannot as it references another internal model',
                            $modelPath
                        );
                    }

                    $rules = $this->referencedModelSchemaRules;
                    if ($parentType) {
                        $rules = array_merge($rules, $parentType->childSchemaValidationRules());
                    }
                    $this->validateSchema('Model', $modelPath, $rules, $modelDefinition);
                } else {
                    $external = array_key_exists('namespace', $modelDefinition);

                    if (!$external) {
                        if (array_key_exists('type', $modelDefinition)) {
                            $typeKey = $modelDefinition['type'];

                            if ($this->modelTypeRepository->has($typeKey)) {
                                $modelType = $this->modelTypeRepository->get($typeKey);
                                if ($modelHasChildren && !$modelType->allowChildModels()) {
                                    $this->errors[] = sprintf(
                                        'Model "%s" - defines children but its type ("%s") does support them',
                                        $modelPath,
                                        $typeKey
                                    );
                                }

                                $rules = array_merge(
                                    $this->definedModelSchemaRules,
                                    $modelType->ownSchemaValidationRules()
                                );
                                if ($parentType) {
                                    $rules = array_merge($rules, $parentType->childSchemaValidationRules());
                                }
                                $this->validateSchema('Model', $modelPath, $rules, $modelDefinition);
                            } else {
                                $this->errors[] = sprintf(
                                    'Model "%s" - defines an unsupported type - "%s"',
                                    $modelPath,
                                    $typeKey
                                );
                            }
                        } else {
                            $this->errors[] = sprintf(
                                'Model "%s" - must define a "type"',
                                $modelPath
                            );
                        }
                    }

                    $decorator = $modelDefinition['decorator'] ?? null;
                    if ($decorator) {
                        if (!$this->modelTypeDecoratorRepository->has((string)$decorator)) {
                            $this->errors[] = sprintf(
                                'Model "%s" - defines a decorator ("%s") but it doesn\'t exist',
                                $modelPath,
                                $decorator
                            );
                        }
                    }

                    $existingModelDefinitionNames[] = $modelDefinitionName;
                }

                if ($modelHasChildren) {
                    $properties = $modelDefinition['children'];
                    if (is_array($properties)) {
                        $currentPathElements = $parentPathElements;
                        $currentPathElements[] = $modelDefinitionName;
                        foreach ($properties as $childModel) {
                            $existingModelDefinitionNames = array_unique(array_merge(
                                $existingModelDefinitionNames,
                                $this->validateModelElement(
                                    $existingModelDefinitionNames,
                                    $currentPathElements,
                                    $childModel,
                                    $modelType
                                )
                            ));
                        }
                    } else {
                        $this->errors[] = sprintf(
                            'Model "%s" - has defined "properties" but it is not an array',
                            $modelPath
                        );
                    }
                }
            } else {
                $this->errors[] = sprintf(
                    'Model "%s" - not defined as an array',
                    implode('\\', $parentPathElements)
                );
            }
        } else {
            $this->errors[] = sprintf(
                'All items must define a "name"',
                implode('\\', $parentPathElements)
            );
        }

        return $existingModelDefinitionNames;
    }

    private function validateDeltas(array $modelNames, array $definitionInput): array
    {
        $existingDeltaDefinitionNames = [];
        if (array_key_exists('deltas', $definitionInput) && is_array($definitionInput['deltas'])) {
            foreach ($definitionInput['deltas'] as $deltaDefinition) {
                $existingDeltaDefinitionNames = array_unique(array_merge(
                    $existingDeltaDefinitionNames,
                    $this->validateDeltaElement($modelNames, $existingDeltaDefinitionNames, $deltaDefinition)
                ));
            }
        }
        return $existingDeltaDefinitionNames;
    }

    private function validateDeltaElement(array $modelNames, array $existingDeltaNames, array $deltaDefinition): array
    {
        $deltaName = $deltaDefinition['name'] ?? 'N\A';

        if ($this->validateSchema('Delta', $deltaName, $this->deltaSchemaRules, $deltaDefinition)) {
            if (!in_array($deltaName, $existingDeltaNames)) {
                $existingDeltaNames[] = $deltaName;
                $location = $deltaDefinition['location'] ?? null;

                $payloadExists = array_key_exists('payload', $deltaDefinition);
                if ($location) {
                    if ($payloadExists) {
                        $this->errors[] = sprintf(
                            'Delta "%s" - "location" and "payload" cannot both be defined',
                            $deltaName
                        );
                    }
                } else {
                    if ($payloadExists) {
                        foreach ($deltaDefinition['payload'] as $payloadItem) {
                            $payloadItemName = $payloadItem['name'];
                            if (!in_array($payloadItemName, $modelNames)) {
                                $this->errors[] = sprintf(
                                    'Delta "%s" - "%s" is not a valid model',
                                    $deltaName,
                                    $payloadItemName
                                );
                            }
                        }
                    } else {
                        $this->errors[] = sprintf(
                            'Delta "%s" - no "payload" has been defined',
                            $deltaName
                        );
                    }
                }

                if (array_key_exists('deltas', $deltaDefinition)) {
                    foreach ($deltaDefinition['deltas'] as $deltaItem) {
                        $subDeltaName = $deltaItem['name'];
                        if (!in_array($subDeltaName, $existingDeltaNames)) {
                            $this->errors[] = sprintf(
                                'Delta "%s" - "%s" is not a valid delta',
                                $deltaName,
                                $subDeltaName
                            );
                        }
                    }
                }
            } else {
                $this->errors[] = sprintf(
                    'Delta "%s" - cannot be redefined',
                    $deltaName
                );
            }
        }

        return $existingDeltaNames;
    }

    private function validateCommands(array $modelNames, array $deltaNames, array $definitionInput): void
    {
        if (array_key_exists('commands', $definitionInput) && is_array($definitionInput['commands'])) {
            foreach ($definitionInput['commands'] as $commandDefinition) {
                $this->validateCommandElement($modelNames, $deltaNames, $commandDefinition);
            }
        }
    }

    private function validateCommandElement(array $modelNames, array $deltaNames, array $commandDefinition): void
    {
        $commandName = $commandDefinition['name'] ?? 'N\A';

        if ($this->validateSchema('Command', $commandName, $this->commandSchemaRules, $commandDefinition)) {
            foreach ($commandDefinition['payload'] as $payloadItem) {
                $commandName = $payloadItem['name'];
                if (!in_array($commandName, $modelNames)) {
                    $this->errors[] = sprintf(
                        'Command "%s" - "%s" is not a valid model',
                        $commandName,
                        $commandName
                    );
                }
            }

            if (array_key_exists('deltas', $commandDefinition)) {
                foreach ($commandDefinition['deltas'] as $deltaItem) {
                    $subDeltaName = $deltaItem['name'];
                    if (!in_array($subDeltaName, $deltaNames)) {
                        $this->errors[] = sprintf(
                            'Command "%s" - "%s" is not a valid delta',
                            $commandName,
                            $subDeltaName
                        );
                    }
                }
            }
        } else {
            $this->errors[] = sprintf(
                'Delta "%s" - cannot be redefined',
                $commandName
            );
        }
    }

    private function validateQueries(array $modelNames, array $deltaNames, array $definitionInput): void
    {
        if (array_key_exists('queries', $definitionInput) && is_array($definitionInput['queries'])) {
            foreach ($definitionInput['queries'] as $commandDefinition) {
                $this->validateQueryElement($modelNames, $deltaNames, $commandDefinition);
            }
        }
    }

    private function validateQueryElement(array $modelNames, array $deltaNames, array $queryDefinition): void
    {
        $queryName = $queryDefinition['name'] ?? 'N\A';

        if ($this->validateSchema('Query', $queryName, $this->querySchemaRules, $queryDefinition)) {
            foreach ($queryDefinition['payload'] as $payloadItem) {
                $modelName = $payloadItem['name'];
                if (!in_array($modelName, $modelNames)) {
                    $this->errors[] = sprintf(
                        'Query "%s" - "%s" is not a valid model',
                        $queryName,
                        $modelName
                    );
                }
            }

            if (array_key_exists('deltas', $queryDefinition)) {
                foreach ($queryDefinition['deltas'] as $deltaItem) {
                    $deltaName = $deltaItem['name'];
                    if (!in_array($deltaName, $deltaNames)) {
                        $this->errors[] = sprintf(
                            'Query "%s" - "%s" is not a valid delta',
                            $queryName,
                            $deltaName
                        );
                    }
                }
            }
        } else {
            $this->errors[] = sprintf(
                'Query "%s" - cannot be redefined',
                $queryName
            );
        }
    }

    private function validateEvents(array $modelNames, array $deltaNames, array $definitionInput): void
    {
        if (array_key_exists('events', $definitionInput) && is_array($definitionInput['events'])) {
            foreach ($definitionInput['events'] as $eventDefinition) {
                $this->validateEventElement($modelNames, $deltaNames, $eventDefinition);
            }
        }
    }

    private function validateEventElement(array $modelNames, array $deltaNames, array $eventDefinition): void
    {
        $eventName = $eventDefinition['name'] ?? 'N\A';

        if ($this->validateSchema('Event', $eventName, $this->eventSchemaRules, $eventDefinition)) {
            if (array_key_exists('payload', $eventDefinition)) {
                foreach ($eventDefinition['payload'] as $payloadItem) {
                    $modelName = $payloadItem['name'];
                    if (!in_array($modelName, $modelNames)) {
                        $this->errors[] = sprintf(
                            'Event "%s" - "%s" is not a valid model',
                            $eventName,
                            $modelName
                        );
                    }
                }
            }

            if (array_key_exists('deltas', $eventDefinition)) {
                foreach ($eventDefinition['deltas'] as $deltaItem) {
                    $deltaName = $deltaItem['name'];
                    if (!in_array($deltaName, $deltaNames)) {
                        $this->errors[] = sprintf(
                            'Event "%s" - "%s" is not a valid delta',
                            $deltaName,
                            $deltaName
                        );
                    }
                }
            }

            if (array_key_exists('meta', $eventDefinition)) {
                foreach ($eventDefinition['meta'] as $metaItem) {
                    $modelName = $metaItem['name'];
                    if (!in_array($modelName, $modelNames)) {
                        $this->errors[] = sprintf(
                            'Event "%s" - "%s" is not a valid model',
                            $eventName,
                            $modelName
                        );
                    }
                }
            }
        }
    }

    private function validateSchema(string $schemaType, string $name, array $rules, array $definition): bool
    {
        $valid = true;

        foreach (array_keys($definition) as $key) {
            if (!array_key_exists($key, $rules)) {
                $valid = false;

                $this->errors[] = sprintf(
                    '%s "%s" - "%s" is an invalid property',
                    $schemaType,
                    $name,
                    $key
                );
            }
        }

        $validator = new Validator(
            new Translator(new ArrayLoader(), 'EN'),
            $definition,
            $rules
        );

        try {
            $validator->validate();
        } catch (Exception $e) {
            $valid = false;

            $errorDetails = $validator->errors();
            foreach ($errorDetails->getMessages() as $propertyName => $rules) {
                $this->errors[] = sprintf(
                    '%s "%s" - "%s" is invalid. Failed on: "%s"',
                    $schemaType,
                    $name,
                    $propertyName,
                    str_replace('validation.', '', implode(', ', $rules))
                );
            }
        }

        return $valid;
    }
}
