<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions;

use Funeralzone\ValueObjectGenerator\Definitions\Exceptions\DefinitionSourceDoesNotExist;

final class FileSystemDefinitionLoader implements DefinitionLoader
{
    private $converter;

    public function __construct(DefinitionConverter $converter)
    {
        $this->converter = $converter;
    }

    public function load(array $rootNamespace, string $source, Definition $baseDefinition = null): Definition
    {
        if (is_file($source)) {
            $contents = file_get_contents($source);
            $definition = $this->converter->convert($rootNamespace, $contents, $baseDefinition);
            return $definition;
        } else {
            throw new DefinitionSourceDoesNotExist($source);
        }
    }
}
