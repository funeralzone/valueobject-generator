<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions;

use Exception;
use Funeralzone\ValueObjectGenerator\Definitions\Exceptions\DefinitionIsInvalid;
use Funeralzone\ValueObjectGenerator\Definitions\Exceptions\InvalidDefinition;
use Funeralzone\ValueObjectGenerator\Definitions\Models\Decorators\ModelDecorator;
use Funeralzone\ValueObjectGenerator\Definitions\Models\Decorators\ModelDecoratorHookSet;
use Funeralzone\ValueObjectGenerator\Definitions\Models\Decorators\ModelDecoratorHookStage;
use Funeralzone\ValueObjectGenerator\Definitions\Models\Decorators\ModelDecoratorPath;
use Funeralzone\ValueObjectGenerator\Definitions\Models\Decorators\ModelDecoratorSet;
use Funeralzone\ValueObjectGenerator\Definitions\Models\DefinedModel;
use Funeralzone\ValueObjectGenerator\Definitions\Models\Model;
use Funeralzone\ValueObjectGenerator\Definitions\Models\ModelInterface;
use Funeralzone\ValueObjectGenerator\Definitions\Models\ModelInterfaces;
use Funeralzone\ValueObjectGenerator\Definitions\Models\ModelNamespace;
use Funeralzone\ValueObjectGenerator\Definitions\Models\ModelProperties;
use Funeralzone\ValueObjectGenerator\Definitions\Models\ModelRegister;
use Funeralzone\ValueObjectGenerator\Definitions\Models\ModelSet;
use Funeralzone\ValueObjectGenerator\Definitions\Models\ReferencedModel;
use Funeralzone\ValueObjectGenerator\Repositories\ModelTypes\ModelType;
use Funeralzone\ValueObjectGenerator\Repositories\ModelTypes\ModelTypeRepository;
use Funeralzone\ValueObjectGenerator\Testing\ModelTestStipulations;

final class DefaultDefinitionConverter implements DefinitionConverter
{
    private $modelTypeRepository;
    private $validator;
    private $definitionErrorRenderer;

    public function __construct(
        ModelTypeRepository $modelTypeRepository,
        NativeDefinitionValidator $validator,
        DefinitionErrorRenderer $modelDefinitionErrorRenderer
    ) {
        $this->modelTypeRepository = $modelTypeRepository;
        $this->validator = $validator;
        $this->definitionErrorRenderer = $modelDefinitionErrorRenderer;
    }

    public function convert(
        array $rootNamespace,
        NativeDefinition $nativeDefinition
    ): Definition {
        $this->validateInput($nativeDefinition);

        $relativeNamespace = $this->getGlobalRelativeNamespace($nativeDefinition);

        $modelRegister = new ModelRegister();
        $baseModels = new ModelSet($modelRegister);

        $models = $this->convertModel(
            $modelRegister,
            $baseModels,
            $rootNamespace,
            $relativeNamespace,
            $nativeDefinition
        );

        return new Definition($modelRegister, $models);
    }

    private function validateInput(NativeDefinition $nativeDefinition): void
    {
        if (!$this->validator->validate($nativeDefinition)) {
            $this->definitionErrorRenderer->render($this->validator->errors());

            throw new InvalidDefinition;
        }
    }

    private function getGlobalRelativeNamespace(NativeDefinition $nativeDefinition): array
    {
        $namespace = $nativeDefinition->getNamespace();
        $namespace = trim($namespace, '\\');

        $namespaceElements = explode('\\', $namespace);

        return array_filter($namespaceElements);
    }

    private function convertModel(
        ModelRegister $modelRegister,
        ModelSet $models,
        array $rootNamespace,
        array $relativeNamespace,
        NativeDefinition $nativeDefinition
    ): ModelSet {

        if (count($nativeDefinition->getModel()) === 0) {
            return $models;
        }

        $allDefinedModelNames = array_merge(
            array_keys($modelRegister->allByName()),
            $this->indexAllDefinedModelNamesFromDefinitionInput($nativeDefinition->getModel())
        );

        foreach ($nativeDefinition->getModel() as $key => $item) {
            $itemNamespace = $relativeNamespace;

            if (array_key_exists('name', $item)) {
                $models->add($this->convertModelElement(
                    $modelRegister,
                    $models,
                    $rootNamespace,
                    $itemNamespace,
                    $item,
                    $allDefinedModelNames,
                    null
                ));
            } else {
                if (array_key_exists('namespace', $item) && $item['namespace'] !== '') {
                    $groupNamespace = trim($item['namespace'], '\\');
                    $itemNamespace = array_merge($itemNamespace, explode('\\', $groupNamespace));

                    $itemNamespace = array_filter($itemNamespace);
                }

                foreach (($item['model'] ?? []) as $childItem) {
                    $models->add($this->convertModelElement(
                        $modelRegister,
                        $models,
                        $rootNamespace,
                        $itemNamespace,
                        $childItem,
                        $allDefinedModelNames,
                        null
                    ));
                }
            }
        }

        return $models;
    }

