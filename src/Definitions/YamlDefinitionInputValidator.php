<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions;

use Exception;
use Funeralzone\ValueObjectGenerator\Definitions\Models\Model;
use Funeralzone\ValueObjectGenerator\Repositories\ModelTypes\ModelType;
use Funeralzone\ValueObjectGenerator\Repositories\ModelTypes\ModelTypeRepository;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Validator;

final class YamlDefinitionInputValidator implements DefinitionInputValidator
{
    private $modelTypeRepository;
    private $errors;

    private $rootSchemaRules = [
        'generatorVersion' => 'integer',
        'namespace' => 'string',
        'model' => 'array',
    ];

    private $modelGroupSchemaRules = [
        'namespace' => 'string',
        'model' => 'required|array'
    ];

    private $definedModelSchemaRules = [
        'name' => 'required|string',
        'type' => 'required|string',
        'children' => 'array',
        'export' => 'boolean',
        'instantiationName' => 'string',
        'referenceName' => 'string',
        'namespace' => 'string',

        'decorators' => 'array',
        'decorators.*.path' => 'required|string',
        'decorators.*.hooks' => 'array',
        'decorators.*.hooks.*.type' => 'required|string',
        'decorators.*.hooks.*.method' => 'required|string',
        'decorators.*.hooks.*.stage' => 'string',
        'decorators.*.hooks.*.splatArguments' => 'bool',

        'testing' => 'array',
        'testing.fromNative' => 'string',
        'testing.constructor' => 'string',
        'testing.useStatements' => 'array',
    ];

    private $referencedModelSchemaRules = [
        'name' => 'required|string',
    ];

    public function __construct(
        ModelTypeRepository $modelTypeRepository
    ) {
        $this->modelTypeRepository = $modelTypeRepository;
    }

    public function validate(array $rawDefinition, Definition $baseDefinition = null): bool
    {
        $this->errors = [];

        $this->validateSchema('Root', 'N/A', $this->rootSchemaRules, $rawDefinition);

        $this->validateModel($rawDefinition, $baseDefinition);

        return count($this->errors) == 0;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    private function validateModel(array $definitionInput, Definition $baseDefinition = null): array
    {
        $existingModelDefinitionNames = [];
        if ($baseDefinition) {
            foreach ($baseDefinition->models()->allByName() as $model) {
                /** @var Model $model */
                $existingModelDefinitionNames[] = $model->definitionName();
            }
        }

        if (array_key_exists('model', $definitionInput) && is_array($definitionInput['model'])) {
            foreach ($definitionInput['model'] as $key => $item) {
                if (array_key_exists('name', $item)) {
                    $existingModelDefinitionNames = array_unique(array_merge(
                        $existingModelDefinitionNames,
                        $this->validateModelElement($existingModelDefinitionNames, [], $item)
                    ));
                } else {
                    if ($this->validateSchema('Model group', 'N/A', $this->modelGroupSchemaRules, $item)) {
                        foreach ($item['model'] as $childItem) {
                            $existingModelDefinitionNames = array_unique(array_merge(
                                $existingModelDefinitionNames,
                                $this->validateModelElement(
                                    $existingModelDefinitionNames,
                                    [],
                                    $childItem
                                )
                            ));
                        }
                    }
                }
            }
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
                                'Model "%s" - must define a "type" or reference an existing model',
                                $modelPath
                            );
                        }
                    }

                    $decorator = $modelDefinition['nonNullDecorator'] ?? null;
                    if ($decorator) {
                        if (! (class_exists($decorator) || trait_exists($decorator))) {
                            $this->errors[] = sprintf(
                                'Model "%s" - defines a non-null decorator ("%s") but it does not exist',
                                $modelPath,
                                $decorator
                            );
                        }
                    }
                    $decorator = $modelDefinition['nullDecorator'] ?? null;
                    if ($decorator) {
                        if (! (class_exists($decorator) || trait_exists($decorator))) {
                            $this->errors[] = sprintf(
                                'Model "%s" - defines a null decorator ("%s") but it does not exist',
                                $modelPath,
                                $decorator
                            );
                        }
                    }
                    $decorator = $modelDefinition['nullableDecorator'] ?? null;
                    if ($decorator) {
                        if (! (class_exists($decorator) || trait_exists($decorator))) {
                            $this->errors[] = sprintf(
                                'Model "%s" - defines a nullable decorator ("%s") but it does not exist',
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
