<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions;

use Exception;
use Funeralzone\ValueObjectGenerator\Repositories\ExternalModels\ExternalModelRepository;
use Funeralzone\ValueObjectGenerator\Repositories\ModelDecorators\ModelDecoratorRepository;
use Funeralzone\ValueObjectGenerator\Repositories\ModelTypes\ModelTypeRepository;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Validator;

final class YamlDefinitionInputValidator implements DefinitionInputValidator
{
    private $externalModelRepository;
    private $modelTypeRepository;
    private $modelTypeDecoratorRepository;
    private $errors;

    private $commonModelSchemaRules = [
        'type' => 'required|string',
        'propertyName' => 'string',
        'nullable' => 'boolean',
        'children' => 'array',
        'export' => 'boolean',
        'instantiationName' => 'string',
        'referenceName' => 'string',
        'namespace' => 'string',
        'decorator' => 'string',
    ];

    private $commonExternalModelSchemaRules = [
        'type' => 'required|string',
        'propertyName' => 'string',
    ];

    private $eventSchemaRules = [
        'commandName' => 'required|string',
        'aggregateIdModel' => 'required|string',
        'payload' => 'required|array',
    ];

    public function __construct(
        ModelTypeRepository $modelTypeRepository,
        ExternalModelRepository $externalModelRepository,
        ModelDecoratorRepository $modelTypeDecoratorRepository
    ) {
        $this->externalModelRepository = $externalModelRepository;
        $this->modelTypeRepository = $modelTypeRepository;
        $this->modelTypeDecoratorRepository = $modelTypeDecoratorRepository;
    }