    private function indexAllDefinedModelNamesFromDefinitionInput(array $modelDefinitions): array
    {
        $modelDefinitionNames = [];

        foreach ($modelDefinitions as $modelDefinition) {
            if (array_key_exists('name', $modelDefinition) && array_key_exists('type', $modelDefinition)) {
                $modelDefinitionNames[] = $modelDefinition['name'];

                if (array_key_exists('children', $modelDefinition)) {
                    $modelDefinitionNames = array_merge(
                        $modelDefinitionNames,
                        $this->indexAllDefinedModelNamesFromDefinitionInput($modelDefinition['children'])
                    );
                }
            } elseif (array_key_exists('model', $modelDefinition)) {
                $modelDefinitionNames = array_merge(
                    $modelDefinitionNames,
                    $this->indexAllDefinedModelNamesFromDefinitionInput($modelDefinition['model'])
                );
            }
        }

        return $modelDefinitionNames;
    }

    private function convertModelElement(
        ModelRegister $modelRegister,
        ModelSet $models,
        array $rootNamespace,
        array $parentNamespace,
        array $definitionInput,
        array $allDefinedModelNames,
        ?Model $parent = null
    ): Model {

        $modelDefinitionName = $this->getModelDefinitionName($definitionInput);

        try {
            $modelNamespace = $this->makeModelNamespace($definitionInput, $rootNamespace, $parentNamespace);

            $isModelExternal = $modelNamespace->rootNamespace() !== $rootNamespace;
            $isModelDefinition = $isModelExternal === true || array_key_exists('type', $definitionInput);
            $isModelValidReference = (
                $isModelDefinition === false &&
                in_array($modelDefinitionName, $allDefinedModelNames) === true
            );

            if ($isModelDefinition === false) {
                if ($isModelValidReference === false) {
                    $message = sprintf(
                        '"%s" cannot be converted - it references a non-existent Model',
                        $modelDefinitionName
                    );
                    throw new DefinitionIsInvalid($message);
                }

                return new ReferencedModel(
                    $modelRegister,
                    $parent,
                    $modelDefinitionName,
                    new ModelProperties($definitionInput)
                );
            } else {
                $modelType = $this->getModelType($definitionInput);
                $modelDecorators = $this->makeModelDecorators($definitionInput);
                $modelInterfaces = $this->makeModelInterfaces($definitionInput);
                $testStipulations = $this->makeModelTestStipulations($definitionInput);

                $parentType = null;
                if ($parent !== null) {
                    $parentType = $parent->type();
                }

                $modelProperties = $this->distillModelPropertiesFromSchema(
                    $modelType,
                    $definitionInput,
                    $parentType
                );

                $modelChildren = new ModelSet($modelRegister);
                $model = new DefinedModel(
                    $modelRegister,
                    $parent,
                    $modelType,
                    $modelNamespace,
                    $modelDefinitionName,
                    $isModelExternal,
                    $modelProperties,
                    $modelDecorators,
                    $modelInterfaces,
                    $testStipulations,
                    $modelChildren
                );

                if (array_key_exists('children', $definitionInput)) {
                    $childNamespace = $parentNamespace;
                    $childNamespace[] = $modelDefinitionName;
                    foreach ($definitionInput['children'] as $childModelDefinition) {
                        $modelChildren->add($this->convertModelElement(
                            $modelRegister,
                            $models,
                            $rootNamespace,
                            $modelNamespace->relativeNamespace(),
                            $childModelDefinition,
                            $allDefinedModelNames,
                            $model
                        ));
                    }
                }

                return $model->type()->buildModel($model);
            }
        } catch (Exception $exception) {
            $message = sprintf(
                '"%s" cannot be converted - %s',
                $modelDefinitionName,
                $exception->getMessage()
            );
            throw new DefinitionIsInvalid($message);
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
        foreach ($schemaValidationRules as $key => $rules) {
            try {
                if (array_key_exists($key, $modelDefinition)) {
                    $properties[$key] = $modelDefinition[$key];
                }
            } catch (Exception $exception) {
                throw new Exception(sprintf('"%s" property is invalid - %s', $key, $exception->getMessage()));
            }
        }

        return new ModelProperties($properties);
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
                $hooks = $decorator['hooks'] ?? [];
                foreach ($hooks as $hookIndex => $hook) {
                    if (array_key_exists('stage', $hooks[$hookIndex]) === false) {
                        $hooks[$hookIndex]['stage'] = ModelDecoratorHookStage::POST_ORIGINAL_CODE()->toNative();
                    }
                    if (array_key_exists('splatArguments', $hooks[$hookIndex]) === false) {
                        $hooks[$hookIndex]['splatArguments'] = true;
                    }
                }

                $items[] = new ModelDecorator(
                    ModelDecoratorPath::fromNative($decorator['path']),
                    ModelDecoratorHookSet::fromNative($hooks)
                );
            }
        }
        return new ModelDecoratorSet($items);
    }

    private function makeModelInterfaces(array $modelDefinitionInput): ModelInterfaces
    {
        $interfaces = [];
        foreach (($modelDefinitionInput['interfaces'] ?? []) as $nativeInterface) {
            $interfaces[] = new ModelInterface($nativeInterface);
        }
        return new ModelInterfaces($interfaces);
    }
}
