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
        'external' => 'bool',
    ];

    private $referencedModelRules = [
        'name' => 'required|string',
    ];

    private $eventSchemaRules = [
        'name' => 'required|string',

        'payload' => 'required|array',
        'payload.*.name' => 'required|string',
        'payload.*.propertyName' => 'required|string',

        'meta' => 'array',
        'meta.*.name' => 'required|string',
        'meta.*.propertyName' => 'required|string',
        'meta.*.key' => 'required|string',
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
        $this->validateEvents($modelNames, $rawDefinition);

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

                    $rules = $this->referencedModelRules;
                    if ($parentType) {
                        $rules = array_merge($rules, $parentType->childSchemaValidationRules());
                    }
                    $this->validateModelSchema($modelPath, $rules, $modelDefinition);
                } else {
                    $external = (bool) ($modelDefinition['external'] ?? false);

                    if (! $external) {
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
                                $this->validateModelSchema($modelPath, $rules, $modelDefinition);

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

    private function validateModelSchema(
        string $modelPath,
        array $rules,
        array $modelDefinition
    ): void {
        foreach (array_keys($modelDefinition) as $key) {
            if (!array_key_exists($key, $rules)) {
                $this->errors[] = sprintf(
                    'Model "%s" - "%s" is an invalid property or is not allowed in this context',
                    $modelPath,
                    $key
                );
            }
        }

        $validator = new Validator(
            new Translator(new ArrayLoader(), 'EN'),
            $modelDefinition,
            $rules
        );

        try {
            $validator->validate();
        } catch (Exception $e) {
            $errorDetails = $validator->errors();
            foreach ($errorDetails->getMessages() as $propertyName => $rules) {
                $this->errors[] = sprintf(
                    'Model "%s" - "%s" is invalid. Failed on: "%s"',
                    $modelPath,
                    $propertyName,
                    str_replace('validation.', '', implode(', ', $rules))
                );
            }
        }
    }

    private function validateEvents(array $modelNames, array $definitionInput): void
    {
        if (array_key_exists('events', $definitionInput) && is_array($definitionInput['events'])) {
            foreach ($definitionInput['events'] as $eventDefinition) {
                $this->validateEventElement($modelNames, $eventDefinition);
            }
        }
    }

    private function validateEventElement(array $modelNames, array $eventDefinition): void
    {
        $eventName = $eventDefinition['name'] ?? 'N\A';

        if ($this->validateEventSchema($eventName, $this->eventSchemaRules, $eventDefinition)) {
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

            if (array_key_exists('meta', $payloadItem)) {
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

    private function validateEventSchema(string $eventName, array $rules, array $modelDefinition): bool
    {
        $valid = true;

        foreach (array_keys($modelDefinition) as $key) {
            if (!array_key_exists($key, $rules)) {
                $valid = false;

                $this->errors[] = sprintf(
                    'Event "%s" - "%s" is an invalid property',
                    $eventName,
                    $key
                );
            }
        }

        $validator = new Validator(
            new Translator(new ArrayLoader(), 'EN'),
            $modelDefinition,
            $rules
        );

        try {
            $validator->validate();
        } catch (Exception $e) {
            $valid = false;

            $errorDetails = $validator->errors();
            foreach ($errorDetails->getMessages() as $propertyName => $rules) {
                $this->errors[] = sprintf(
                    'Event "%s" - "%s" is invalid. Failed on: "%s"',
                    $eventName,
                    $propertyName,
                    str_replace('validation.', '', implode(', ', $rules))
                );
            }
        }

        return $valid;
    }
}