    public function validate(array $rawDefinition): bool
    {
        $this->errors = [];

        $modelPaths = $this->validateModel($rawDefinition);
        $this->validateEvents($modelPaths, $rawDefinition);

        return count($this->errors) == 0;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    private function validateModel(array $definitionInput): array
    {
        $modelPaths = [];
        if (array_key_exists('model', $definitionInput) && is_array($definitionInput['model'])) {
            $internallyExportedModels = [];
            foreach ($definitionInput['model'] as $modelName => $modelDefinition) {
                $internallyExportedModels = array_merge(
                    $internallyExportedModels,
                    $this->findInternallyExportedModelsFromModelElement([], $modelName, $modelDefinition)
                );
            }

            foreach ($definitionInput['model'] as $modelName => $modelDefinition) {
                $modelPaths = array_merge(
                    $modelPaths,
                    $this->validateModelElement($internallyExportedModels, [], $modelName, $modelDefinition)
                );
            }
        } else {
            $this->errors[] = '"model" has not been defined or is empty';
        }
        return $modelPaths;
    }

    private function findInternallyExportedModelsFromModelElement(
        array $parentPathElements,
        string $modelName,
        array $modelDefinition,
        string $pathOfExportedParent = null
    ): array {
        $internallyExportedModels = [];
        if (is_array($modelDefinition)) {
            $modelPath = ltrim(implode('\\', $parentPathElements) . '\\' . $modelName, '\\');

            $exported = $modelDefinition['export'] ?? false;
            if ($exported) {
                if ($pathOfExportedParent === null) {
                    $internallyExportedModels[$modelName] = $modelDefinition;
                } else {
                    $this->errors[] = sprintf(
                        'Model "%s" - cannot be exported because a parent (%s) already has been',
                        $modelPath,
                        $pathOfExportedParent
                    );
                }
            }

            if (array_key_exists('children', (array)$modelDefinition)) {
                $currentPathElements = $parentPathElements;
                $currentPathElements[] = $modelName;

                foreach ($modelDefinition['children'] as $childModelName => $childModel) {
                    $internallyExportedModels = array_merge(
                        $internallyExportedModels,
                        $this->findInternallyExportedModelsFromModelElement(
                            $currentPathElements,
                            $childModelName,
                            $childModel,
                            $exported ? $modelPath : $pathOfExportedParent
                        )
                    );
                }
            }
        }
        return $internallyExportedModels;
    }

    private function validateModelElement(
        array $internallyExportedModels,
        array $parentPathElements,
        string $modelName,
        $modelDefinition
    ): array {
        $modelPaths = [];

        if (is_array($modelDefinition)) {
            $modelPath = ltrim(implode('\\', $parentPathElements) . '\\' . $modelName, '\\');
            $modelPaths[] = $modelPath;

            $modelHasChildren = array_key_exists('children', (array)$modelDefinition);

            if (array_key_exists('type', $modelDefinition)) {
                $typeKey = $modelDefinition['type'];

                if (array_key_exists($typeKey, $internallyExportedModels)) {
                    if ($modelHasChildren) {
                        $this->errors[] = sprintf(
                            'Model "%s" - defines children but its cannot as it references another internal model',
                            $modelPath,
                            $typeKey
                        );
                    }

                    $this->validateModelSchema($modelPath, $this->commonExternalModelSchemaRules, $modelDefinition);
                } elseif ($this->externalModelRepository->has($typeKey)) {
                    if ($modelHasChildren) {
                        $this->errors[] = sprintf(
                            'Model "%s" - defines children but its cannot as it references another model',
                            $modelPath,
                            $typeKey
                        );
                    }

                    $this->validateModelSchema($modelPath, $this->commonExternalModelSchemaRules, $modelDefinition);
                } elseif ($this->modelTypeRepository->has($typeKey)) {
                    $type = $this->modelTypeRepository->get($typeKey);
                    if ($modelHasChildren && !$type->allowChildModels()) {
                        $this->errors[] = sprintf(
                            'Model "%s" - defines children but its type ("%s") does support them',
                            $modelPath,
                            $typeKey
                        );
                    }

                    $rules = array_merge($this->commonModelSchemaRules, $type->schemaValidationRules());
                    $this->validateModelSchema($modelPath, $rules, $modelDefinition);

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
                } else {
                    $this->errors[] = sprintf(
                        'Model "%s" - defines an unsupported type - "%s"',
                        $modelPath,
                        $typeKey
                    );
                }
            } else {
                $this->errors[] = sprintf(
                    'Model "%s" - must define either a "type"',
                    $modelPath
                );
            }

            if ($modelHasChildren) {
                $properties = $modelDefinition['children'];
                if (is_array($properties)) {
                    $currentPathElements = $parentPathElements;
                    $currentPathElements[] = $modelName;
                    foreach ($properties as $childModelName => $childModel) {
                        $modelPaths = array_merge(
                            $modelPaths,
                            $this->validateModelElement(
                                $internallyExportedModels,
                                $currentPathElements,
                                $childModelName,
                                $childModel
                            )
                        );
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

        return $modelPaths;
    }

    private function validateModelSchema(string $modelPath, array $rules, array $modelDefinition): void
    {
        foreach (array_keys($modelDefinition) as $key) {
            if (!array_key_exists($key, $rules)) {
                $this->errors[] = sprintf(
                    'Model "%s" - "%s" is an invalid property',
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

    private function validateEvents(array $modelPaths, array $definitionInput): void
    {
        if (array_key_exists('events', $definitionInput) && is_array($definitionInput['events'])) {
            foreach ($definitionInput['events'] as $eventName => $eventDefinition) {
                $this->validateEventElement($modelPaths, $eventName, $eventDefinition);
            }
        }
    }

    private function validateEventElement(array $modelPaths, string $eventName, array $eventDefinition): void
    {
        if ($this->validateEventSchema($eventName, $this->eventSchemaRules, $eventDefinition)) {
            $aggregateIdModelPath = $eventDefinition['aggregateIdModel'] ?? null;
            if (! $aggregateIdModelPath) {
                $this->errors[] = sprintf(
                    'Event "%s" - "aggregateModelId" has not been defind',
                    $eventName
                );
            }
            if (!in_array($aggregateIdModelPath, $modelPaths)) {
                $this->errors[] = sprintf(
                    'Event "%s" - "%s" is not a valid model',
                    $eventName,
                    $aggregateIdModelPath
                );
            }

            $payload = $eventDefinition['payload'];
            foreach ($payload as $modelPath) {
                if (!in_array($modelPath, $modelPaths)) {
                    $this->errors[] = sprintf(
                        'Event "%s" - "%s" is not a valid model',
                        $eventName,
                        $modelPath
                    );
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
