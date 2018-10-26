<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions;

use Exception;
use Funeralzone\ValueObjectGenerator\Definitions\Exceptions\DefinitionSourceDoesIsInvalid;
use Funeralzone\ValueObjectGenerator\Definitions\Exceptions\InvalidDefinition;
use Funeralzone\ValueObjectGenerator\Definitions\Models\Decorators\ModelDecorator;
use Funeralzone\ValueObjectGenerator\Definitions\Models\Decorators\ModelDecoratorHookSet;
use Funeralzone\ValueObjectGenerator\Definitions\Models\Decorators\ModelDecoratorPath;
use Funeralzone\ValueObjectGenerator\Definitions\Models\Decorators\ModelDecoratorSet;
use Funeralzone\ValueObjectGenerator\Definitions\Models\DefinedModel;
use Funeralzone\ValueObjectGenerator\Definitions\Models\Model;
use Funeralzone\ValueObjectGenerator\Definitions\Models\ModelNamespace;
use Funeralzone\ValueObjectGenerator\Definitions\Models\ModelProperties;
use Funeralzone\ValueObjectGenerator\Definitions\Models\ModelSet;
use Funeralzone\ValueObjectGenerator\Definitions\Models\ReferencedModel;
use Funeralzone\ValueObjectGenerator\Repositories\ModelTypes\ModelType;
use Funeralzone\ValueObjectGenerator\Repositories\ModelTypes\ModelTypeRepository;
use Funeralzone\ValueObjectGenerator\Testing\ModelTestStipulations;
use Symfony\Component\Yaml\Yaml;

final class YamlDefinitionConverter implements DefinitionConverter
{
    private const VALID_MODEL_VALIDATION_RULE_NAME = 'validModel';

    private $modelTypeRepository;
    private $validator;
    private $definitionErrorRenderer;

    public function __construct(
        ModelTypeRepository $modelTypeRepository,
        DefinitionInputValidator $validator,
        DefinitionErrorRenderer $modelDefinitionErrorRenderer
    ) {
        $this->modelTypeRepository = $modelTypeRepository;
        $this->validator = $validator;
        $this->definitionErrorRenderer = $modelDefinitionErrorRenderer;
    }

    public function convert(
        array $rootNamespace,
        string $definitionInput,
        Definition $baseDefinition = null
    ): Definition {
        try {
            $parsedDefinitionInput = Yaml::parse($definitionInput);
        } catch (Exception $exception) {
            throw new DefinitionSourceDoesIsInvalid($exception->getMessage());
        }

        $this->validateInput($parsedDefinitionInput, $baseDefinition);

        $relativeNamespace = $this->getGlobalRelativeNamespace($parsedDefinitionInput);

        $models = $this->convertModel(
            $rootNamespace,
            $relativeNamespace,
            $parsedDefinitionInput,
            $baseDefinition
        );

        $definition = new Definition($models);

        if ($baseDefinition) {
            return $baseDefinition->merge($definition);
        } else {
            return $definition;
        }
    }

    private function validateInput(array $modelDefinitionInput, Definition $baseDefinition = null): void
    {
        if (!$this->validator->validate($modelDefinitionInput, $baseDefinition)) {
            $this->definitionErrorRenderer->render($this->validator->errors());

            throw new InvalidDefinition;
        }
    }

    private function getGlobalRelativeNamespace(array $definition): array
    {
        if (array_key_exists('namespace', $definition)) {
            $namespace = $definition['namespace'];
            $namespace = trim($namespace, '\\');
            return explode('\\', $namespace);
        } else {
            return [];
        }
    }

