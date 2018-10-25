<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Models;

final class ModelNamespaceFactory
{
    public function makeFromString(string $path): ModelNamespace
    {
        $elements = explode('\\', $path);
        return new ModelNamespace(
            $elements,
            []
        );
    }
}
