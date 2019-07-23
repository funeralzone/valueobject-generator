<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions;

use Exception;
use Funeralzone\ValueObjectGenerator\Definitions\Exceptions\DefinitionIsInvalid;
use Funeralzone\ValueObjectGenerator\Definitions\Exceptions\DefinitionSourceDoesNotExist;
use Symfony\Component\Yaml\Yaml;

final class YamlFileDefinitionLoader implements DefinitionLoader
{
    private $converter;
    private $definitionsCombiner;

    public function __construct(DefinitionConverter $converter, DefinitionsCombiner $definitionsCombiner)
    {
        $this->converter = $converter;
        $this->definitionsCombiner = $definitionsCombiner;
    }

    public function load(array $rootNamespace, array $sources, Definition $baseDefinition = null): Definition
    {
        $nativeDefinitions = [];

        foreach ($sources as $source) {
            if (is_file($source)) {
                $definitionString = file_get_contents($source);

                try {
                    $nativeDefinition = Yaml::parse($definitionString);

                    $nativeDefinitions[] = new NativeDefinition($nativeDefinition);
                } catch (Exception $exception) {
                    throw new DefinitionIsInvalid($exception->getMessage());
                }
            } else {
                throw new DefinitionSourceDoesNotExist($source);
            }
        }

        $combinedNativeDefinition = $this->definitionsCombiner->combine(new NativeDefinitions($nativeDefinitions));

        $definition = $this->converter->convert($rootNamespace, $combinedNativeDefinition);

        return $definition;
    }
}