    private function convertModel(
        array $rootNamespace,
        array $relativeNamespace,
        array $parsedDefinitionInput,
        Definition $baseDefinition = null
    ): ModelSet {

        $models = [];
        $existingModels = [];
        if ($baseDefinition) {
            foreach ($baseDefinition->models()->allByName() as $model) {
                /** @var Model $model */
                $models[] = $model;
                $existingModels[$model->definitionName()] = $model;
            }
        }

        if (array_key_exists('model', $parsedDefinitionInput)) {
            foreach ($parsedDefinitionInput['model'] as $key => $item) {
                $itemNamespace = $relativeNamespace;

                if (array_key_exists('name', $item)) {
                    $models[] = $this->convertModelElement(
                        $rootNamespace,
                        $itemNamespace,
                        $item,
                        $existingModels
                    );
                } else {
                    if (array_key_exists('namespace', $item)) {
                        $groupNamespace = trim($item['namespace'], '\\');
                        $itemNamespace = array_merge($itemNamespace, explode('\\', $groupNamespace));
                    }

                    foreach ($item['model'] as $childItem) {
                        $models[] = $this->convertModelElement(
                            $rootNamespace,
                            $itemNamespace,
                            $childItem,
                            $existingModels
                        );
                    }
                }
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

        $modelDefinitionName = $this->getModelDefinitionName($modelDefinitionInput);

        try {
            $modelNamespace = $this->makeModelNamespace($modelDefinitionInput, $rootNamespace, $parentNamespace);
            $modelIsExternalToDefinition = $modelNamespace->rootNamespace() !== $rootNamespace;

            if (array_key_exists($modelDefinitionName, $existingModels)) {
                /** @var Model $existingModel */
                $existingModel = $existingModels[$modelDefinitionName];

                return new ReferencedModel(
                    $existingModel,
                    $modelDefinitionName,
                    $this->distillModelPropertiesFromSchema(
                        $existingModel->type(),
                        $modelDefinitionInput,
                        $parentModelType,
                        $existingModels
                    )
                );
            } else {
                $modelType = $this->getModelType($modelDefinitionInput);
                $modelDecorators = $this->makeModelDecorators($modelDefinitionInput);
                $testStipulations = $this->makeModelTestStipulations($modelDefinitionInput);
                $modelProperties = $this->distillModelPropertiesFromSchema(
                    $modelType,
                    $modelDefinitionInput,
                    $parentModelType,
                    $existingModels
                );

                $childModels = [];
                if (array_key_exists('children', $modelDefinitionInput)) {
                    $childNamespace = $parentNamespace;
                    $childNamespace[] = $modelDefinitionName;
                    foreach ($modelDefinitionInput['children'] as $childModelDefinition) {
                        $childModels[] = $this->convertModelElement(
                            $rootNamespace,
                            $modelNamespace->relativeNamespace(),
                            $childModelDefinition,
                            $existingModels,
                            $modelType
                        );
                    }
                }

                $model = new DefinedModel(
                    $modelType,
                    $modelNamespace,
                    $modelDefinitionName,
                    $modelIsExternalToDefinition,
                    $modelProperties,
                    $modelDecorators,
                    $testStipulations,
                    new ModelSet($childModels)
                );
                $existingModels[$modelDefinitionName] = $model;

                return $model;
            }
        } catch (Exception $exception) {
            $message = sprintf(
                '"%s" cannot be converted - %s',
                $modelDefinitionName,
                $exception->getMessage()
            );
            throw new DefinitionSourceDoesIsInvalid($message);
        }
    }

    private function distillModelPropertiesFromSchema(
        ModelType $modelType,
        array $modelDefinition,
        ModelType $parentModelType = null,
        array &$existingModels
    ): ModelProperties {
        $schemaValidationRules = $modelType->ownSchemaValidationRules();
        if ($parentModelType) {
            $schemaValidationRules = array_merge(
                $schemaValidationRules,
                $parentModelType->childSchemaValidationRules()
            );
        }

        $properties = [];
        foreach ($schemaValidationRules as $key => $rules) {
            try {
                if (array_key_exists($key, $modelDefinition)) {
                    $properties[$key] = $this->resolvePropertyValue($rules, $modelDefinition[$key], $existingModels);
                }
            } catch (Exception $exception) {
                throw new Exception(sprintf('"%s" property is invalid - %s', $key, $exception->getMessage()));
            }
        }

        return new ModelProperties($properties);
    }

    private function resolvePropertyValue(string $schemaValidationRules, $value, array &$existingModels)
    {
        $resolvedValue = $value;

        foreach (explode('|', $schemaValidationRules) as $rule) {
            switch ($rule) {
                case self::VALID_MODEL_VALIDATION_RULE_NAME:
                    if (array_key_exists($value, $existingModels) === false) {
                        throw new Exception(sprintf('model "%s" does not exist', $value));
                    }

                    $resolvedValue = $existingModels[$value];

                    break;
            }
        }

        return $resolvedValue;
    }


    private function getModelDefinitionName(array $modelDefinitionInput): string
    {
        return $modelDefinitionInput['name'];
    }

    private function makeModelNamespace(
        array $modelDefinitionInput,
        array $rootNamespace,
        array $parentNamespace
    ): ModelNamespace {
        if (array_key_exists('namespace', $modelDefinitionInput)) {
            $modelRootNamespace = [];
            $elementNamespace = explode('\\', trim($modelDefinitionInput['namespace'], '\\'));
        } else {
            $modelRootNamespace = $rootNamespace;

            $elementNamespace = $parentNamespace;
            $elementNamespace[] = $this->getModelDefinitionName($modelDefinitionInput);
        }

        return new ModelNamespace(
            $modelRootNamespace,
            $elementNamespace
        );
    }

    private function getModelType(array $modelDefinitionInput): ModelType
    {
        $modelTypeKey = $modelDefinitionInput['type'];
        return $this->modelTypeRepository->get((string)$modelTypeKey);
    }

    private function makeModelTestStipulations(array $modelDefinitionInput): ?ModelTestStipulations
    {
        $testStipulations = null;
        if (array_key_exists('testing', $modelDefinitionInput)) {
            $testingDefinition = $modelDefinitionInput['testing'];
            $testStipulations = new ModelTestStipulations(
                $testingDefinition['fromNative'] ?? 'null',
                $testingDefinition['useStatements'] ?? []
            );
        }
        return $testStipulations;
    }

    private function makeModelDecorators(array $modelDefinitionInput): ModelDecoratorSet
    {
        $items = [];
        if (array_key_exists('decorators', $modelDefinitionInput)) {
            foreach ($modelDefinitionInput['decorators'] as $decorator) {
                $items[] = new ModelDecorator(
                    ModelDecoratorPath::fromNative($decorator['path']),
                    ModelDecoratorHookSet::fromNative($decorator['hooks'] ?? [])
                );
            }
        }
        return new ModelDecoratorSet($items);
    }
}
